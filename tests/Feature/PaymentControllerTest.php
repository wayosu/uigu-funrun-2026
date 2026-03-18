<?php

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PaymentController', function () {
    beforeEach(function () {
        $this->event = Event::factory()->create();
        $this->category = RaceCategory::factory()->create(['event_id' => $this->event->id]);
    });

    it('displays payment page for pending registration with null expired_at', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => null, // Explicitly null, no expiration
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('payment.show');
        $response->assertViewHas('registration');
    });

    it('displays payment page for pending registration with future expired_at', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => now()->addHours(24),
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertStatus(200);
        $response->assertViewIs('payment.show');
    });

    it('redirects to payment status when registration is already paid', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::PaymentVerified,
        ]);

        $response = $this->get(route('payment.show', $registration));

        $response->assertRedirect(route('payment.status', $registration->registration_number));
    });

    it('isExpired returns false when expired_at is null', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => null,
        ]);

        expect($registration->isExpired())->toBeFalse();
    });

    it('isExpired returns false when expired_at is in the future', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::PendingPayment,
            'expired_at' => now()->addHours(24),
        ]);

        expect($registration->isExpired())->toBeFalse();
    });

    it('isExpired returns true when status is Expired', function () {
        $registration = Registration::factory()->create([
            'race_category_id' => $this->category->id,
            'status' => PaymentStatus::Expired,
        ]);

        expect($registration->isExpired())->toBeTrue();
    });
});
