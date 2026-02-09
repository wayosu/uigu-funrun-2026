<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Models\JerseySize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_displays_event()
    {
        $event = \App\Models\Event::factory()->create([
            'name' => 'Fun Run 2026',
            'is_active' => true,
        ]);
        $category = \App\Models\RaceCategory::factory()->create([
            'event_id' => $event->id,
            'name' => '5K Run',
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Fun Run 2026')
            ->assertSee('5K Run');
    }

    public function test_registration_flow_individual()
    {
        $this->seed(\Database\Seeders\JerseySizeSeeder::class);

        $event = \App\Models\Event::factory()->create(['is_active' => true]);
        $category = \App\Models\RaceCategory::factory()->create([
            'event_id' => $event->id,
            'price_individual' => 150000,
            'price_collective_5' => null,
            'price_collective_10' => null,
            'quota' => 100,
        ]);
        $jerseySize = JerseySize::query()->first();

        // Visit Form
        $this->get(route('registration.form', $category))
            ->assertStatus(200)
            ->assertSee($category->name);

        // Submit Form
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

        $response = $this->post(route('registration.store', $category), $data);

        $response->assertSessionHasNoErrors();

        $registration = \App\Models\Registration::first();
        $this->assertNotNull($registration);
        $this->assertEquals('pending_payment', $registration->status);

        $response->assertRedirect(route('payment.show', $registration->registration_number));

        // Visit Payment Page
        $this->get(route('payment.show', $registration->registration_number))
            ->assertStatus(200)
            ->assertSee('Complete Your Payment');

        // Upload Proof
        Storage::fake('public');
        $file = UploadedFile::fake()->image('proof.jpg');

        $this->post(route('payment.store', $registration->registration_number), [
            'payment_method' => PaymentMethod::BankTransfer->value,
            'payment_proof' => $file,
        ])->assertRedirect(route('payment.status', $registration->registration_number));

        $registration->refresh();
        $this->assertEquals('payment_uploaded', $registration->status);

        $this->assertDatabaseHas('payments', [
            'registration_id' => $registration->id,
            'status' => 'pending',
        ]);
    }
}
