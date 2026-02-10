<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\Participant;
use App\Models\Registration;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send WhatsApp notification via Fonnte
     */
    public function sendWhatsApp(
        string $recipient,
        string $message,
        ?Registration $registration = null,
        ?Participant $participant = null,
        string $type = 'general'
    ): bool {
        // Get WhatsApp channel settings
        $settings = \App\Models\NotificationSetting::firstOrCreate(
            ['channel' => 'whatsapp'],
            [
                'delay_seconds' => 5,
                'max_send_per_minute' => 60,
                'retry_limit' => 3,
                'is_active' => true,
            ]
        );

        if (! $settings || ! $settings->is_active) {
            Log::info('WhatsApp notifications are disabled');

            return false;
        }

        if (! $settings->fonnte_token) {
            Log::error('Fonnte token is not configured');

            return false;
        }

        if (! $registration) {
            Log::error('Notification log requires registration', [
                'channel' => 'whatsapp',
                'recipient' => $recipient,
                'type' => $type,
            ]);

            return false;
        }

        try {
            // Normalize phone number (ensure starts with 62)
            $recipient = $this->normalizePhoneNumber($recipient);

            // Log notification attempt
            $log = NotificationLog::create([
                'registration_id' => $registration->id,
                'channel' => 'whatsapp',
                'type' => $type,
                'recipient' => $recipient,
                'message_body' => $message,
                'status' => 'pending',
            ]);

            // Send via Fonnte API (following official documentation format)
            $response = Http::withHeaders([
                'Authorization' => $settings->fonnte_token,
            ])->asForm()->post('https://api.fonnte.com/send', [
                'target' => $recipient,
                'message' => $message,
                'countryCode' => '62',
                'delay' => $settings->delay_seconds ?? '5-10',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Check if message was sent successfully
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    $log->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'response_data' => $responseData,
                    ]);

                    return true;
                }

                // Handle API error
                $log->update([
                    'status' => 'failed',
                    'response_data' => [
                        'reason' => $responseData['reason'] ?? 'Unknown error from Fonnte API',
                        'response' => $responseData,
                    ],
                ]);

                Log::error('Fonnte API failed', [
                    'recipient' => $recipient,
                    'response' => $responseData,
                ]);

                return false;
            }

            // Handle HTTP error
            $log->update([
                'status' => 'failed',
                'response_data' => [
                    'reason' => 'HTTP error: '.$response->status(),
                    'body' => $response->body(),
                ],
            ]);

            Log::error('Fonnte HTTP error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'recipient' => $recipient,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Fonnte API request failed: '.$e->getMessage(), [
                'recipient' => $recipient,
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'response_data' => [
                        'reason' => $e->getMessage(),
                    ],
                ]);
            }

            return false;
        }
    }

    /**
     * Send email notification
     */
    public function sendEmail(
        string $recipient,
        string $subject,
        string $message,
        ?Registration $registration = null,
        ?Participant $participant = null,
        string $type = 'general',
        bool $attachTicketPdf = false
    ): bool {
        // Get Email channel settings
        $settings = \App\Models\NotificationSetting::firstOrCreate(
            ['channel' => 'email'],
            [
                'delay_seconds' => 0,
                'max_send_per_minute' => 100,
                'retry_limit' => 3,
                'is_active' => true,
            ]
        );

        if (! $settings || ! $settings->is_active) {
            Log::info('Email notifications are disabled');

            return false;
        }

        if (! $registration) {
            Log::error('Notification log requires registration', [
                'channel' => 'email',
                'recipient' => $recipient,
                'type' => $type,
            ]);

            return false;
        }

        try {
            // Log notification attempt
            $log = NotificationLog::create([
                'registration_id' => $registration->id,
                'channel' => 'email',
                'type' => $type,
                'recipient' => $recipient,
                'message_body' => $message,
                'status' => 'pending',
            ]);

            // Send email
            $fromAddress = config('mail.from.address');
            $fromName = config('mail.from.name');

            $ticketPdf = $attachTicketPdf ? $this->generateTicketPdf($registration) : null;
            $ticketFilename = $attachTicketPdf
                ? "e-ticket-{$registration->registration_number}.pdf"
                : null;

            Mail::raw($message, function ($mail) use ($recipient, $subject, $fromAddress, $fromName, $ticketPdf, $ticketFilename) {
                $mail->from($fromAddress, $fromName)
                    ->to($recipient)
                    ->subject($subject);

                if ($ticketPdf && $ticketFilename) {
                    $mail->attachData($ticketPdf, $ticketFilename, [
                        'mime' => 'application/pdf',
                    ]);
                }
            });

            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
                'response_data' => [
                    'status' => 'sent',
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Email exception', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            if (isset($log)) {
                $log->update([
                    'status' => 'failed',
                    'response_data' => [
                        'reason' => $e->getMessage(),
                    ],
                ]);
            }

            return false;
        }
    }

    /**
     * Process template with variables
     */
    public function processTemplate(string $template, array $variables): string
    {
        $message = $template;

        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }

        return $message;
    }

    public function getTemplate(string $channel, string $type): string
    {
        $key = $this->getTemplateCacheKey($channel, $type);

        return Cache::get($key, $this->getDefaultTemplate($channel, $type));
    }

    public function buildTemplateVariables(Registration $registration, ?Participant $participant = null, array $extra = []): array
    {
        $picName = $participant?->name ?? $registration->pic_name ?? '-';
        $category = $registration->raceCategory;
        $event = $category?->event;

        $paymentUrl = route('payment.show', $registration->registration_number);
        $statusUrl = route('payment.status', $registration->registration_number);
        $ticketUrl = route('ticket.show', $registration->registration_number);

        return array_merge([
            'registration_number' => $registration->registration_number,
            'event_name' => $event?->name ?? '-',
            'category' => $category?->name ?? '-',
            'distance' => $category?->distance ?? '-',
            'type' => $registration->registration_type->label(),
            'participants' => (string) $registration->participants()->count(),
            'pic_name' => $picName,
            'total' => number_format((float) $registration->total_amount, 0, ',', '.'),
            'expiry' => $registration->expired_at?->format('d M Y H:i') ?? '-',
            'payment_status' => $registration->status->label(),
            'payment_url' => $paymentUrl,
            'status_url' => $statusUrl,
            'ticket_url' => $ticketUrl,
        ], $extra);
    }

    public function getDefaultTemplate(string $channel, string $type): string
    {
        return match ($channel) {
            'whatsapp' => match ($type) {
                'registration_created' => "PENDAFTARAN BERHASIL ✅\n\n".
                    "Halo {pic_name},\n\n".
                    "Nomor Registrasi: {registration_number}\n".
                    "Event: {event_name}\n".
                    "Kategori: {category} ({distance})\n".
                    "Tipe: {type}\n".
                    "Peserta: {participants}\n".
                    "Total: Rp {total}\n\n".
                    "Silakan lakukan pembayaran sebelum {expiry}.\n\n".
                    "Link pembayaran: {payment_url}\n".
                    "Status pembayaran: {status_url}\n\n".
                    'Terima kasih!',
                'payment_uploaded' => "BUKTI PEMBAYARAN DITERIMA 📸\n\n".
                    "Halo {pic_name},\n\n".
                    "Nomor Registrasi: {registration_number}\n".
                    "Status saat ini: {payment_status}\n\n".
                    "Cek status: {status_url}\n\n".
                    'Terima kasih!',
                'payment_verified' => "PEMBAYARAN TERVERIFIKASI ✅\n\n".
                    "Halo {pic_name},\n\n".
                    "Nomor Registrasi: {registration_number}\n".
                    "Status saat ini: {payment_status}\n\n".
                    "E-Ticket: {ticket_url}\n".
                    "Cek status: {status_url}\n\n".
                    'Sampai jumpa di hari H!',
                'payment_rejected' => "PEMBAYARAN DITOLAK ❌\n\n".
                    "Halo {pic_name},\n\n".
                    "Nomor Registrasi: {registration_number}\n".
                    "Alasan: {reason}\n\n".
                    "Upload ulang bukti pembayaran: {payment_url}\n".
                    "Cek status: {status_url}\n\n".
                    'Terima kasih.',
                default => 'Notification',
            },
            'email' => match ($type) {
                'registration_created' => "Hello {pic_name},\n\n".
                    "Your registration is successful.\n\n".
                    "Registration Number: {registration_number}\n".
                    "Event: {event_name}\n".
                    "Category: {category} ({distance})\n".
                    "Type: {type}\n".
                    "Participants: {participants}\n".
                    "Total: Rp {total}\n".
                    "Expiry: {expiry}\n\n".
                    "Payment link: {payment_url}\n".
                    "Payment status: {status_url}\n\n".
                    'Thank you.',
                'payment_uploaded' => "Hello {pic_name},\n\n".
                    "We have received your payment proof.\n\n".
                    "Registration Number: {registration_number}\n".
                    "Status: {payment_status}\n\n".
                    "Payment status link: {status_url}\n\n".
                    'Thank you.',
                'payment_verified' => "Hello {pic_name},\n\n".
                    "Your payment has been verified.\n\n".
                    "Registration Number: {registration_number}\n".
                    "Status: {payment_status}\n\n".
                    "E-Ticket link: {ticket_url}\n".
                    "Payment status link: {status_url}\n\n".
                    "Your e-ticket PDF is attached to this email.\n\n".
                    'See you on race day!',
                'payment_rejected' => "Hello {pic_name},\n\n".
                    "We could not verify your payment.\n\n".
                    "Registration Number: {registration_number}\n".
                    "Reason: {reason}\n\n".
                    "Re-upload payment proof: {payment_url}\n".
                    "Payment status link: {status_url}\n\n".
                    'Thank you.',
                default => 'Notification',
            },
            default => 'Notification',
        };
    }

    public function getTemplateCacheKey(string $channel, string $type): string
    {
        return "notification.templates.{$channel}.{$type}";
    }

    public function generateTicketPdf(Registration $registration): string
    {
        return Pdf::loadView('ticket.pdf', [
            'registration' => $registration->load('participants', 'raceCategory.event'),
        ])->output();
    }

    /**
     * Normalize phone number to international format (62xxx)
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove spaces, dashes, and other non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        // Ensure starts with 62
        if (! str_starts_with($phone, '62')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        $query = NotificationLog::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'by_channel' => [
                'whatsapp' => $query->where('channel', 'whatsapp')->count(),
                'email' => $query->where('channel', 'email')->count(),
            ],
        ];
    }
}
