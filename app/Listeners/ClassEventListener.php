<?php

namespace App\Listeners;

use App\Events\ClassEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\ClassActivity;
class ClassEventListener
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
     * @param  ClassTeacherEvent  $event
     * @return void
     */
    public function handle(ClassEvent $event)
    {
        //
        $class_activity_obj = new ClassActivity();
        $class_activity_obj->addEvent($event->request);
    }
}
