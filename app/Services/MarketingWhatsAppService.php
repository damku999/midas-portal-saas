<?php

namespace App\Services;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Services\MarketingWhatsAppServiceInterface;
use App\Models\Customer;
use App\Traits\LogsNotificationsTrait;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Marketing WhatsApp Service
 *
 * Handles WhatsApp marketing business logic including campaign management,
 * customer selection, and message sending operations.
 * Inherits transaction management from BaseService.
 */
class MarketingWhatsAppService extends BaseService implements MarketingWhatsAppServiceInterface
{
    use LogsNotificationsTrait, WhatsAppApiTrait;

    /**
     * Constructor
     */
    public function __construct(
        /**
         * Customer Repository instance
         */
        private CustomerRepositoryInterface $customerRepository,
        /**
         * File Upload Service instance
         */
        private FileUploadService $fileUploadService
    ) {}

    /**
     * Get active customers for WhatsApp marketing
     */
    public function getActiveCustomers(): Collection
    {
        return $this->customerRepository->getActiveCustomers();
    }

    /**
     * Get customers with valid mobile numbers for marketing
     */
    public function getValidCustomersForMarketing(string $recipients, array $selectedCustomerIds = []): Collection
    {
        if ($recipients === 'all') {
            return $this->customerRepository->getCustomersWithValidMobileNumbers();
        }

        return $this->customerRepository->getCustomersByIds($selectedCustomerIds)
            ->filter(fn ($customer): bool => $customer->status && ! empty($customer->mobile_number));
    }

    /**
     * Send WhatsApp marketing campaign to customers
     */
    public function sendMarketingCampaign(array $campaignData): array
    {
        return $this->executeInTransaction(function () use ($campaignData): array {
            $customers = $this->getValidCustomersForMarketing(
                $campaignData['recipients'],
                $campaignData['selected_customers'] ?? []
            );

            if ($customers->isEmpty()) {
                throw new \Exception('No valid customers found with mobile numbers.');
            }

            $successCount = 0;
            $failedCount = 0;
            $failedCustomers = [];
            $imagePath = null;

            // Handle image upload if message type is image
            if ($campaignData['message_type'] === 'image' && isset($campaignData['image'])) {
                $uploadResult = $this->fileUploadService->uploadFile(
                    $campaignData['image'],
                    'marketing/images'
                );

                if ($uploadResult['status']) {
                    $imagePath = storage_path('app/public/'.$uploadResult['file_path']);
                } else {
                    throw new \Exception('Failed to upload image: '.$uploadResult['message']);
                }
            }

            // Send messages to each customer
            foreach ($customers as $customer) {
                try {
                    if ($campaignData['message_type'] === 'text') {
                        $success = $this->sendTextMessage(
                            $campaignData['message_text'],
                            $customer->mobile_number,
                            $customer->id
                        );
                    } else {
                        $success = $this->sendImageMessage(
                            $campaignData['message_text'],
                            $customer->mobile_number,
                            $imagePath,
                            $customer->id
                        );
                    }

                    if ($success) {
                        $successCount++;
                        $this->logMarketingAttempt($customer->id, $campaignData['message_type'], true);
                    } else {
                        $failedCount++;
                        $failedCustomers[] = $customer->name.' ('.$customer->mobile_number.')';
                        $this->logMarketingAttempt($customer->id, $campaignData['message_type'], false, 'WhatsApp API failed');
                    }

                } catch (\Exception $e) {
                    $failedCount++;
                    $failedCustomers[] = $customer->name.' ('.$customer->mobile_number.')';
                    $this->logMarketingAttempt($customer->id, $campaignData['message_type'], false, $e->getMessage());
                }
            }

            return [
                'total_customers' => $customers->count(),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'failed_customers' => $failedCustomers,
            ];
        });
    }

    /**
     * Preview customer list for marketing campaign
     */
    public function previewCustomerList(string $recipients, array $selectedCustomerIds = []): array
    {
        $customers = $this->getValidCustomersForMarketing($recipients, $selectedCustomerIds);

        return [
            'status' => 'success',
            'customers' => $customers->map(fn ($customer): array => [
                'name' => $customer->name,
                'mobile_number' => $customer->mobile_number,
            ]),
            'count' => $customers->count(),
        ];
    }

