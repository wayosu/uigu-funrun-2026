<?php

namespace App\Listeners;

use App\Events\PaymentRejected;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentRejectedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentRejected $event): void
    {
        $registration = $event->registration;
        $pic = $registration->participants()->pic()->first();

        if (! $pic) {
            return;
        }

        $service = app(NotificationService::class);
        $variables = $service->buildTemplateVariables($registration, $pic, [
            'reason' => $event->reason,
        ]);

        $whatsappTemplate = $service->getTemplate('whatsapp', 'payment_rejected');
        $emailTemplate = $service->getTemplate('email', 'payment_rejected');

        $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
        $emailMessage = $service->processTemplate($emailTemplate, $variables);
        $emailSubject = "Payment Rejected - {$registration->registration_number}";

        SendWhatsAppNotificationJob::dispatch(
            $pic->phone,
            $whatsappMessage,
            $registration,
            null,
            'payment_rejected'
        )->onQueue('notifications');

        SendEmailNotificationJob::dispatch(
            $pic->email,
            $emailSubject,
            $emailMessage,
            $registration,
            null,
            'payment_rejected'
        )->onQueue('notifications');
    }
}
