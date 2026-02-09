<?php

namespace Tests\Feature;

use App\Models\CheckinSetting;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\RegistrationSequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckinFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $event;

    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary settings
        CheckinSetting::create(['pin_code' => '123456', 'is_active' => true]);

        // Prepare Event & Category
        $this->event = Event::factory()->create(['is_active' => true]);
        $this->category = RaceCategory::factory()->create(['event_id' => $this->event->id]);
    }

    public function test_checkin_auth_flow()
    {
        // 1. Redirected if not authorized
        $response = $this->get(route('checkin.scan'));
        $response->assertRedirect(route('checkin.login'));

        // 2. Login Page accessible
        $response = $this->get(route('checkin.login'));
        $response->assertStatus(200);

        // 3. Invalid PIN
        $response = $this->post(route('checkin.authenticate'), ['pin' => '000000']);
        $response->assertSessionHasErrors(['pin']);
        $this->assertFalse(session()->has('checkin_authorized'));

        // 4. Valid PIN
        $response = $this->post(route('checkin.authenticate'), ['pin' => '123456']);
        $response->assertRedirect(route('checkin.scan'));
        $this->assertTrue(session('checkin_authorized'));

        // 5. Access Scan Page
        $response = $this->get(route('checkin.scan'));
        $response->assertStatus(200);
    }

    public function test_checkin_verification_api()
    {
        // Authorize session
        session(['checkin_authorized' => true]);

        // Create Paid Registration
        // Create Seq
        RegistrationSequence::create(['race_category_id' => $this->category->id, 'current_number' => 10]);

        $registration = Registration::create([
            'race_category_id' => $this->category->id,
            'registration_number' => 'REG-0010',
            'pic_name' => 'John Doe',
            'pic_email' => 'john@example.com',
            'pic_phone' => '08123456789',
            'status' => 'paid', // Paid status
            'total_amount' => 100000,
        ]);

        $participant = $registration->participants()->create([
            'race_category_id' => $this->category->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'jersey_size' => 'M',
            'emergency_name' => 'Jane',
            'emergency_phone' => '08987654321',
            'emergency_relation' => 'Wife',
        ]);

        // 1. Verify Valid Registration
        $response = $this->postJson(route('checkin.verify'), ['qr_content' => 'REG-0010']);

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonStructure(['data', 'participants']);

        // Assert Checked In
        $this->assertDatabaseHas('checkins', ['participant_id' => $participant->id]);

        // 2. Double Check-in attempt
        $response = $this->postJson(route('checkin.verify'), ['qr_content' => 'REG-0010']);
        $response->assertStatus(400)
            ->assertJson(['status' => 'error', 'message' => 'All participants in this registration have already checked in.']);
    }

    public function test_cannot_checkin_unpaid_registration()
    {
        session(['checkin_authorized' => true]);

        // Create Pending Registration
        RegistrationSequence::create(['race_category_id' => $this->category->id, 'current_number' => 11]);

        $registration = Registration::create([
            'race_category_id' => $this->category->id,
            'registration_number' => 'REG-0011',
            'pic_name' => 'Unpaid User',
            'pic_email' => 'unpaid@example.com',
            'pic_phone' => '08123456789',
            'status' => 'pending_payment',
            'total_amount' => 100000,
        ]);

        $participant = $registration->participants()->create([
            'race_category_id' => $this->category->id,
            'name' => 'Unpaid User',
            'email' => 'unpaid@example.com',
            'phone' => '08123456789',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'jersey_size' => 'M',
            'emergency_name' => 'Jane',
            'emergency_phone' => '08987654321',
            'emergency_relation' => 'Wife',
        ]);

        $response = $this->postJson(route('checkin.verify'), ['qr_content' => 'REG-0011']);

        $response->assertStatus(400)
            ->assertJson(['status' => 'error']);

        $this->assertDatabaseMissing('checkins', ['participant_id' => $participant->id]);
    }
}
