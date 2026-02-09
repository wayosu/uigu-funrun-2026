<?php

use App\Enums\RegistrationType;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('notification templates render with links', function (): void {
    $event = Event::factory()->create();
    $category = RaceCategory::factory()->create([
        'event_id' => $event->id,
    ]);

    $registration = Registration::factory()->create([
        'race_category_id' => $category->id,
        'registration_type' => RegistrationType::Individual,
        'participants_count' => 1,
        'pic_name' => 'Test User',
        'pic_email' => 'test@example.com',
        'pic_phone' => '08123456789',
    ]);

    $service = app(NotificationService::class);
    $variables = $service->buildTemplateVariables($registration, null, [
        'reason' => 'Test reason',
    ]);

    $template = 'Pay: {payment_url} | Status: {status_url} | Ticket: {ticket_url} | Reason: {reason}';
    $message = $service->processTemplate($template, $variables);

    expect($message)->toContain(route('payment.show', $registration->registration_number));
    expect($message)->toContain(route('payment.status', $registration->registration_number));
    expect($message)->toContain(route('ticket.show', $registration->registration_number));
    expect($message)->toContain('Test reason');
});
