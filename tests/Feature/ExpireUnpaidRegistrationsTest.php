<?php

use App\Enums\PaymentStatus;
use App\Jobs\ExpireUnpaidRegistrationsJob;
use App\Models\Event;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\Registration;

test('it deletes unpaid registrations that expired after 24 hours', function () {
    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);

    $expiredUnpaid = Registration::factory()->create([
        'race_category_id' => $category->id,
        'status' => PaymentStatus::PendingPayment,
        'expired_at' => now()->subHours(25),
    ]);

    $expiredWithPayment = Registration::factory()->create([
        'race_category_id' => $category->id,
        'status' => PaymentStatus::PendingPayment,
        'expired_at' => now()->subHours(25),
    ]);

    Payment::create([
        'registration_id' => $expiredWithPayment->id,
        'amount' => 150000,
        'proof_path' => 'payment-proofs/test.jpg',
    ]);

    $expiredUploaded = Registration::factory()->create([
        'race_category_id' => $category->id,
        'status' => PaymentStatus::PaymentUploaded,
        'expired_at' => now()->subHours(25),
    ]);

    $notExpired = Registration::factory()->create([
        'race_category_id' => $category->id,
        'status' => PaymentStatus::PendingPayment,
        'expired_at' => now()->addHours(2),
    ]);

    (new ExpireUnpaidRegistrationsJob)->handle();

    $this->assertDatabaseMissing('registrations', ['id' => $expiredUnpaid->id]);
    $this->assertDatabaseHas('registrations', ['id' => $expiredWithPayment->id]);
    $this->assertDatabaseHas('registrations', ['id' => $expiredUploaded->id]);
    $this->assertDatabaseHas('registrations', ['id' => $notExpired->id]);
});
