<?php

if (app()->environment(['production'])) {
    // Fetch trades every 15 mins for faster updates
    Schedule::command('trades:fetch-pnl')->everyFifteenMinutes()->withoutOverlapping();
    Schedule::command('trades:fetch-pnl --demo')->everyFifteenMinutes()->withoutOverlapping();

    // Fetch account balance daily at 2:00 AM (local time)
    Schedule::command('account:fetch-balance')->dailyAt('02:00')->timezone('Asia/Manila')->withoutOverlapping();
    Schedule::command('account:fetch-balance --demo')->dailyAt('02:05')->timezone('Asia/Manila')->withoutOverlapping();
}

if (app()->environment('production')) {
    Schedule::command('generate:daily-news')->dailyAt('2:00')->timezone('Asia/Manila');
}