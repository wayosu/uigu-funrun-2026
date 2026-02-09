<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentVerifiedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentVerified $event): void
    {
        $payment = $event->payment;
        $registration = $payment->registration;
        $participants = $registration->participants;
        $pic = $participants->where('is_pic', true)->first();

        if (! $pic) {
            return;
        }

        $service = app(NotificationService::class);
        $variables = $service->buildTemplateVariables($registration, $pic);

        $whatsappTemplate = $service->getTemplate('whatsapp', 'payment_verified');
        $emailTemplate = $service->getTemplate('email', 'payment_verified');

        $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
        $emailMessage = $service->processTemplate($emailTemplate, $variables);
        $emailSubject = "Payment Verified - {$registration->registration_number}";

        SendWhatsAppNotificationJob::dispatch(
            $pic->phone,
            $whatsappMessage,
            $registration,
            null,
            'payment_verified'
        )->onQueue('notifications');

        SendEmailNotificationJob::dispatch(
            $pic->email,
            $emailSubject,
            $emailMessage,
            $registration,
            null,
            'payment_verified',
            true
        )->onQueue('notifications');
    }
}
