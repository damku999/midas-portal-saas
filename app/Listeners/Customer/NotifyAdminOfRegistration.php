<?php

namespace App\Listeners\Customer;

use App\Events\Communication\EmailQueued;
use App\Events\Customer\CustomerRegistered;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminOfRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CustomerRegistered $event): void
    {
        $customer = $event->customer;

        // Get admin users who should be notified
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['admin', 'manager']);
        })->get();

        foreach ($adminUsers as $admin) {
            EmailQueued::dispatch(
                $admin->email,
                $admin->full_name,
                'New Customer Registration',
                'admin_notification',
                [
                    'admin_name' => $admin->full_name,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_mobile' => $customer->mobile,
                    'customer_type' => $customer->customer_type,
                    'registration_channel' => $event->registrationChannel,
                    'registration_date' => $customer->created_at->format('d/m/Y H:i'),
                    'customer_profile_url' => route('customers.show', $customer->id),
                ],
                [], // attachments
                7, // Lower priority for admin notifications
                "admin_notification_customer_{$customer->id}",
                $customer->id
            );
        }
    }

    public function failed(CustomerRegistered $event, \Throwable $exception): void
    {
        \Log::error('Failed to notify admin of customer registration', [
            'customer_id' => $event->customer->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
