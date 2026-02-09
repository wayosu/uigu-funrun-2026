<?php

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\JerseySize;
use Database\Seeders\JerseySizeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\post;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

function registrationPayload(JerseySize $jerseySize, string $identityNumber, array $overrides = []): array
{
    return array_replace_recursive([
        'registration_type' => 'individual',
        'pic' => [
            'full_name' => 'John Doe',
            'email' => 'john@gmail.com',
            'whatsapp' => '08123456789',
            'gender' => 'male',
            'date_of_birth' => '1990-01-01',
            'jersey_size' => $jerseySize->code,
            'identity_number' => $identityNumber,
            'emergency_contact_name' => 'Jane Doe',
            'emergency_contact_phone' => '081234567890',
            'emergency_relation' => 'Parent',
        ],
        'website' => '',
    ], $overrides);
}

it('requires pic identity number', function () {
    seed(JerseySizeSeeder::class);

    $event = Event::factory()->create(['is_active' => true]);
    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'is_active' => true,
    ]);

    $jerseySize = JerseySize::query()->firstOrFail();

    $data = registrationPayload($jerseySize, '', [
        'pic' => ['identity_number' => ''],
    ]);

    post(route('registration.store', $category), $data)
        ->assertSessionHasErrors(['pic.identity_number']);
});

it('rejects duplicate identity number within the same category', function () {
    seed(JerseySizeSeeder::class);

    $event = Event::factory()->create(['is_active' => true]);
    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'is_active' => true,
    ]);

    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    Participant::factory()->create([
        'registration_id' => $registration->id,
        'identity_number' => 'ID-12345',
    ]);

    $jerseySize = JerseySize::query()->firstOrFail();

    $data = registrationPayload($jerseySize, 'ID-12345');

    post(route('registration.store', $category), $data)
        ->assertSessionHasErrors(['pic.identity_number']);
});

it('allows the same identity number in different categories', function () {
    seed(JerseySizeSeeder::class);

    $event = Event::factory()->create(['is_active' => true]);
    $categoryA = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'is_active' => true,
    ]);
    $categoryB = RaceCategory::factory()->create([
        'event_id' => $event->id,
        'is_active' => true,
    ]);

    $registration = Registration::factory()->create([
        'race_category_id' => $categoryA->id,
    ]);

    Participant::factory()->create([
        'registration_id' => $registration->id,
        'identity_number' => 'ID-99999',
    ]);

    $jerseySize = JerseySize::query()->firstOrFail();

    $data = registrationPayload($jerseySize, 'ID-99999');

    post(route('registration.store', $categoryB), $data)
        ->assertSessionHasNoErrors();
});
