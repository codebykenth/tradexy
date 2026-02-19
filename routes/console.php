<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('trades:fetch-pnl')->everyFiveMinutes();
