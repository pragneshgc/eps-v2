<?php

use App\Jobs\DeleteCourierLogs;
use App\Jobs\DeleleOrderRequestLogs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DeleteCourierLogs())->dailyAt("12:00");
Schedule::job(new DeleleOrderRequestLogs())->dailyAt("12:00");
