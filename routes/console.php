<?php

if (app()->environment(['production'])) {
    // Fetch trades every 5 mins for faster updates
    Schedule::command('trades:fetch-pnl')->everyFiveMinutes()->withoutOverlapping();
    Schedule::command('trades:fetch-pnl --demo')->everyFiveMinutes()->withoutOverlapping();

    // Fetch account balance daily (local time)
    Schedule::command('account:fetch-balance')->daily()->timezone('Asia/Manila')->withoutOverlapping();
    Schedule::command('account:fetch-balance --demo')->daily()->timezone('Asia/Manila')->withoutOverlapping();

    Schedule::command('generate:daily-news')->daily()->timezone('Asia/Manila')->withoutOverlapping();
}

Schedule::command('logs:cleanup')->daily()->withoutOverlapping();

// Daily DB Backup to Firebase Storage
Schedule::command('db:backup')->daily()->timezone('Asia/Manila')->withoutOverlapping();
