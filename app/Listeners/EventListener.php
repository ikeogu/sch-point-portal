<?php

namespace App\Listeners;

use App\Events\Event;
use App\Models\AuditTrail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventListener
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
     * @param  Event  $event
     * @return void
     */
    public function handle(Event $event)
    {
        //
        $audit_trail_obj = new AuditTrail();
        $audit_trail_obj->addEvent($event->request);
    }
}