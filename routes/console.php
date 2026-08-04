<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('payments:process-scheduled')->everyMinute();
