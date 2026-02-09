<?php

use App\Events\PaymentRejected;
use App\Events\PaymentUploaded;
use App\Events\PaymentVerified;
use App\Events\RegistrationCreated;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Listeners\SendPaymentRejectedNotification;
use App\Listeners\SendPaymentUploadedNotification;
use App\Listeners\SendPaymentVerifiedNotification;
use App\Listeners\SendRegistrationCreatedNotification;
use App\Models\Event;
use App\Models\Payment;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

test('registration created event dispatches notification jobs', function () {
    Queue::fake();

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    // Create PIC participant
    $pic = $registration->participants()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'jersey_size' => 'm',
        'emergency_name' => 'Jane Doe',
        'emergency_phone' => '081234567891',
        'emergency_relation' => 'Spouse',
        'is_pic' => true,
    ]);

    app(SendRegistrationCreatedNotification::class)
        ->handle(new RegistrationCreated($registration));

    Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->phone && $job->type === 'registration_created';
    });

    Queue::assertPushed(SendEmailNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->email && $job->type === 'registration_created';
    });
});

test('payment uploaded event dispatches notification jobs', function () {
    Queue::fake();

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    $pic = $registration->participants()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'jersey_size' => 'm',
        'emergency_name' => 'Jane Doe',
        'emergency_phone' => '081234567891',
        'emergency_relation' => 'Spouse',
        'is_pic' => true,
    ]);

    $payment = Payment::factory()->create([
        'registration_id' => $registration->id,
    ]);

    app(SendPaymentUploadedNotification::class)
        ->handle(new PaymentUploaded($registration, $payment));

    Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->phone && $job->type === 'payment_uploaded';
    });
    Queue::assertPushed(SendEmailNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->email && $job->type === 'payment_uploaded';
    });
});

test('payment verified event dispatches notification jobs with BIB numbers', function () {
    Queue::fake();

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    $pic = $registration->participants()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'jersey_size' => 'm',
        'emergency_name' => 'Jane Doe',
        'emergency_phone' => '081234567891',
        'emergency_relation' => 'Spouse',
        'is_pic' => true,
        'bib_number' => '1001',
    ]);

    $payment = Payment::factory()->create([
        'registration_id' => $registration->id,
    ]);

    app(SendPaymentVerifiedNotification::class)
        ->handle(new PaymentVerified($registration, $payment));

    Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->phone && $job->type === 'payment_verified';
    });

    Queue::assertPushed(SendEmailNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->email && $job->type === 'payment_verified' && $job->attachTicketPdf;
    });
});

test('payment rejected event dispatches notification jobs with reason', function () {
    Queue::fake();

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    $pic = $registration->participants()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'jersey_size' => 'm',
        'emergency_name' => 'Jane Doe',
        'emergency_phone' => '081234567891',
        'emergency_relation' => 'Spouse',
        'is_pic' => true,
    ]);

    $payment = Payment::factory()->create([
        'registration_id' => $registration->id,
        'rejection_reason' => 'Invalid payment proof',
    ]);

    app(SendPaymentRejectedNotification::class)
        ->handle(new PaymentRejected($registration, 'Invalid payment proof'));

    Queue::assertPushed(SendWhatsAppNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->phone && $job->type === 'payment_rejected';
    });

    Queue::assertPushed(SendEmailNotificationJob::class, function ($job) use ($pic) {
        return $job->recipient === $pic->email && $job->type === 'payment_rejected';
    });
});

test('jobs are queued to notifications queue', function () {
    Queue::fake();

    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create(['event_id' => $event->id]);
    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
    ]);

    $pic = $registration->participants()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '081234567890',
        'gender' => 'male',
        'birth_date' => '1990-01-01',
        'jersey_size' => 'm',
        'emergency_name' => 'Jane Doe',
        'emergency_phone' => '081234567891',
        'emergency_relation' => 'Spouse',
        'is_pic' => true,
    ]);

    app(SendRegistrationCreatedNotification::class)
        ->handle(new RegistrationCreated($registration));

    Queue::assertPushedOn('notifications', SendWhatsAppNotificationJob::class);
    Queue::assertPushedOn('notifications', SendEmailNotificationJob::class);
});