    /**
     * Send text message to customer
     */
    public function sendTextMessage(string $message, string $mobileNumber, int $customerId): bool
    {
        try {
            $customer = Customer::find($customerId);

            if (! $customer) {
                Log::error('Customer not found for marketing message', [
                    'customer_id' => $customerId,
                ]);

                return false;
            }

            // Use trait method to log and send
            $result = $this->logAndSendWhatsApp(
                $customer,
                $message,
                $mobileNumber,
                [
                    'notification_type_code' => 'marketing_message',
                    'template_id' => null,
                ]
            );

            Log::info('Marketing WhatsApp text sent', [
                'customer_id' => $customerId,
                'mobile_number' => $mobileNumber,
                'message_type' => 'text',
                'result' => $result,
                'sent_by' => auth()->id(),
            ]);

            return $result['success'];
        } catch (\Exception $exception) {
            Log::error('Marketing WhatsApp text failed', [
                'customer_id' => $customerId,
                'mobile_number' => $mobileNumber,
                'error' => $exception->getMessage(),
                'sent_by' => auth()->id(),
            ]);

            return false;
        }
    }

    /**
     * Send image message to customer
     */
    public function sendImageMessage(string $message, string $mobileNumber, string $imagePath, int $customerId): bool
    {
        try {
            $customer = Customer::find($customerId);

            if (! $customer) {
                Log::error('Customer not found for marketing image', [
                    'customer_id' => $customerId,
                ]);

                return false;
            }

            // Use trait method to log and send with attachment
            $result = $this->logAndSendWhatsAppWithAttachment(
                $customer,
                $message,
                $mobileNumber,
                $imagePath,
                [
                    'notification_type_code' => 'marketing_image',
                    'template_id' => null,
                ]
            );

            Log::info('Marketing WhatsApp image sent', [
                'customer_id' => $customerId,
                'mobile_number' => $mobileNumber,
                'message_type' => 'image',
                'image_path' => $imagePath,
                'result' => $result,
                'sent_by' => auth()->id(),
            ]);

            return $result['success'];
        } catch (\Exception $exception) {
            Log::error('Marketing WhatsApp image failed', [
                'customer_id' => $customerId,
                'mobile_number' => $mobileNumber,
                'image_path' => $imagePath,
                'error' => $exception->getMessage(),
                'sent_by' => auth()->id(),
            ]);

            return false;
        }
    }

    /**
     * Get marketing campaign statistics
     */
    public function getMarketingStatistics(): array
    {
        try {
            $totalCustomers = $this->customerRepository->count();
            $activeCustomers = $this->customerRepository->getActiveCustomers()->count();
            $customersWithMobileNumbers = $this->customerRepository->getCustomersWithValidMobileNumbers()->count();

            return [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'customers_with_mobile' => $customersWithMobileNumbers,
                'marketing_ready_customers' => $customersWithMobileNumbers,
                'last_campaign_date' => $this->getLastCampaignDate(),
                'campaigns_this_month' => $this->getCampaignsThisMonth(),
            ];
        } catch (\Exception $exception) {
            Log::error('Failed to get marketing statistics', [
                'error' => $exception->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return [];
        }
    }

    /**
     * Log marketing message attempt
     */
    public function logMarketingAttempt(int $customerId, string $messageType, bool $success, ?string $error = null): void
    {
        $logData = [
            'customer_id' => $customerId,
            'message_type' => $messageType,
            'success' => $success,
            'sent_by' => auth()->id(),
            'sent_at' => now(),
        ];

        if (! $success && $error) {
            $logData['error'] = $error;
        }

        if ($success) {
            Log::info('Marketing WhatsApp attempt logged', $logData);
        } else {
            Log::warning('Marketing WhatsApp attempt failed', $logData);
        }
    }

    /**
     * Get last campaign date from logs
     */
    private function getLastCampaignDate(): ?string
    {
        // This would require a proper marketing campaigns table in a real implementation
        // For now, we'll return null or implement based on log files
        return null;
    }

    /**
     * Get campaigns count for this month from logs
     */
    private function getCampaignsThisMonth(): int
    {
        // This would require a proper marketing campaigns table in a real implementation
        // For now, we'll return 0 or implement based on log files
        return 0;
    }
}
