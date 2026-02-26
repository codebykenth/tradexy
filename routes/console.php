<?php

if (app()->environment('production')) {
    Schedule::command('trades:fetch-pnl')->hourly();
    Schedule::command('account:fetch-balance')->dailyAt('1:00')->timezone('Asia/Manila');
    Schedule::command('generate:daily-news')->dailyAt('08:00')->timezone('Asia/Manila');
}