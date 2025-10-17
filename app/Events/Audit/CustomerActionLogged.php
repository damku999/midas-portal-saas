<?php

namespace App\Events\Audit;

use App\Models\Customer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerActionLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Customer $customer;

    public string $action;

    public string $actionType;

    public array $actionData;

    public ?string $ipAddress;

    public ?string $userAgent;

    public ?int $performedBy;

    public string $context;

    public function __construct(
        Customer $customer,
        string $action,
        string $actionType,
        array $actionData = [],
        ?int $performedBy = null,
        string $context = 'web'
    ) {
        $this->customer = $customer;
        $this->action = $action; // login, profile_update, quotation_request, etc.
        $this->actionType = $actionType; // read, write, delete, auth
        $this->actionData = $actionData;
        $this->ipAddress = request()->ip();
        $this->userAgent = request()->userAgent();
        $this->performedBy = $performedBy;
        $this->context = $context; // web, mobile, api, admin
    }

    public function getEventData(): array
    {
        return [
            'customer_id' => $this->customer->id,
            'customer_email' => $this->customer->email,
            'action' => $this->action,
            'action_type' => $this->actionType,
            'action_data' => $this->actionData,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'performed_by' => $this->performedBy,
            'context' => $this->context,
            'performed_at' => now()->format('Y-m-d H:i:s'),
            'session_id' => session()->getId(),
        ];
    }

    public function isSecurityRelevant(): bool
    {
        $securityActions = ['login', 'logout', 'password_change', 'email_change', 'failed_login'];

        return in_array($this->action, $securityActions);
    }

    public function isHighRisk(): bool
    {
        $highRiskActions = ['data_export', 'bulk_download', 'admin_access', 'suspicious_activity'];

        return in_array($this->action, $highRiskActions);
    }

    public function shouldAlertSecurity(): bool
    {
        return $this->isHighRisk() ||
               ($this->actionType === 'delete' && ! empty($this->actionData)) ||
               str_contains($this->userAgent ?: '', 'bot');
    }

    public function shouldQueue(): bool
    {
        return true;
    }

    public function getQueueName(): string
    {
        return $this->isSecurityRelevant() ? 'audit-priority' : 'audit-normal';
    }
}
