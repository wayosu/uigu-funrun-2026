<?php

namespace App\Listeners;

use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Events\PaymentVerified;

class ClearAvailableSlotsCacheOnPaymentVerified
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
    public function handle(PaymentVerified $event): void
    {
        // Clear cache for the race category when payment is verified
        $this->validateSlots->clearCache($event->registration->raceCategory);
    }
}
