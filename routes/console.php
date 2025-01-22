<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());

})->purpose('Display an inspiring quote')->hourly();


Schedule::command('app:update-task-status-command')->everySecond();
Schedule::command('app:update-subscription-status-command')->daily();

//Schedule::command('app:test-supervisor-command')->everySecond();