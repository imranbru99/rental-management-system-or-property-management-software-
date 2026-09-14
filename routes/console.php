<?php

use App\Jobs\ApplyLateFeesJob;
use App\Jobs\GenerateMonthlyRentInvoicesJob;
use App\Jobs\SendLeaseRenewalRemindersJob;
use App\Jobs\SendSavedSearchAlertsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use ImranDevBd\AiHub\Models\AiRequestLog;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new GenerateMonthlyRentInvoicesJob)->monthlyOn(1, '06:00');
Schedule::job(new ApplyLateFeesJob)->dailyAt('07:00');
Schedule::job(new SendSavedSearchAlertsJob)->dailyAt('08:00');
Schedule::job(new SendLeaseRenewalRemindersJob)->dailyAt('09:00');
Schedule::command('model:prune', ['--model' => [AiRequestLog::class]])->daily();
