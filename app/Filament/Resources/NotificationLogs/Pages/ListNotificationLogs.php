<?php

namespace App\Filament\Resources\NotificationLogs\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\NotificationLogs\NotificationLogResource;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Registration;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListNotificationLogs extends ListRecords
{
    protected static string $resource = NotificationLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resendMissing')
                ->label('Resend Missing Notifications')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Resend Missing Notifications')
                ->modalDescription('This will search for verified payments that did not receive notifications (e.g., when Redis was down) and resend them.')
                ->form([
                    Select::make('type')
                        ->label('Notification Type')
                        ->options([
                            'payment_verified' => 'Payment Verified (E-Ticket)',
                            'payment_uploaded' => 'Payment Uploaded',
                            'registration_created' => 'Registration Created',
                        ])
                        ->default('payment_verified')
                        ->required()
                        ->helperText('Select which type of notification to resend'),
                ])
                ->action(function (array $data) {
                    $type = $data['type'];

                    // Find registrations without sent notifications
                    $query = Registration::query();

                    if ($type === 'payment_verified') {
                        $query->where('status', PaymentStatus::PaymentVerified);
                    } elseif ($type === 'payment_uploaded') {
                        $query->where('status', PaymentStatus::PaymentUploaded);
                    }

                    $registrations = $query->whereDoesntHave('notificationLogs', function ($subQuery) use ($type) {
                        $subQuery->where('type', $type)->where('status', 'sent');
                    })->with(['participants' => function ($q) {
                        $q->where('is_pic', true);
                    }])->get();

                    if ($registrations->isEmpty()) {
                        Notification::make()
                            ->title('No Missing Notifications Found')
                            ->body("All {$type} notifications have been sent successfully.")
                            ->success()
                            ->send();

                        return;
                    }

                    $service = app(NotificationService::class);
                    $queuedCount = 0;

                    foreach ($registrations as $registration) {
                        $pic = $registration->participants()->where('is_pic', true)->first();

                        if (! $pic) {
                            continue;
                        }

                        $variables = $service->buildTemplateVariables($registration, $pic);

                        $whatsappTemplate = $service->getTemplate('whatsapp', $type);
                        $emailTemplate = $service->getTemplate('email', $type);

                        $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
                        $emailMessage = $service->processTemplate($emailTemplate, $variables);

                        $emailSubject = match ($type) {
                            'registration_created' => "Registration Successful - {$registration->registration_number}",
                            'payment_uploaded' => "Payment Proof Received - {$registration->registration_number}",
                            'payment_verified' => "Payment Verified - {$registration->registration_number}",
                            default => "Notification - {$registration->registration_number}",
                        };

                        SendWhatsAppNotificationJob::dispatch(
                            $pic->phone,
                            $whatsappMessage,
                            $registration,
                            null,
                            $type
                        )->onQueue('notifications');

                        $attachTicket = $type === 'payment_verified';

                        SendEmailNotificationJob::dispatch(
                            $pic->email,
                            $emailSubject,
                            $emailMessage,
                            $registration,
                            null,
                            $type,
                            $attachTicket
                        )->onQueue('notifications');

                        $queuedCount++;
                    }

                    Notification::make()
                        ->title('Notifications Queued')
                        ->body("Queued {$queuedCount} {$type} notification(s) for sending.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
