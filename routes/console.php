<?php

if (app()->environment(['production'])) {
    // Fetch trades every 5 mins for faster updates
    Schedule::command('trades:fetch-pnl')->everyFiveMinutes()->withoutOverlapping();
    Schedule::command('trades:fetch-pnl --demo')->everyFiveMinutes()->withoutOverlapping();

    // Fetch account balance daily (local time)
    Schedule::command('account:fetch-balance')->daily()->timezone('Asia/Manila')->withoutOverlapping();
    Schedule::command('account:fetch-balance --demo')->daily()->timezone('Asia/Manila')->withoutOverlapping();

    Schedule::command('logs:cleanup')->daily()->withoutOverlapping();
}

if (app()->environment('production')) {
    Schedule::command('generate:daily-news')->dailyAt('2:00')->timezone('Asia/Manila');
}
