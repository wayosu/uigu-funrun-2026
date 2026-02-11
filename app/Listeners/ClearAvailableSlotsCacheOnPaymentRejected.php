<?php

namespace App\Listeners;

use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Events\PaymentRejected;

class ClearAvailableSlotsCacheOnPaymentRejected
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ValidateAvailableSlotsAction $validateSlots
    ) {}

    /**
     * Handle the event.
     */
    public function handle(PaymentRejected $event): void
    {
        // Clear cache for the race category when payment is rejected
        $this->validateSlots->clearCache($event->registration->raceCategory);
    }
}
