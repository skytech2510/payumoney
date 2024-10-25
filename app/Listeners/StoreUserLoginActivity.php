<?php

namespace App\Listeners;

use App\Event\LoginActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class StoreUserLoginActivity
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Event\LoginActivity  $event
     * @return void
     */
    public function handle(LoginActivity $event)
    {
        \App\Models\ActivityLog::create([
            'user_id' =>  $event->user->id,
            'type' => $event->userType,
            'ip_address' => request()->ip(),
            'browser_agent' => request()->header('user-agent'),
        ]);
    }
}
