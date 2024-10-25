<?php

namespace App\Event;

use Illuminate\Broadcasting\{Channel,
    InteractsWithSockets,
    PresenceChannel,
    PrivateChannel
};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoginActivity
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $userType;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $userType)
    {
        $this->user = $user;
        $this->userType = $userType;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
