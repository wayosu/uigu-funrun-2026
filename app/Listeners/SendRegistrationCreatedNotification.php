<?php

namespace App\Listeners;

use App\Events\RegistrationCreated;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendRegistrationCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(RegistrationCreated $event): void
    {
        $registration = $event->registration;
        $pic = $registration->participants()->pic()->first();

        if (! $pic) {
            return;
        }

        $service = app(NotificationService::class);
        $variables = $service->buildTemplateVariables($registration, $pic);

        $whatsappTemplate = $service->getTemplate('whatsapp', 'registration_created');
        $emailTemplate = $service->getTemplate('email', 'registration_created');

        $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
        $emailMessage = $service->processTemplate($emailTemplate, $variables);
        $emailSubject = "Registration Successful - {$registration->registration_number}";

        // Dispatch notification jobs
        SendWhatsAppNotificationJob::dispatch(
            $pic->phone,
            $whatsappMessage,
            $registration,
            null,
            'registration_created'
        )->onQueue('notifications');

        SendEmailNotificationJob::dispatch(
            $pic->email,
            $emailSubject,
            $emailMessage,
            $registration,
            null,
            'registration_created'
        )->onQueue('notifications');
    }
}
