<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Models\Registration;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExpireUnpaidRegistrationsJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Registration::query()
            ->where('status', PaymentStatus::PendingPayment)
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->whereDoesntHave('payments')
            ->chunkById(200, function (Collection $registrations): void {
                foreach ($registrations as $registration) {
                    /** @var Registration $registration */
                    $registration->update(['status' => PaymentStatus::Expired]);
                    $registration->delete();
                }
            });
    }
}
