<?php

use App\Jobs\ExpireUnpaidRegistrationsJob;
use App\Jobs\PruneNotificationLogsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ExpireUnpaidRegistrationsJob)
    ->hourly()
    ->name('expire-unpaid-registrations')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('queue:prune-failed --hours=168')
    ->daily()
    ->name('prune-failed-jobs')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new PruneNotificationLogsJob)
    ->daily()
    ->name('prune-notification-logs')
    ->withoutOverlapping()
    ->onOneServer();
