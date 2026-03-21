<?php

if (app()->environment(['production'])) {
    // Fetch trades
    Schedule::command('trades:fetch-pnl')->twiceDaily()->timezone('Asia/Manila')->withoutOverlapping();
    Schedule::command('trades:fetch-pnl --demo')->twiceDaily()->timezone('Asia/Manila')->withoutOverlapping();

    // Fetch account balance daily
    Schedule::command('account:fetch-balance')->daily()->timezone('Asia/Manila')->withoutOverlapping();
    Schedule::command('account:fetch-balance --demo')->daily()->timezone('Asia/Manila')->withoutOverlapping();

    // Generate AI news
    Schedule::command('generate:daily-news')->daily()->timezone('Asia/Manila')->withoutOverlapping();
}

Schedule::command('logs:cleanup')->daily()->withoutOverlapping();

// Daily DB Backup to Firebase Storage
Schedule::command('db:backup')->daily()->timezone('Asia/Manila')->withoutOverlapping();
