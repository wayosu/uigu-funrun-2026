<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function createRaceCategoryForPaymentTests(): RaceCategory
{
    $event = Event::factory()->create();

    return RaceCategory::factory()->create(['event_id' => $event->id]);
}

describe('PaymentController', function () {
    it('displays payment page for pending registration with null expired_at', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => null, // Explicitly null, no expiration
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('payment.show');
        $response->assertViewHas('registration');
    });

    it('displays payment page for pending registration with future expired_at', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('payment.show');
    });

    it('redirects to payment status when registration is already paid', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PaymentVerified,
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertRedirect(route('payment.status', $registration->registration_number));
    });

    it('isExpired returns false when expired_at is null', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => null,
        ]);

        expect($registration->isExpired())->toBeFalse();
    });

    it('isExpired returns false when expired_at is in the future', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => now()->addHours(24),
        ]);

        expect($registration->isExpired())->toBeFalse();
    });

    it('isExpired returns true when status is Expired', function () {
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::Expired,
        ]);

        expect($registration->isExpired())->toBeTrue();
    });

    it('allows uploading payment proof even when expired_at is in the past', function () {
        Storage::fake('local');
        $category = createRaceCategoryForPaymentTests();

        $registration = Registration::factory()->create([
            'race_category_id' => $category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => now()->subHour(),
        ]);

        $response = $this->post(route('payment.store', $registration->registration_number), [
            'payment_method' => PaymentMethod::BankTransfer->value,
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ]);

        $response->assertRedirect(route('payment.status', $registration->registration_number));

        $registration->refresh();

        expect($registration->status)->toBe(PaymentStatus::PaymentVerified);
    });
});
