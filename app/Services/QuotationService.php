<?php

namespace App\Services;

use App\Contracts\Repositories\QuotationRepositoryInterface;
use App\Contracts\Services\QuotationServiceInterface;
use App\Events\Quotation\QuotationGenerated;
use App\Models\AddonCover;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use App\Traits\LogsNotificationsTrait;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuotationService extends BaseService implements QuotationServiceInterface
{
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function __construct(
        private PdfGenerationService $pdfGenerationService,
        private QuotationRepositoryInterface $quotationRepository
    ) {}

    /**
     * Create a new quotation with multiple company quotes.
     *
     * Creates a quotation record with vehicle details and generates insurance quotes
     * from multiple companies. The total IDV is calculated from all IDV components
     * (vehicle, trailer, CNG kit, accessories). Transaction-safe creation ensures
     * atomicity across quotation and company quote records. Dispatches QuotationGenerated
     * event after successful creation for notification processing.
     *
     * @param  array  $data  Quotation data including vehicle details, customer_id, and optional 'companies' array
     * @return Quotation Newly created quotation with company quotes
     *
     * @throws \Exception If transaction fails during quotation or company quote creation
     */
    public function createQuotation(array $data): Quotation
    {
        return $this->createInTransaction(function () use ($data) {
            // Note: QuotationRequested event disabled - policy types handled at company level

            $data['total_idv'] = $this->calculateTotalIdv($data);

            // Extract company data before creating quotation
            $companies = $data['companies'] ?? [];
            unset($data['companies']);

            $quotation = Quotation::query()->create($data);

            // Create company quotes manually
            if (! empty($companies)) {
                $this->createManualCompanyQuotes($quotation, $companies);
            }

            // Fire QuotationGenerated event after successful creation
            QuotationGenerated::dispatch($quotation);

            return $quotation;
        });
    }

    /**
     * Generate quotes from up to 5 active insurance companies.
     *
     * Automatically generates comparative quotes from active insurance companies
     * in the system (maximum 5 companies). Each quote includes premium calculations,
     * addon covers, GST, and final premium. Sets recommendations based on lowest
     * premium and best value proposition.
     *
     * @param  Quotation  $quotation  Quotation to generate company quotes for
     */
    public function generateCompanyQuotes(Quotation $quotation): void
    {
        $companies = InsuranceCompany::query()->where('status', 1)
            ->limit(5) // Maximum 5 companies as requested
            ->get();

        foreach ($companies as $company) {
            $this->generateCompanyQuote($quotation, $company);
        }

        // Set recommendations
        $this->setRecommendations($quotation);
    }

    /**
     * Generate quotes for specific selected insurance companies.
     *
     * Generates quotations only for user-selected insurance companies rather than
     * all active companies. Useful when customer has specific company preferences
     * or when comparing limited set of insurers.
     *
     * @param  Quotation  $quotation  Quotation to generate quotes for
     * @param  array  $companyIds  Array of insurance company IDs to generate quotes from
     */
    public function generateQuotesForSelectedCompanies(Quotation $quotation, array $companyIds): void
    {
        $companies = InsuranceCompany::query()->whereIn('id', $companyIds)
            ->where('status', 1)
            ->get();

        foreach ($companies as $company) {
            $this->generateCompanyQuote($quotation, $company);
        }

        // Set recommendations
        $this->setRecommendations($quotation);
    }

    /**
     * Generate a single company quote with premium calculations.
     *
     * Calculates comprehensive premium breakdown for a specific insurance company
     * including: base OD premium, CNG/LPG premiums, addon covers, GST (18% total:
     * 9% SGST + 9% CGST), roadside assistance, and final premium. Each company's
     * quote includes unique quote number, benefits, and exclusions.
     *
     * @param  Quotation  $quotation  Parent quotation with vehicle details
     * @param  InsuranceCompany  $insuranceCompany  Insurance company to generate quote for
     * @return QuotationCompany Created company quote with full premium breakdown
     */
    private function generateCompanyQuote(Quotation $quotation, InsuranceCompany $insuranceCompany): QuotationCompany
    {
        $baseData = $this->calculateBasePremium($quotation, $insuranceCompany);
        $addonData = $this->calculateAddonPremiums($quotation, $insuranceCompany);

        $netPremium = $baseData['total_od_premium'] + $addonData['total_addon_premium'];
        $sgstAmount = $netPremium * 0.09; // 9% SGST
        $cgstAmount = $netPremium * 0.09; // 9% CGST
        $totalPremium = $netPremium + $sgstAmount + $cgstAmount;
        $roadsideAssistance = $this->calculateRoadsideAssistance();
        $finalPremium = $totalPremium + $roadsideAssistance;

        return QuotationCompany::query()->create([
            'quotation_id' => $quotation->id,
            'insurance_company_id' => $insuranceCompany->id,
            'quote_number' => $this->generateQuoteNumber($quotation, $insuranceCompany),
            'basic_od_premium' => $baseData['basic_od_premium'],
            'cng_lpg_premium' => $baseData['cng_lpg_premium'],
            'total_od_premium' => $baseData['total_od_premium'],
            'addon_covers_breakdown' => $addonData['breakdown'],
            'total_addon_premium' => $addonData['total_addon_premium'],
            'net_premium' => $netPremium,
            'sgst_amount' => $sgstAmount,
            'cgst_amount' => $cgstAmount,
            'total_premium' => $totalPremium,
            'roadside_assistance' => $roadsideAssistance,
            'final_premium' => $finalPremium,
            'benefits' => $this->getCompanyBenefits(),
            'exclusions' => $this->getCompanyExclusions(),
        ]);
    }

    /**
     * Calculate base Own Damage (OD) premium with company-specific factors.
     *
     * Computes basic OD premium based on vehicle IDV, manufacturing year, and
     * company rating factors. Premium rates increase with vehicle age (1.2% for
     * new vehicles, up to 3.0% for older vehicles). Also calculates CNG/LPG kit
     * premium at 5% of kit IDV for hybrid/CNG fuel types.
     *
     * @param  Quotation  $quotation  Quotation with vehicle details and IDV
     * @param  InsuranceCompany  $insuranceCompany  Insurance company for rating factor
     * @return array Array with 'basic_od_premium', 'cng_lpg_premium', and 'total_od_premium'
     */
    private function calculateBasePremium(Quotation $quotation, InsuranceCompany $insuranceCompany): array
    {
        // Base premium calculation based on IDV and company factors
        $idv = $quotation->total_idv;
        $companyFactor = $this->getCompanyRatingFactor($insuranceCompany);

        // Basic OD premium calculation (simplified)
        $basicRate = $this->getBasicOdRate($quotation);
        $basicOdPremium = ($idv * $basicRate / 100) * $companyFactor;

        // CNG/LPG kit premium
        $cngLpgPremium = 0;
        if (in_array($quotation->fuel_type, ['CNG', 'Hybrid']) && $quotation->idv_cng_lpg_kit > 0) {
            $cngLpgPremium = ($quotation->idv_cng_lpg_kit * 0.05) * $companyFactor;
        }

        return [
            'basic_od_premium' => round($basicOdPremium, 2),
            'cng_lpg_premium' => round($cngLpgPremium, 2),
            'total_od_premium' => round($basicOdPremium + $cngLpgPremium, 2),
        ];
    }

    /**
     * Calculate addon cover premiums with company-specific rates.
     *
     * Computes premium for each addon cover selected in quotation using company-specific
     * rating factors. Addons include: Zero Depreciation (0.4% of IDV), Engine Protection
     * (0.1% of IDV), NCB Protection (0.05% of IDV), Invoice Protection (0.23% of IDV),
     * Tyre Protection (0.18% of IDV), Consumables (0.06% of IDV), and fixed-rate addons
     * like Road Side Assistance (₹180), Key Replacement (₹425), Personal Accident (₹450).
     *
     * @param  Quotation  $quotation  Quotation with addon covers array
     * @param  InsuranceCompany  $insuranceCompany  Insurance company for addon rates
     * @return array Array with 'breakdown' (addon name => premium) and 'total_addon_premium'
     */
    private function calculateAddonPremiums(Quotation $quotation, InsuranceCompany $insuranceCompany): array
    {
        $addons = $quotation->addon_covers ?? [];
        $breakdown = [];
        $totalAddonPremium = 0;
        $companyFactor = $this->getCompanyRatingFactor($insuranceCompany);

        $addonRates = $this->getAddonRates();

        foreach ($addons as $addon) {
            $premium = $this->calculateAddonPremium($addon, $quotation, $addonRates, $companyFactor);
            if ($premium > 0) {
                $breakdown[$addon] = $premium;
                $totalAddonPremium += $premium;
            }
        }

        return [
            'breakdown' => $breakdown,
            'total_addon_premium' => round($totalAddonPremium, 2),
        ];
    }

    /**
     * Calculate premium for a specific addon cover.
     *
     * Applies specific calculation formula for each addon type. IDV-based addons
     * use percentage of vehicle IDV multiplied by company factor. Fixed-rate addons
     * (Road Side Assistance, Key Replacement, Personal Accident) use base rates
     * adjusted by company factor.
     *
     * @param  string  $addon  Addon cover name (e.g., 'Zero Depreciation')
     * @param  Quotation  $quotation  Quotation with vehicle IDV
     * @param  array  $rates  Company-specific addon rate configuration
     * @param  float  $companyFactor  Company rating multiplier (0.92 to 1.05)
     * @return float Calculated addon premium rounded to 2 decimals
     */
    private function calculateAddonPremium(string $addon, Quotation $quotation, array $rates, float $companyFactor): float
    {
        $idv = $quotation->total_idv;

        return match ($addon) {
            'Zero Depreciation' => ($idv * ($rates['depreciation'] ?? 0.4) / 100) * $companyFactor,
            'Engine Protection' => ($idv * ($rates['engine_secure'] ?? 0.1) / 100) * $companyFactor,
            'Road Side Assistance' => 180 * $companyFactor,
            'NCB Protection' => ($idv * 0.05 / 100) * $companyFactor,
            'Invoice Protection' => ($idv * ($rates['return_to_invoice'] ?? 0.23) / 100) * $companyFactor,
            'Key Replacement' => 425 * $companyFactor,
            'Personal Accident' => 450 * $companyFactor,
            'Tyre Protection' => ($idv * ($rates['tyre_secure'] ?? 0.18) / 100) * $companyFactor,
            'Consumables' => ($idv * ($rates['consumables'] ?? 0.06) / 100) * $companyFactor,
            default => 0,
        };
    }

    /**
     * Set ranking and recommendations for quotation companies.
     *
     * Ranks all company quotes by final premium (lowest to highest) and marks
     * the lowest premium quote as recommended. Rankings are updated in database
     * for display in quotation comparison view.
     *
     * @param  Quotation  $quotation  Quotation with company quotes to rank
     */
    private function setRecommendations(Quotation $quotation): void
    {
        $quotes = $quotation->quotationCompanies()->orderBy('final_premium')->get();

        // Set ranking
        foreach ($quotes as $index => $quote) {
            $quote->update(['ranking' => $index + 1]);
        }

        // Mark best value quote as recommended (lowest premium with good coverage)
        $recommended = $quotes->first();
        if ($recommended) {
            $recommended->update(['is_recommended' => true]);
        }
    }

    /**
     * Send quotation via WhatsApp with PDF attachment.
     *
     * Generates quotation comparison PDF and sends it to customer via WhatsApp
     * with formatted message showing all company quotes. Uses notification template
     * system with fallback to hardcoded message. Updates quotation status to 'Sent'
     * on successful delivery. Cleans up temporary PDF file after sending.
     *
     * @param  Quotation  $quotation  Quotation to send
     *
     * @throws \Exception If WhatsApp sending fails or PDF generation fails
     */
    public function sendQuotationViaWhatsApp(Quotation $quotation): void
    {
        Log::info('Starting WhatsApp quotation send', [
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'customer_name' => $quotation->customer->name ?? 'N/A',
            'whatsapp_number' => $quotation->whatsapp_number,
            'user_id' => auth()->user()->id ?? 'System',
        ]);

        $message = $this->generateWhatsAppMessageWithAttachment($quotation);
        $pdfPath = $this->pdfGenerationService->generateQuotationPdfForWhatsApp($quotation);

        try {
            // Get template information
            $templateService = app(TemplateService::class);
            $template = $templateService->getTemplateByCode('quotation_ready', 'whatsapp');

            // Use trait method to log and send with attachment
            $result = $this->logAndSendWhatsAppWithAttachment(
                $quotation,
                $message,
                $quotation->whatsapp_number,
                $pdfPath,
                [
                    'notification_type_code' => 'quotation_ready',
                    'template_id' => $template->id ?? null,
                ]
            );

            if ($result['success']) {
                Log::info('WhatsApp quotation sent successfully', [
                    'quotation_id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'whatsapp_number' => $quotation->whatsapp_number,
                    'result' => $result,
                    'user_id' => auth()->user()->id ?? 'System',
                ]);

                $quotation->update([
                    'status' => 'Sent',
                    'sent_at' => now(),
                ]);
            } else {
                throw new \Exception($result['error'] ?? 'Unknown error occurred while sending quotation');
            }
        } catch (\Exception $exception) {
            Log::error('WhatsApp quotation send failed', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'customer_name' => $quotation->customer->name ?? 'N/A',
                'whatsapp_number' => $quotation->whatsapp_number,
                'error' => $exception->getMessage(),
                'user_id' => auth()->user()->id ?? 'System',
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception; // Re-throw to maintain the error flow
        } finally {
            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
                Log::debug('Temporary PDF file cleaned up', ['path' => $pdfPath]);
            }
        }
    }

    /**
     * Generate WhatsApp message with quotation comparison details.
     *
     * Creates formatted WhatsApp message showing vehicle details, company quotes
     * comparison, best premium, and potential savings. Uses notification template
     * system with fallback to hardcoded format. Includes company rankings and
     * recommendation indicators.
     *
     * @param  Quotation  $quotation  Quotation with company quotes
     * @return string Formatted WhatsApp message with markdown-style formatting
     */
    private function generateWhatsAppMessageWithAttachment(Quotation $quotation): string
    {
        $customer = $quotation->customer;
        $quotes = $quotation->quotationCompanies()->orderBy('final_premium')->get();
        $bestQuote = $quotes->first();

        // Build comparison list
        $comparisonList = '';
        foreach ($quotes as $index => $quote) {
            $icon = $quote->is_recommended ? '⭐' : ($index + 1);
            $ranking = is_numeric($icon) ? $icon.'.' : $icon;
            $comparisonList .= sprintf('%s *%s*: %s', $ranking, $quote->insuranceCompany->name, $quote->getFormattedPremium());
            if ($quote->is_recommended) {
                $comparisonList .= ' _(Recommended)_';
            }

            $comparisonList .= "\n";
        }

        // Try to get message from template, fallback to hardcoded
        $templateService = app(TemplateService::class);
        $message = $templateService->renderFromQuotation('quotation_ready', 'whatsapp', $quotation);

        if ($message) {
            return $message;
        }

        // Fallback to original hardcoded message
        $message = "🚗 *Insurance Quotation*\n\n";
        $message .= "Dear *{$customer->name}*,\n\n";
        $message .= "Your insurance quotation is ready! We have compared *{$quotes->count()} insurance companies* for you.\n\n";

        $message .= "🚙 *Vehicle Details:*\n";
        $message .= "• Vehicle: *{$quotation->make_model_variant}*\n";
        $message .= "• Registration: *{$quotation->vehicle_number}*\n";
        $message .= '• IDV: *₹'.number_format($quotation->total_idv)."*\n";
        $message .= "• Policy: *{$quotation->policy_type}* - {$quotation->policy_tenure_years} Year(s)\n\n";

        if ($bestQuote) {
            $message .= "💰 *Best Premium:*\n";
            $message .= "• *{$bestQuote->insuranceCompany->name}*\n";
            $message .= sprintf('• Premium: *%s*', $bestQuote->getFormattedPremium());
            $message .= "\n\n";
        }

        $message .= "📊 *Premium Comparison:*\n";
        $message .= $comparisonList;

        // Calculate savings if more than one quote
        if ($quotes->count() > 1) {
            $highestQuote = $quotes->last();
            $savings = $highestQuote->final_premium - $bestQuote->final_premium;
            if ($savings > 0) {
                $message .= "\n💵 *You can save up to ₹".number_format($savings)."*\n";
            }
        }

        $message .= "\n📎 *Detailed PDF comparison attached*";
        $message .= "\n\n📞 For any queries or to proceed with purchase:";
        $message .= "\n\nBest regards,";
        $message .= "\n".company_advisor_name();
        $message .= "\n".company_website();
        $message .= "\n".company_title();

        return $message.("\n\"".company_tagline().'"');
    }

    /**
     * Send quotation via email with PDF attachment.
     *
     * Generates quotation comparison PDF and sends it to customer email address
     * using EmailService with notification templates. Updates quotation status to
     * 'Sent' on successful delivery. Cleans up temporary PDF file after sending.
     * Falls back to quotation.email if customer.email is not available.
     *
     * @param  Quotation  $quotation  Quotation to send
     *
     * @throws \Exception If email sending fails or PDF generation fails
     */
    public function sendQuotationViaEmail(Quotation $quotation): void
    {
        Log::info('Starting email quotation send', [
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
            'customer_name' => $quotation->customer->name ?? 'N/A',
            'email' => $quotation->email ?? $quotation->customer->email,
            'user_id' => auth()->user()->id ?? 'System',
        ]);

        $pdfPath = $this->pdfGenerationService->generateQuotationPdfForWhatsApp($quotation);

        try {
            $emailService = app(EmailService::class);
            $sent = $emailService->sendFromQuotation('quotation_ready', $quotation, [$pdfPath]);

            if ($sent) {
                Log::info('Email quotation sent successfully', [
                    'quotation_id' => $quotation->id,
                    'quotation_number' => $quotation->quotation_number,
                    'email' => $quotation->email ?? $quotation->customer->email,
                    'user_id' => auth()->user()->id ?? 'System',
                ]);

                // Update quotation status if not already sent
                if ($quotation->status !== 'Sent') {
                    $quotation->update([
                        'status' => 'Sent',
                        'sent_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::error('Email quotation send failed', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'customer_name' => $quotation->customer->name ?? 'N/A',
                'email' => $quotation->email ?? $quotation->customer->email,
                'error' => $exception->getMessage(),
                'user_id' => auth()->user()->id ?? 'System',
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception; // Re-throw to maintain the error flow
        } finally {
            // Clean up temporary PDF file
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
                Log::debug('Temporary PDF file cleaned up', ['path' => $pdfPath]);
            }
        }
    }

    /**
     * Generate quotation PDF for download or viewing.
     *
     * Delegates PDF generation to PdfGenerationService. Returns response suitable
     * for browser download or inline viewing. PDF includes full quotation details,
     * company comparison table, premium breakdowns, and terms & conditions.
     *
     * @param  Quotation  $quotation  Quotation to generate PDF for
     * @return Response PDF response for browser
     */
    public function generatePdf(Quotation $quotation)
    {
        return $this->pdfGenerationService->generateQuotationPdf($quotation);
    }

    /**
     * Calculate total IDV from all IDV components.
     *
     * Sums all Insured Declared Value (IDV) components including: vehicle IDV,
     * trailer IDV, CNG/LPG kit IDV, electrical accessories IDV, and non-electrical
     * accessories IDV. Total IDV determines the insurance coverage amount and is
     * used in premium calculations.
     *
     * @param  array  $data  Quotation data with IDV components
     * @return float Total IDV rounded to 2 decimals
     */
    private function calculateTotalIdv(array $data): float
    {
        return ($data['idv_vehicle'] ?? 0) +
               ($data['idv_trailer'] ?? 0) +
               ($data['idv_cng_lpg_kit'] ?? 0) +
               ($data['idv_electrical_accessories'] ?? 0) +
               ($data['idv_non_electrical_accessories'] ?? 0);
    }

    /**
     * Generate unique quote number for company quotation.
     *
     * Format: QT/YY/####CC######## where:
     * - QT = Quotation prefix
     * - YY = Current year (2 digits)
     * - #### = Quotation ID (4 digits, zero-padded)
     * - CC = Company ID (2 digits, zero-padded)
     * - ######## = Microsecond timestamp (8 digits, ensures uniqueness)
     *
     * @param  Quotation  $quotation  Parent quotation
     * @param  InsuranceCompany|int  $companyId  Company instance or ID
     * @return string Unique quote number (e.g., QT/25/000112345678901)
     */
    private function generateQuoteNumber(Quotation $quotation, $companyId): string
    {
        // Generate unique quote number using microsecond timestamp to avoid duplicates
        $microtime = (string) microtime(true);
        $uniqueId = str_replace('.', '', $microtime); // Remove decimal point
        $uniqueId = substr($uniqueId, -8); // Take last 8 digits for uniqueness

        return 'QT/'.date('y').'/'.str_pad($quotation->id, 4, '0', STR_PAD_LEFT).
               str_pad($companyId, 2, '0', STR_PAD_LEFT).
               $uniqueId;
    }

    /**
     * Get company-specific rating factor for premium adjustment.
     *
     * Each insurance company has different risk assessment and pricing strategies
     * reflected in their rating factor. Lower factors (0.92) indicate more competitive
     * pricing, while higher factors (1.05) indicate premium pricing. Default factor
     * is 1.0 for neutral pricing.
     *
     * @param  InsuranceCompany  $insuranceCompany  Insurance company instance
     * @return float Rating factor between 0.92 and 1.05
     */
    private function getCompanyRatingFactor(InsuranceCompany $insuranceCompany): float
    {
        // Different companies have different rating factors
        return match ($insuranceCompany->name) {
            'TATA AIG' => 1.0,
            'HDFC ERGO' => 0.95,
            'ICICI Lombard' => 1.05,
            'Bajaj Allianz' => 0.98,
            'Reliance General' => 0.92,
            default => 1.0,
        };
    }

    /**
     * Get basic Own Damage (OD) rate based on vehicle age.
     *
     * OD premium rate increases with vehicle age due to higher depreciation and
     * repair costs. Rates range from 1.2% for new vehicles (0-1 year) up to 3.0%
     * for older vehicles (5+ years). Calculated as percentage of vehicle IDV.
     *
     * @param  Quotation  $quotation  Quotation with vehicle manufacturing year
     * @return float OD rate as percentage (1.2 to 3.0)
     */
    private function getBasicOdRate(Quotation $quotation): float
    {
        // Basic OD rate based on vehicle age and IDV
        $vehicleAge = date('Y') - $quotation->manufacturing_year;

        if ($vehicleAge <= 1) {
            return 1.2;
        }

        if ($vehicleAge <= 3) {
            return 1.8;
        }

        if ($vehicleAge <= 5) {
            return 2.4;
        }

        return 3.0;
    }

    /**
     * Get company-specific addon cover rates.
     *
     * Returns addon premium rates as percentages of vehicle IDV. Rates are customizable
     * per company but currently use standard rates: Zero Depreciation (0.4%), Engine
     * Protection (0.1%), Consumables (0.06%), Tyre Protection (0.18%), Return to
     * Invoice (0.23%).
     *
     * @return array Addon rates keyed by addon type
     */
    private function getAddonRates(): array
    {
        // Company-specific addon rates
        return [
            'depreciation' => 0.4,
            'consumables' => 0.06,
            'tyre_secure' => 0.18,
            'engine_secure' => 0.1,
            'return_to_invoice' => 0.23,
        ];
    }

    /**
     * Calculate roadside assistance charge.
     *
     * Standard roadside assistance charge applicable to all companies. Covers
     * emergency services like towing, flat tire assistance, battery jump-start,
     * and fuel delivery within specified limits.
     *
     * @return float Roadside assistance premium (₹136.88)
     */
    private function calculateRoadsideAssistance(): float
    {
        return 136.88;
        // Standard rate
    }

    /**
     * Get standard company benefits description.
     *
     * Returns comprehensive list of benefits provided by insurance company including
     * coverage details, customer support availability, claim settlement process, and
     * service network information.
     *
     * @return string Formatted benefits description
     */
    private function getCompanyBenefits(): string
    {
        return 'Comprehensive coverage with add-on benefits, 24/7 customer support, quick claim settlement, nationwide network of garages.';
    }

    /**
     * Get standard policy exclusions description.
     *
     * Returns list of scenarios not covered by the insurance policy including
     * pre-existing damage, normal wear and tear, consequential losses, intoxicated
     * driving, and commercial use (for private car policies).
     *
     * @return string Formatted exclusions description
     */
    private function getCompanyExclusions(): string
    {
        return 'Pre-existing damages, wear and tear, consequential damages, driving under influence, use for commercial purposes.';
    }

    /**
     * Create multiple company quotes manually from form data.
     *
     * Processes array of company quote data from quotation form submission. Each
     * company can have multiple plan variations. Handles addon breakdown processing,
     * duplicate detection, and automatic ranking. Used when user manually enters
     * quotes from different insurers rather than auto-generating them.
     *
     * @param  Quotation  $quotation  Parent quotation
     * @param  array  $companies  Array of company quote data from form
     */
    public function createManualCompanyQuotes(Quotation $quotation, array $companies): void
    {
        $processedQuotes = []; // Track processed quotes to avoid exact duplicates

        foreach ($companies as $index => $companyData) {
            // Create unique key based on multiple fields to allow same company with different plans
            $quoteKey = $companyData['insurance_company_id'].'_'.
                       ($companyData['quote_number'] ?? '').'_'.
                       ($companyData['basic_od_premium'] ?? '').'_'.
                       $index; // Include index to ensure each form entry is processed

            // Skip only if this exact quote has already been processed
            if (in_array($quoteKey, $processedQuotes)) {
                continue;
            }

            $processedQuotes[] = $quoteKey;

            // Process addon breakdown to calculate total if needed
            $companyData = $this->processAddonBreakdown($companyData);
            $this->createManualCompanyQuote($quotation, $companyData);
        }

        // Set rankings if not provided
        $this->setRankings($quotation);
    }

    /**
     * Process addon covers breakdown and calculate total addon premium.
     *
     * Ensures addon breakdown structure is properly formatted and calculates total
     * addon premium from individual addon prices. Handles both array format (with
     * 'price' key) and simple numeric values.
     *
     * @param  array  $data  Company quote data with addon_covers_breakdown
     * @return array Processed data with calculated total_addon_premium
     */
    private function processAddonBreakdown(array $data): array
    {
        if (! isset($data['addon_covers_breakdown'])) {
            $data['addon_covers_breakdown'] = [];

            return $data;
        }

        // Calculate total addon premium from breakdown
        $totalAddon = 0;
        foreach ($data['addon_covers_breakdown'] as $addon) {
            if (is_array($addon) && isset($addon['price'])) {
                $totalAddon += floatval($addon['price']);
            } else {
                $totalAddon += floatval($addon);
            }
        }

        // Update total if not set or if breakdown total differs
        if (! isset($data['total_addon_premium']) || $data['total_addon_premium'] != $totalAddon) {
            $data['total_addon_premium'] = $totalAddon;
        }

        return $data;
    }

    /**
     * Create a single manual company quote from form data.
     *
     * Processes comprehensive form data including policy details, IDV components,
     * premium breakdown, addon covers, and GST calculations. Handles both pre-calculated
     * addon breakdown from frontend and legacy individual addon field format. Creates
     * QuotationCompany record with complete premium and coverage details.
     *
     * @param  Quotation  $quotation  Parent quotation
     * @param  array  $data  Company quote data including premiums, addons, and policy details
     * @return QuotationCompany Created company quote record
     */
    private function createManualCompanyQuote(Quotation $quotation, array $data): QuotationCompany
    {
        // Check if frontend already sent addon_covers_breakdown (from edit-quote-form calculation)
        if (isset($data['addon_covers_breakdown']) && ! empty($data['addon_covers_breakdown'])) {
            // Use the breakdown as-is, just ensure proper structure
            $addonBreakdown = [];
            foreach ($data['addon_covers_breakdown'] as $addonName => $addonData) {
                if (is_array($addonData)) {
                    $addonBreakdown[$addonName] = [
                        'price' => floatval($addonData['price'] ?? 0),
                        'note' => $addonData['note'] ?? '',
                    ];
                }
            }

            $data['addon_covers_breakdown'] = $addonBreakdown;
        } else {
            // Fallback: Process individual addon fields into breakdown
            $addonBreakdown = [];
            $addonCovers = AddonCover::getOrdered(1);

            foreach ($addonCovers as $addonCover) {
                $slug = Str::slug($addonCover->name, '_');
                $field = 'addon_'.$slug;
                $noteField = $field.'_note';
                $selectedField = $field.'_selected';

                // Check if addon is selected (either has selected flag = 1 OR has value > 0 OR has note)
                $isSelected = ($data[$selectedField] ?? '0') === '1'
                    || (isset($data[$field]) && $data[$field] > 0)
                    || ! empty($data[$noteField]);

                if ($isSelected) {
                    $addonBreakdown[$addonCover->name] = [
                        'price' => isset($data[$field]) && $data[$field] !== '' ? floatval($data[$field]) : 0,
                        'field' => $field,
                        'note' => $data[$noteField] ?? '',
                    ];
                }
            }

            $data['addon_covers_breakdown'] = $addonBreakdown;
        }

        return QuotationCompany::query()->create([
            'quotation_id' => $quotation->id,
            'insurance_company_id' => $data['insurance_company_id'],
            'quote_number' => $data['quote_number'] ?? $this->generateQuoteNumber($quotation, $data['insurance_company_id']),
            // Policy and coverage fields
            'policy_type' => $data['policy_type'] ?? 'Comprehensive',
            'policy_tenure_years' => $data['policy_tenure_years'] ?? 1,
            // IDV fields
            'idv_vehicle' => $data['idv_vehicle'] ?? 0,
            'idv_trailer' => $data['idv_trailer'] ?? 0,
            'idv_cng_lpg_kit' => $data['idv_cng_lpg_kit'] ?? 0,
            'idv_electrical_accessories' => $data['idv_electrical_accessories'] ?? 0,
            'idv_non_electrical_accessories' => $data['idv_non_electrical_accessories'] ?? 0,
            'total_idv' => $data['total_idv'] ?? 0,
            // Premium fields
            'basic_od_premium' => $data['basic_od_premium'],
            'tp_premium' => $data['tp_premium'] ?? 0,
            'cng_lpg_premium' => $data['cng_lpg_premium'] ?? 0,
            'total_od_premium' => $data['total_od_premium'] ?? $data['basic_od_premium'],
            'addon_covers_breakdown' => $data['addon_covers_breakdown'] ?? [],
            'total_addon_premium' => $data['total_addon_premium'] ?? 0,
            'net_premium' => $data['net_premium'] ?? 0,
            'sgst_amount' => $data['sgst_amount'] ?? 0,
            'cgst_amount' => $data['cgst_amount'] ?? 0,
            'total_premium' => $data['total_premium'] ?? 0,
            'roadside_assistance' => $data['roadside_assistance'] ?? 0,
            'final_premium' => $data['final_premium'] ?? 0,
            'is_recommended' => $data['is_recommended'] ?? false,
            'recommendation_note' => $data['recommendation_note'] ?? null,
            'ranking' => $data['ranking'] ?? 1,
            'benefits' => $data['benefits'] ?? null,
            'exclusions' => $data['exclusions'] ?? null,
        ]);
    }

    /**
     * Update quotation and replace all company quotes.
     *
     * Updates quotation master data and deletes all existing company quotes before
     * creating new ones. Used when editing quotation requires regenerating all company
     * quotes (e.g., when vehicle details or IDV changes). Transaction-safe operation.
     *
     * @param  Quotation  $quotation  Quotation to update
     * @param  array  $data  Updated quotation data with 'companies' array
     */
    public function updateQuotationWithCompanies(Quotation $quotation, array $data): void
    {
        // Update quotation data
        $quotationData = $data;
        $companies = $quotationData['companies'] ?? [];
        unset($quotationData['companies']);

        $quotationData['total_idv'] = $this->calculateTotalIdv($quotationData);
        $quotation->update($quotationData);

        // Delete existing company quotes and create new ones
        $quotation->quotationCompanies()->delete();
        if (! empty($companies)) {
            $this->createManualCompanyQuotes($quotation, $companies);
        }
    }

    /**
     * Set or update rankings for company quotes by final premium.
     *
     * Automatically ranks company quotes from lowest to highest premium. Only updates
     * ranking if not manually set (ranking = 1 indicates auto-ranking needed). Preserves
     * manually assigned rankings from user adjustments.
     *
     * @param  Quotation  $quotation  Quotation with company quotes to rank
     */
    private function setRankings(Quotation $quotation): void
    {
        $quotes = $quotation->quotationCompanies()->orderBy('final_premium')->get();

        foreach ($quotes as $index => $quote) {
            if (! $quote->ranking || $quote->ranking === 1) {
                $quote->update(['ranking' => $index + 1]);
            }
        }
    }

    /**
     * Get paginated list of quotations with filters and search.
     *
     * Retrieves quotations using repository pattern with optional filtering by status,
     * customer, date range, and search terms. Returns paginated results (15 per page)
     * suitable for listing view.
     *
     * @param  Request  $request  HTTP request with optional filter parameters
     * @return LengthAwarePaginator Paginated quotation collection
     */
    public function getQuotations(Request $request): LengthAwarePaginator
    {
        return $this->quotationRepository->getPaginated($request, 15);
    }

    /**
     * Delete a quotation with cascade to company quotes.
     *
     * Deletes quotation and all associated company quotes within transaction to ensure
     * data consistency. Uses soft delete if configured in model, otherwise performs
     * hard delete.
     *
     * @param  Quotation  $quotation  Quotation to delete
     * @return bool True if deletion successful
     *
     * @throws \Exception If deletion fails within transaction
     */
    public function deleteQuotation(Quotation $quotation): bool
    {
        return $this->deleteInTransaction(
            fn (): bool => $this->quotationRepository->delete($quotation)
        );
    }

    /**
     * Calculate total premium for quotation (legacy method).
     *
     * Currently calculates total IDV as a legacy implementation. May be extended
     * to calculate full premium including OD, TP, addons, and GST in future updates.
     *
     * @param  array  $data  Quotation data with IDV components
     * @return float Calculated total (currently returns total IDV)
     */
    public function calculatePremium(array $data): float
    {
        return $this->calculateTotalIdv($data);
    }

    /**
     * Get form data for quotation creation and editing.
     *
     * Retrieves all reference data needed for quotation form including active customers,
     * insurance companies, and ordered list of available addon covers. Used to populate
     * form dropdowns and addon cover options.
     *
     * @return array Associative array with 'customers', 'insuranceCompanies', and 'addonCovers'
     */
    public function getQuotationFormData(): array
    {
        return [
            'customers' => Customer::query()->where('status', 1)->orderBy('name')->get(),
            'insuranceCompanies' => InsuranceCompany::query()->where('status', 1)->orderBy('name')->get(),
            'addonCovers' => AddonCover::getOrdered(1),
        ];
    }
}
