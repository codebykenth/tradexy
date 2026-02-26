<?php

if (app()->environment('production')) {
    Schedule::command('trades:fetch-pnl')->everyFiveMinutes();
    Schedule::command('account:fetch-balance')->daily();
    Schedule::command('generate:daily-news')->dailyAt('08:00');
}