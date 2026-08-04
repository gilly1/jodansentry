<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('payments:process-scheduled')->everyMinute();
Schedule::command('db:backup')->dailyAt('02:00');
