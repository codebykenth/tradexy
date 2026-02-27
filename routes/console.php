<?php

if (app()->environment('production')) {
    Schedule::command('trades:fetch-pnl')->hourly();
    Schedule::command('account:fetch-balance')->dailyAt('2:00')->timezone('Asia/Manila');
    Schedule::command('generate:daily-news')->dailyAt('11:00')->timezone('Asia/Manila');
}