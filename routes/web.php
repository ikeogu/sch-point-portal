<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/clear-cache', function () {
    if (app()->environment('local')) {
        Artisan::call('cache:clear');
        return 'Application cache cleared!';
    }

    abort(403, 'Unauthorized');
});