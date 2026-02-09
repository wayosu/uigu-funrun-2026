<?php

use App\Jobs\PruneNotificationLogsJob;
use App\Models\Event;
use App\Models\NotificationLog;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it prunes notification logs older than retention window', function () {
    config(['notifications.log_retention_days' => 30]);

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);

    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    $oldLog = NotificationLog::create([
        'registration_id' => $registration->id,
        'channel' => 'email',
        'type' => 'payment_verified',
        'recipient' => 'test@example.com',
        'message_body' => 'Old message',
        'status' => 'sent',
        'created_at' => now()->subDays(40),
        'updated_at' => now()->subDays(40),
    ]);

    $recentLog = NotificationLog::create([
        'registration_id' => $registration->id,
        'channel' => 'whatsapp',
        'type' => 'registration_created',
        'recipient' => '6281234567890',
        'message_body' => 'Recent message',
        'status' => 'sent',
        'created_at' => now()->subDays(5),
        'updated_at' => now()->subDays(5),
    ]);

    (new PruneNotificationLogsJob())->handle();

    $this->assertDatabaseMissing('notification_logs', ['id' => $oldLog->id]);
    $this->assertDatabaseHas('notification_logs', ['id' => $recentLog->id]);
});
