<?php

namespace App\Events;

use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lead $lead;
    public ?LeadStatus $oldStatus;
    public LeadStatus $newStatus;

    public function __construct(Lead $lead, ?LeadStatus $oldStatus, LeadStatus $newStatus)
    {
        $this->lead = $lead;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('leads.' . $this->lead->id),
        ];
    }
}
