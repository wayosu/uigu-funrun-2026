<?php

use App\Enums\PaymentMethod;
use App\Models\Event;
use App\Models\JerseySize;
use App\Models\RaceCategory;
use App\Models\Registration;
use Database\Seeders\JerseySizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('registration endpoint is rate limited', function () {
    $this->seed(JerseySizeSeeder::class);

    $event = Event::factory()->create(['is_active' => true]);
    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'price_individual' => 150000,
        'price_collective_5' => null,
        'price_collective_10' => null,
        'quota' => 200,
    ]);

    $jerseySize = JerseySize::query()->first();

    $data = [
        'registration_type' => 'individual',
        'pic' => [
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'whatsapp' => '08123456789',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'jersey_size' => $jerseySize->code,
            'identity_number' => '1234567890',
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '081234567890',
            'emergency_relation' => 'Parent',
        ],
    ];

    for ($i = 0; $i < 10; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
            ->post(route('registration.store', $category), $data)
            ->assertStatus(302);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])
        ->post(route('registration.store', $category), $data)
        ->assertStatus(429);
});

test('payment upload endpoint is rate limited', function () {
    Storage::fake('local');

    $event = Event::factory()->create(['is_active' => true]);
    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'price_individual' => 150000,
        'price_collective_5' => null,
        'price_collective_10' => null,
        'quota' => 200,
    ]);

    $registrations = Registration::factory()->count(6)->create([
        'race_category_id' => $category->id,
        'expired_at' => now()->addHours(24),
    ]);

    foreach ($registrations->take(5) as $registration) {
        $this->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])
            ->post(route('payment.store', $registration->registration_number), [
                'payment_method' => PaymentMethod::BankTransfer->value,
                'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
            ])
            ->assertStatus(302);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])
        ->post(route('payment.store', $registrations->last()->registration_number), [
            'payment_method' => PaymentMethod::BankTransfer->value,
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])
        ->assertStatus(429);
});
