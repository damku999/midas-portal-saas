<?php

namespace App\Http\Controllers;

use App\Contracts\Services\MarketingWhatsAppServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Marketing WhatsApp Controller
 *
 * Handles WhatsApp marketing operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class MarketingWhatsAppController extends AbstractBaseCrudController
{
    public function __construct(/**
     * Marketing WhatsApp Service instance
     */
        private readonly MarketingWhatsAppServiceInterface $marketingWhatsAppService)
    {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'customer-list|customer-edit', 'only' => ['index', 'show']],
            ['permission' => 'customer-edit', 'only' => ['send']],
        ]);
    }

    /**
     * Display the marketing WhatsApp interface
     */
    public function index()
    {
        $this->marketingWhatsAppService->getActiveCustomers();

        return view('marketing.whatsapp.index', ['customers' => $customers]);
    }

    /**
     * Send WhatsApp marketing messages
     */
    public function send(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'message_type' => 'required|in:text,image',
            'message_text' => 'required|string|max:1000',
            'recipients' => 'required|in:all,selected',
            'selected_customers' => 'required_if:recipients,selected|array|min:1',
            'selected_customers.*' => 'exists:customers,id',
            'image' => 'required_if:message_type,image|file|mimes:jpeg,png,jpg,gif,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return $this->redirectWithValidationErrors($validator);
        }

        try {
            // Prepare campaign data
            $campaignData = [
                'message_type' => $request->message_type,
                'message_text' => $request->message_text,
                'recipients' => $request->recipients,
                'selected_customers' => $request->selected_customers,
                'image' => $request->file('image'),
                'sent_by' => auth()->user()->id,
            ];

            // Call the service to send the marketing campaign
            $result = $this->marketingWhatsAppService->sendMarketingCampaign($campaignData);

            // Generate appropriate success/error messages based on the result
            if ($result['failed_count'] > 0) {
                $message = sprintf('Messages sent with some issues. Successfully sent to %s out of %s customers.', $result['success_count'], $result['total_customers']);

                return $this->redirectWithSuccess('marketing.whatsapp.index', $message)
                    ->with('marketing_result', $result);
            }
            $message = sprintf('All marketing messages sent successfully! Sent to %s customers.', $result['success_count']);

            return $this->redirectWithSuccess('marketing.whatsapp.index', $message)
                ->with('marketing_result', $result);

        } catch (\Exception $exception) {
            return $this->redirectWithError('Failed to send marketing messages: '.$exception->getMessage())
                ->withInput();
        }
    }

    /**
     * Preview the customer list based on selection
     */
    public function preview(Request $request)
    {
        $result = $this->marketingWhatsAppService->previewCustomerList(
            $request->recipients,
            $request->selected_customers ?? []
        );

        return response()->json($result);
    }
}
