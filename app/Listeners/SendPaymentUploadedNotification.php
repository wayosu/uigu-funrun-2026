<?php

namespace App\Listeners;

use App\Events\PaymentUploaded;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentUploadedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentUploaded $event): void
    {
        $payment = $event->payment;
        $registration = $payment->registration;
        $pic = $registration->participants()->pic()->first();

        if (! $pic) {
            return;
        }

        $service = app(NotificationService::class);
        $variables = $service->buildTemplateVariables($registration, $pic);

        $whatsappTemplate = $service->getTemplate('whatsapp', 'payment_uploaded');
        $emailTemplate = $service->getTemplate('email', 'payment_uploaded');

        $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
        $emailMessage = $service->processTemplate($emailTemplate, $variables);
        $emailSubject = "Payment Proof Received - {$registration->registration_number}";

        SendWhatsAppNotificationJob::dispatch(
            $pic->phone,
            $whatsappMessage,
            $registration,
            null,
            'payment_uploaded'
        )->onQueue('notifications');

        SendEmailNotificationJob::dispatch(
            $pic->email,
            $emailSubject,
            $emailMessage,
            $registration,
            null,
            'payment_uploaded'
        )->onQueue('notifications');
    }
}
