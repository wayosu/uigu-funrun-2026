<?php

namespace App\Listeners;

use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Events\RegistrationCreated;

class ClearAvailableSlotsCache
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
    public function handle(RegistrationCreated $event): void
    {
        // Clear cache for the race category when new registration is created
        $this->validateSlots->clearCache($event->registration->raceCategory);
    }
}
