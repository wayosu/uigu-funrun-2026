<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_migrate_fresh_and_tables_exist()
    {
        $this->artisan('migrate:fresh');

        $tables = [
            'events',
            'race_categories',
            'registrations',
            'participants',
            'registration_sequences',
            'payments',
            'payment_settings',
            'checkin_settings',
            'notification_settings',
            'notification_logs',
            'checkins',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasTable($table),
                "Table $table does not exist"
            );
        }
    }

    public function test_it_can_create_event_and_race_category()
    {
        $event = Event::create([
            'name' => 'Fun Run 2026',
            'date' => '2026-12-31',
            'location' => 'Jakarta',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('events', ['name' => 'Fun Run 2026']);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K',
            'quota' => 100,
            'price' => 150000,
            'registration_type' => 'individual',
            'prefix' => '5K',
        ]);

        $this->assertDatabaseHas('race_categories', ['name' => '5K']);
        $this->assertTrue($event->raceCategories->contains($category));
    }

    public function test_it_can_create_registration_with_participants()
    {
        $event = Event::create([
            'name' => 'Fun Run 2026',
            'date' => '2026-12-31',
            'location' => 'Jakarta',
        ]);

        $category = RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K',
            'quota' => 100,
            'price' => 200000,
            'registration_type' => 'individual',
            'prefix' => '10K',
        ]);

        $registration = Registration::create([
            'registration_number' => '10K-0001',
            'race_category_id' => $category->id,
            'pic_name' => 'John Doe',
            'pic_email' => 'john@example.com',
            'pic_phone' => '08123456789',
            'total_amount' => 200000,
            'status' => 'pending_payment',
        ]);

        $this->assertDatabaseHas('registrations', ['registration_number' => '10K-0001']);

        $participant = Participant::create([
            'registration_id' => $registration->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'jersey_size' => 'L',
            'emergency_name' => 'Jane Doe',
            'emergency_phone' => '08987654321',
            'emergency_relation' => 'Wife',
            'is_pic' => true,
        ]);

        $this->assertDatabaseHas('participants', ['name' => 'John Doe']);
        $this->assertTrue($registration->participants->contains($participant));
        $this->assertEquals($registration->id, $participant->registration->id);
    }
}
