<?php

if (app()->environment(['production', 'staging'])) {
    Schedule::command('trades:fetch-pnl')->hourly();
    Schedule::command('trades:fetch-pnl --demo')->hourlyAt(30);
    Schedule::command('account:fetch-balance')->dailyAt('2:00')->timezone('Asia/Manila');
    Schedule::command('account:fetch-balance --demo')->dailyAt('2:05')->timezone('Asia/Manila');
}

if (app()->environment('production')) {
    Schedule::command('generate:daily-news')->dailyAt('2:00')->timezone('Asia/Manila');
}