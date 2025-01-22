<?php

namespace App\Listeners;

use App\Events\SubjectEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\SubjectActivity;
class SubjectEventListener
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
     * @param  SubjectEvent  $event
     * @return void
     */
    public function handle(SubjectEvent $event)
    {
        //
        $sub_activity_obj = new SubjectActivity();
        $sub_activity_obj->addEvent($event->request);
    }
}
