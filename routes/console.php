<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

 

// Send morning verse at 6:00 AM
Schedule::command('bible:send-daily-verse morning')
    ->dailyAt('06:00')
    ->timezone('Africa/Lagos'); // Adjust to your timezone

// Send evening verse at 6:00 PM
Schedule::command('bible:send-daily-verse evening')
    ->dailyAt('18:00')
    ->timezone('Africa/Lagos'); // Adjust to your timezone

//// Optional: Clean up inactive users weekly
//Schedule::call(function () {
//    DB::table('telegram_users')
//        ->where('is_active', false)
//        ->where('updated_at', '<', now()->subMonths(3))
//        ->delete();
//})->weekly();