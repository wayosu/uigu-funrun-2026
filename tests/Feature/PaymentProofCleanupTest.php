<?php

use App\Models\Event;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('deleting a payment removes its proof file', function () {
    Storage::fake('local');

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create(['race_category_id' => $category->id]);

    $proofPath = 'payment-proofs/test-proof.jpg';
    Storage::disk('local')->put($proofPath, 'file-content');

    $payment = Payment::create([
        'registration_id' => $registration->id,
        'amount' => 150000,
        'proof_path' => $proofPath,
    ]);

    $payment->delete();

    Storage::disk('local')->assertMissing($proofPath);
});
