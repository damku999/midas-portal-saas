<?php

namespace App\Listeners\Customer;

use App\Events\Customer\CustomerEmailVerified;
use App\Events\Customer\CustomerProfileUpdated;
use App\Events\Customer\CustomerRegistered;
use App\Events\Quotation\QuotationRequested;
use App\Models\CustomerAuditLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateCustomerAuditLog implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(object $event): void
    {
        $auditData = $this->prepareAuditData($event);

        if ($auditData) {
            CustomerAuditLog::create($auditData);
        }
    }

    private function prepareAuditData(object $event): ?array
    {
        $customer = $event->customer ?? null;

        if (! $customer) {
            \Log::warning('Audit log event missing customer', ['event_class' => get_class($event)]);

            return null;
        }

        $eventData = method_exists($event, 'getEventData') ? $event->getEventData() : [];
        $ipAddress = $eventData['ip_address'] ?? request()->ip();
        $userAgent = $eventData['user_agent'] ?? request()->userAgent();

        switch (get_class($event)) {
            case CustomerRegistered::class:
                return [
                    'customer_id' => $customer->id,
                    'action' => 'customer_registered',
                    'description' => "Customer registered via {$event->registrationChannel}",
                    'metadata' => [
                        'registration_channel' => $event->registrationChannel,
                        'registration_ip' => $ipAddress,
                        'user_agent' => $userAgent,
                        'event_metadata' => $event->metadata,
                    ],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ];

            case CustomerProfileUpdated::class:
                return [
                    'customer_id' => $customer->id,
                    'action' => 'profile_updated',
                    'description' => 'Customer profile updated. Changed fields: '.implode(', ', $event->changedFields),
                    'metadata' => [
                        'changed_fields' => $event->changedFields,
                        'original_values' => $event->originalValues,
                        'updated_by' => $event->updatedBy,
                        'has_significant_changes' => method_exists($event, 'hasSignificantChanges') ? $event->hasSignificantChanges() : false,
                    ],
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ];

            case CustomerEmailVerified::class:
                return [
                    'customer_id' => $customer->id,
                    'action' => 'email_verified',
                    'description' => 'Customer email address verified',
                    'metadata' => $eventData,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ];

            case QuotationRequested::class:
                return [
                    'customer_id' => $customer->id,
                    'action' => 'quotation_requested',
                    'description' => 'Customer requested insurance quotation',
                    'metadata' => $eventData,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ];

            default:
                \Log::warning('Unknown event type for customer audit log', [
                    'event_class' => get_class($event),
                    'customer_id' => $customer->id ?? 'unknown',
                ]);

                return null;
        }
    }

    public function failed(object $event, \Throwable $exception): void
    {
        $customer = $event->customer ?? null;
        $eventData = method_exists($event, 'getEventData') ? $event->getEventData() : [];

        \Log::error('Failed to create customer audit log', [
            'event_class' => get_class($event),
            'customer_id' => $customer->id ?? 'unknown',
            'error' => $exception->getMessage(),
            'event_data' => $eventData,
        ]);
    }
}
