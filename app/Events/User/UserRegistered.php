<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;

    public array $metadata;

    public string $registrationChannel;

    public function __construct(User $user, array $metadata = [], string $registrationChannel = 'web')
    {
        $this->user = $user;
        $this->metadata = $metadata;
        $this->registrationChannel = $registrationChannel;
    }

    public function getEventData(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'user_name' => $this->user->full_name,
            'registration_channel' => $this->registrationChannel,
            'registration_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $this->metadata,
        ];
    }

    public function shouldQueue(): bool
    {
        return true;
    }
}
