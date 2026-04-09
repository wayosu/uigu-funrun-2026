<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\PaymentRejected;
use App\Events\PaymentUploaded;
use App\Events\PaymentVerified;
use App\Models\Event as FunRunEvent;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createPendingRegistration(): Registration
{
    $event = FunRunEvent::factory()->create(['is_active' => true]);

    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'bib_start_number' => 1001,
        'bib_end_number' => 2000,
    ]);

    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
        'status' => PaymentStatus::PendingPayment,
        'participants_count' => 1,
    ]);

    Participant::factory()->pic()->create([
        'registration_id' => $registration->id,
    ]);

    return $registration;
}

test('uploading payment proof auto verifies and dispatches two payment events', function () {
    Event::fake([PaymentUploaded::class, PaymentVerified::class]);
    Storage::fake('local');

    $registration = createPendingRegistration();

    $payment = app(PaymentService::class)->uploadPaymentProof(
        $registration,
        PaymentMethod::BankTransfer,
        UploadedFile::fake()->image('proof.jpg')
    );

    $registration->refresh();
    $payment->refresh();

    expect($registration->status)->toBe(PaymentStatus::PaymentVerified)
        ->and($payment->verified_at)->not->toBeNull()
        ->and($payment->rejection_reason)->toBeNull()
        ->and($registration->participants()->first()?->bib_number)->not->toBeNull();

    Event::assertDispatched(PaymentUploaded::class);
    Event::assertDispatched(PaymentVerified::class);
});

test('emergency reject can rollback from verified to pending while keeping bib numbers', function () {
    Event::fake([PaymentRejected::class]);
    Storage::fake('local');

    $registration = createPendingRegistration();

    $payment = app(PaymentService::class)->uploadPaymentProof(
        $registration,
        PaymentMethod::BankTransfer,
        UploadedFile::fake()->image('proof.jpg')
    );

    $participantBib = $registration->participants()->first()?->bib_number;
    $verifier = User::factory()->create();

    app(PaymentService::class)->verifyPayment(
        $payment->fresh(),
        $verifier,
        false,
        'Emergency rollback test'
    );

    $registration->refresh();

    expect($registration->status)->toBe(PaymentStatus::PendingPayment)
        ->and($registration->payment_verified_at)->toBeNull()
        ->and($registration->payment_verified_by)->toBeNull()
        ->and($registration->participants()->first()?->bib_number)->toBe($participantBib)
        ->and(Payment::query()->whereKey($payment->id)->exists())->toBeFalse();

    Event::assertDispatched(PaymentRejected::class);
});
