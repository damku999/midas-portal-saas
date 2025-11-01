<?php

namespace App\Events;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Lead $lead;
    public ?User $oldUser;
    public User $newUser;

    public function __construct(Lead $lead, ?User $oldUser, User $newUser)
    {
        $this->lead = $lead;
        $this->oldUser = $oldUser;
        $this->newUser = $newUser;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->newUser->id),
        ];
    }
}
