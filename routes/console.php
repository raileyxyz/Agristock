<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventory:check-expiring')->daily();
Schedule::command('reports:weekly-digest')->weeklyOn(1, '00:00');
Schedule::command('reports:monthly-digest')->monthlyOn(1, '00:00');
