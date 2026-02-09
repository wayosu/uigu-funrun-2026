# API & Notification Design — Fun Run Event Registration

## Overview

Dokumen ini menjelaskan secara detail sistem notifikasi dan strategi anti-ban/anti-spam untuk WhatsApp dan Email delivery.

---

## Table of Contents

1. [Notification Architecture](#1-notification-architecture)
2. [WhatsApp Integration (Fonnte)](#2-whatsapp-integration-fonnte)
3. [Email System](#3-email-system)
4. [Queue Strategy](#4-queue-strategy)
5. [Anti-Spam & Anti-Ban Strategy](#5-anti-spam--anti-ban-strategy)
6. [Notification Templates](#6-notification-templates)
7. [Delivery Monitoring](#7-delivery-monitoring)
8. [Error Handling & Retry Logic](#8-error-handling--retry-logic)
9. [Testing Strategy](#9-testing-strategy)

---

## 1. Notification Architecture

### Design Principles

1. **Asynchronous**: Semua notifikasi dikirim via queue (never synchronous)
2. **Reliable**: Retry mechanism dengan exponential backoff
3. **Monitored**: Complete logging untuk audit & debugging
4. **Throttled**: Rate limiting untuk avoid spam detection
5. **Flexible**: Template-based dengan variable substitution

### System Flow

```
┌─────────────────┐
│  Trigger Event  │
│  (Registration, │
│   Payment, etc) │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│  Event Listener             │
│  (e.g., SendRegistration    │
│   Confirmation)             │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Dispatch Notification Jobs │
│  - SendWhatsAppNotificationJob
│  - SendEmailNotificationJob │
└────────┬────────────────────┘
         │
         ├─────────────────────┐
         │                     │
         ▼                     ▼
┌──────────────────┐  ┌────────────────┐
│  WhatsApp Queue  │  │   Email Queue  │
│  (notifications) │  │  (notifications)│
└────────┬─────────┘  └────────┬───────┘
         │                     │
         │ (with delay)        │ (with delay)
         │                     │
         ▼                     ▼
┌──────────────────┐  ┌────────────────┐
│  Fonnte API      │  │   SMTP Server  │
└────────┬─────────┘  └────────┬───────┘
         │                     │
         ▼                     ▼
┌──────────────────┐  ┌────────────────┐
│ Notification Log │  │ Notification Log│
│ (success/failed) │  │ (success/failed)│
└──────────────────┘  └────────────────┘
```

---

## 2. WhatsApp Integration (Fonnte)

### Fonnte API Overview

**Base URL**: `https://api.fonnte.com`  
**Authentication**: Bearer token (API Key)

### API Endpoints

#### Send Message
```http
POST /send
Authorization: {api_key}
Content-Type: application/json

{
    "target": "6281234567890",
    "message": "Your message here",
    "countryCode": "62",
    "device": "{device_id}"
}
```

**Response (Success)**:
```json
{
    "status": true,
    "message": "Message sent successfully",
    "id": "msg_123456"
}
```

**Response (Failed)**:
```json
{
    "status": false,
    "reason": "Device offline"
}
```

### Custom Notification Channel

```php
// app/Notifications/Channels/FonnteChannel.php
namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $message = $notification->toFonnte($notifiable);
        
        if (!$message) {
            return;
        }
        
        $settings = app(NotificationSettingRepository::class)->get();
        
        if (!$settings->whatsapp_enabled) {
            Log::info('WhatsApp notifications are disabled');
            return;
        }
        
        $response = Http::withHeaders([
            'Authorization' => $settings->fonnte_api_key,
        ])->post('https://api.fonnte.com/send', [
            'target' => $this->formatPhoneNumber($message->recipient),
            'message' => $message->content,
            'countryCode' => '62',
            'device' => $settings->fonnte_device_id,
        ]);
        
        if ($response->successful() && $response->json('status')) {
            Log::info('WhatsApp sent successfully', [
                'recipient' => $message->recipient,
                'response_id' => $response->json('id'),
            ]);
        } else {
            Log::error('WhatsApp failed', [
                'recipient' => $message->recipient,
                'reason' => $response->json('reason', 'Unknown error'),
            ]);
            
            throw new \Exception('WhatsApp delivery failed: ' . $response->json('reason'));
        }
    }
    
    private function formatPhoneNumber(string $phone): string
    {
        // Remove leading 0, add country code
        $phone = preg_replace('/^0/', '', $phone);
        
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
}
```

### Notification Class Example

```php
// app/Notifications/RegistrationConfirmedNotification.php
namespace App\Notifications;

use App\Models\Registration;
use App\Notifications\Channels\FonnteChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    public function __construct(
        public Registration $registration
    ) {
        $this->onQueue('notifications');
    }
    
    public function via($notifiable): array
    {
        $channels = [];
        
        $settings = app(NotificationSettingRepository::class)->get();
        
        if ($settings->whatsapp_enabled) {
            $channels[] = FonnteChannel::class;
        }
        
        if ($settings->email_enabled) {
            $channels[] = 'mail';
        }
        
        return $channels;
    }
    
    public function toFonnte($notifiable): ?object
    {
        $participant = $this->registration->participants()
            ->where('is_pic', true)
            ->first();
        
        if (!$participant) {
            return null;
        }
        
        $settings = app(NotificationSettingRepository::class)->get();
        
        $message = $this->replaceVariables(
            $settings->registration_template_wa,
            $participant
        );
        
        return (object) [
            'recipient' => $participant->whatsapp,
            'content' => $message,
        ];
    }
    
    public function toMail($notifiable): MailMessage
    {
        $participant = $this->registration->participants()
            ->where('is_pic', true)
            ->first();
        
        $settings = app(NotificationSettingRepository::class)->get();
        
        $message = $this->replaceVariables(
            $settings->registration_template_email,
            $participant
        );
        
        return (new MailMessage)
            ->subject('Registration Confirmation - ' . $this->registration->event->name)
            ->greeting('Hello ' . $participant->full_name . '!')
            ->line($message)
            ->action('View Registration', url('/payment/' . $this->registration->registration_number));
    }
    
    private function replaceVariables(string $template, $participant): string
    {
        $variables = [
            '{name}' => $participant->full_name,
            '{registration_number}' => $this->registration->registration_number,
            '{event_name}' => $this->registration->event->name,
            '{category_name}' => $this->registration->raceCategory->name,
            '{amount}' => 'Rp ' . number_format($this->registration->total_amount, 0, ',', '.'),
            '{payment_deadline}' => $this->registration->expired_at->format('d M Y H:i'),
        ];
        
        return str_replace(
            array_keys($variables),
            array_values($variables),
            $template
        );
    }
}
```

---

## 3. Email System

### SMTP Configuration

```php
// config/mail.php
'default' => env('MAIL_MAILER', 'smtp'),

'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
        'port' => env('MAIL_PORT', 587),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'timeout' => null,
    ],
],

'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'noreply@funrun.com'),
    'name' => env('MAIL_FROM_NAME', 'Fun Run Event'),
],
```

### Email Templates

```blade
{{-- resources/views/emails/registration-confirmed.blade.php --}}
<x-mail::message>
# Registration Confirmed!

Hello **{{ $participant->full_name }}**,

Thank you for registering for **{{ $registration->event->name }}**.

## Registration Details

- **Registration Number**: {{ $registration->registration_number }}
- **Category**: {{ $registration->raceCategory->name }}
- **Participants**: {{ $registration->total_participants }}
- **Total Amount**: Rp {{ number_format($registration->total_amount, 0, ',', '.') }}

## Next Steps

1. Complete payment within **24 hours**
2. Upload payment proof
3. Wait for admin verification
4. Receive your e-ticket

<x-mail::button :url="$paymentUrl">
Complete Payment
</x-mail::button>

**Payment Deadline**: {{ $registration->expired_at->format('d F Y, H:i') }} WIB

---

If you have any questions, please contact us.

Thanks,<br>
{{ config('app.name') }} Team
</x-mail::message>
```

### Email with Attachment (E-Ticket)

```php
public function toMail($notifiable): MailMessage
{
    $ticketPath = Storage::disk('private')->path($this->participant->ticket_path);
    
    return (new MailMessage)
        ->subject('Your E-Ticket - ' . $this->registration->event->name)
        ->greeting('Congratulations ' . $this->participant->full_name . '!')
        ->line('Your payment has been verified.')
        ->line('**BIB Number**: ' . $this->participant->bib_number)
        ->line('Please find your e-ticket attached to this email.')
        ->attach($ticketPath, [
            'as' => 'eticket-' . $this->participant->bib_number . '.pdf',
            'mime' => 'application/pdf',
        ])
        ->line('Show this e-ticket during race pack collection.');
}
```

---

## 4. Queue Strategy

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 300,
        'block_for' => null,
        'after_commit' => true, // Wait for DB transaction commit
    ],
],
```

### Notification Job

```php
// app/Jobs/SendWhatsAppNotificationJob.php
namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Registration;
use App\Services\NotificationSettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 5;
    public int $maxExceptions = 3;
    public int $timeout = 60;
    
    public function __construct(
        public string $recipient,
        public string $message,
        public ?int $registrationId = null,
        public ?int $participantId = null,
    ) {
        $this->onQueue('notifications');
    }
    
    public function handle(): void
    {
        $settings = app(NotificationSettingRepository::class)->get();
        
        // Create log
        $log = NotificationLog::create([
            'registration_id' => $this->registrationId,
            'participant_id' => $this->participantId,
            'channel' => 'whatsapp',
            'recipient' => $this->recipient,
            'message' => $this->message,
            'status' => 'pending',
            'retry_count' => $this->attempts() - 1,
        ]);
        
        try {
            // Random delay (anti-spam)
            $delay = rand(
                $settings->whatsapp_delay_seconds,
                $settings->whatsapp_delay_seconds + 5
            );
            sleep($delay);
            
            // Send via Fonnte
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => $settings->fonnte_api_key,
                ])
                ->post('https://api.fonnte.com/send', [
                    'target' => $this->formatPhoneNumber($this->recipient),
                    'message' => $this->message,
                    'countryCode' => '62',
                    'device' => $settings->fonnte_device_id,
                ]);
            
            if ($response->successful() && $response->json('status')) {
                // Success
                $log->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                
                Log::info('WhatsApp sent', [
                    'recipient' => $this->recipient,
                    'log_id' => $log->id,
                ]);
            } else {
                // Failed
                $reason = $response->json('reason', 'Unknown error');
                
                $log->update([
                    'status' => 'failed',
                    'failed_reason' => $reason,
                ]);
                
                throw new \Exception('Fonnte API error: ' . $reason);
            }
            
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'failed_reason' => $e->getMessage(),
            ]);
            
            Log::error('WhatsApp failed', [
                'recipient' => $this->recipient,
                'error' => $e->getMessage(),
                'log_id' => $log->id,
            ]);
            
            // Retry if below limit
            if ($this->attempts() < $this->tries) {
                $this->release($this->calculateBackoff());
            } else {
                Log::error('WhatsApp max retries reached', [
                    'recipient' => $this->recipient,
                    'log_id' => $log->id,
                ]);
            }
        }
    }
    
    private function calculateBackoff(): int
    {
        // Exponential backoff: 1min, 2min, 4min, 8min
        return pow(2, $this->attempts()) * 60;
    }
    
    private function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $phone = preg_replace('/^0/', '', $phone);
        
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::critical('WhatsApp job failed permanently', [
            'recipient' => $this->recipient,
            'exception' => $exception->getMessage(),
        ]);
        
        // Update log
        NotificationLog::query()
            ->where('recipient', $this->recipient)
            ->where('channel', 'whatsapp')
            ->whereNull('sent_at')
            ->latest()
            ->first()
            ?->update([
                'status' => 'failed',
                'failed_reason' => 'Max retries exceeded: ' . $exception->getMessage(),
            ]);
    }
}
```

### Email Job

```php
// app/Jobs/SendEmailNotificationJob.php
namespace App\Jobs;

use App\Models\NotificationLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public int $tries = 3;
    public int $timeout = 120;
    
    public function __construct(
        public string $recipient,
        public Mailable $mailable,
        public ?int $registrationId = null,
        public ?int $participantId = null,
    ) {
        $this->onQueue('notifications');
    }
    
    public function handle(): void
    {
        $settings = app(NotificationSettingRepository::class)->get();
        
        // Create log
        $log = NotificationLog::create([
            'registration_id' => $this->registrationId,
            'participant_id' => $this->participantId,
            'channel' => 'email',
            'recipient' => $this->recipient,
            'message' => 'Email notification',
            'status' => 'pending',
            'retry_count' => $this->attempts() - 1,
        ]);
        
        try {
            // Random delay
            $delay = rand(
                $settings->email_delay_seconds,
                $settings->email_delay_seconds + 3
            );
            sleep($delay);
            
            // Send email
            Mail::to($this->recipient)->send($this->mailable);
            
            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
            
            Log::info('Email sent', [
                'recipient' => $this->recipient,
                'log_id' => $log->id,
            ]);
            
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'failed_reason' => $e->getMessage(),
            ]);
            
            Log::error('Email failed', [
                'recipient' => $this->recipient,
                'error' => $e->getMessage(),
                'log_id' => $log->id,
            ]);
            
            if ($this->attempts() < $this->tries) {
                $this->release(60 * $this->attempts());
            }
        }
    }
}
```

---

## 5. Anti-Spam & Anti-Ban Strategy

### Rate Limiting

#### 1. Queue-Level Throttling

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-notifications' => [
            'connection' => 'redis',
            'queue' => ['notifications'],
            'balance' => 'simple',
            'processes' => 2, // Limited workers
            'tries' => 5,
            'timeout' => 300,
            'minProcesses' => 1,
            'maxProcesses' => 3,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
        ],
    ],
],
```

#### 2. Job Middleware (Rate Limiter)

```php
// app/Jobs/Middleware/RateLimited.php
namespace App\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;

class RateLimited
{
    public function __construct(
        private string $key,
        private int $maxAttempts,
        private int $decayMinutes,
    ) {}
    
    public function handle($job, $next)
    {
        Redis::throttle($this->key)
            ->allow($this->maxAttempts)
            ->every($this->decayMinutes * 60)
            ->then(
                fn () => $next($job),
                fn () => $job->release($this->decayMinutes * 60)
            );
    }
}
```

**Usage**:
```php
public function middleware(): array
{
    $settings = app(NotificationSettingRepository::class)->get();
    
    return [
        new RateLimited(
            'whatsapp',
            $settings->whatsapp_max_per_minute,
            1
        ),
    ];
}
```

### Random Delay Strategy

```php
class NotificationService
{
    public function sendWithDelay(
        string $channel,
        callable $sendCallback
    ): void {
        $settings = app(NotificationSettingRepository::class)->get();
        
        $delay = match($channel) {
            'whatsapp' => rand(
                $settings->whatsapp_delay_seconds,
                $settings->whatsapp_delay_seconds + 10
            ),
            'email' => rand(
                $settings->email_delay_seconds,
                $settings->email_delay_seconds + 5
            ),
            default => 5,
        };
        
        sleep($delay);
        
        $sendCallback();
    }
}
```

### Message Variation

```php
// Add slight variations to avoid spam pattern detection
class MessageVariationService
{
    private array $greetings = [
        'Hi {name}',
        'Hello {name}',
        'Dear {name}',
    ];
    
    private array $closings = [
        'Thank you!',
        'Thanks!',
        'Best regards',
    ];
    
    public function addVariation(string $message, array $variables): string
    {
        $greeting = $this->greetings[array_rand($this->greetings)];
        $closing = $this->closings[array_rand($this->closings)];
        
        $greeting = str_replace('{name}', $variables['name'] ?? '', $greeting);
        
        return "{$greeting}\n\n{$message}\n\n{$closing}";
    }
}
```

### Best Practices

1. **WhatsApp**:
   - Max 10-15 messages per minute
   - 5-15 seconds random delay
   - Avoid identical messages
   - Use single device (1 device ID)
   - Monitor device status daily

2. **Email**:
   - Proper SPF/DKIM/DMARC setup
   - Use professional template
   - Include unsubscribe link (jika newsletter)
   - Clear from name & address
   - Avoid spam trigger words

---

## 6. Notification Templates

### Template Variables

| Variable | Description | Example |
|----------|-------------|---------|
| `{name}` | Participant name | John Doe |
| `{registration_number}` | Unique registration ID | FR5K-0001 |
| `{bib_number}` | BIB number | 1001 |
| `{event_name}` | Event name | Jakarta Fun Run 2026 |
| `{event_date}` | Event date | 15 May 2026 |
| `{category_name}` | Race category | 5K Fun Run |
| `{amount}` | Payment amount | Rp 150,000 |
| `{payment_deadline}` | Deadline | 10 Feb 2026, 14:00 |

### Default Templates

#### 1. Registration Confirmation

**WhatsApp**:
```
Halo {name}! 

Terima kasih telah mendaftar {event_name} 🎉

📝 Nomor Registrasi: {registration_number}
🏃 Kategori: {category_name}
💰 Total Biaya: {amount}

LANGKAH SELANJUTNYA:
1. Selesaikan pembayaran sebelum {payment_deadline}
2. Upload bukti transfer
3. Tunggu verifikasi admin
4. Terima e-ticket Anda

Link Pembayaran: {payment_link}

⚠️ PENTING: Registrasi akan otomatis dibatalkan jika pembayaran tidak diupload dalam 24 jam.

Terima kasih!
```

**Email**: Similar dengan formatting HTML yang lebih baik

#### 2. Payment Upload Confirmation

**WhatsApp**:
```
Halo {name}!

✅ Bukti pembayaran Anda sudah kami terima.

📝 Nomor Registrasi: {registration_number}
💰 Jumlah: {amount}

Pembayaran Anda sedang dalam proses verifikasi oleh tim kami (maks 1x24 jam).

Anda akan menerima e-ticket setelah pembayaran diverifikasi.

Terima kasih atas kesabaran Anda!
```

#### 3. Payment Verified + E-Ticket

**WhatsApp**:
```
Selamat {name}! 🎉

Pembayaran Anda telah DIVERIFIKASI!

📝 Nomor Registrasi: {registration_number}
🏃 Kategori: {category_name}
🎫 Nomor BIB: {bib_number}

E-Ticket Anda sudah dikirim via email.

PENGAMBILAN RACE PACK:
📅 Tanggal: [Lihat e-ticket]
📍 Lokasi: [Lihat e-ticket]
⏰ Jam: [Lihat e-ticket]

PENTING: Bawa e-ticket (print/digital) saat pengambilan race pack.

Sampai jumpa di event! 🏃‍♂️💨
```

**Email**: With PDF attachment

---

## 7. Delivery Monitoring

### Metrics Dashboard

**Filament Widget**:
```php
class NotificationStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.notification-stats';
    
    public function getStats(): array
    {
        $whatsappSent = NotificationLog::whatsapp()->sent()->count();
        $whatsappFailed = NotificationLog::whatsapp()->failed()->count();
        $whatsappRate = $whatsappSent + $whatsappFailed > 0
            ? round($whatsappSent / ($whatsappSent + $whatsappFailed) * 100, 2)
            : 0;
        
        $emailSent = NotificationLog::email()->sent()->count();
        $emailFailed = NotificationLog::email()->failed()->count();
        $emailRate = $emailSent + $emailFailed > 0
            ? round($emailSent / ($emailSent + $emailFailed) * 100, 2)
            : 0;
        
        return [
            'whatsapp' => [
                'sent' => $whatsappSent,
                'failed' => $whatsappFailed,
                'success_rate' => $whatsappRate . '%',
            ],
            'email' => [
                'sent' => $emailSent,
                'failed' => $emailFailed,
                'success_rate' => $emailRate . '%',
            ],
        ];
    }
}
```

### Failed Notifications Page

```php
class FailedNotificationsPage extends Page
{
    protected static string $view = 'filament.pages.failed-notifications';
    
    public function getTableQuery()
    {
        return NotificationLog::query()
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc');
    }
    
    public function retryNotification($logId)
    {
        $log = NotificationLog::findOrFail($logId);
        
        if ($log->channel === 'whatsapp') {
            SendWhatsAppNotificationJob::dispatch(
                $log->recipient,
                $log->message,
                $log->registration_id,
                $log->participant_id
            );
        } else {
            // Retry email
        }
        
        Notification::make()
            ->success()
            ->title('Notification re-queued')
            ->send();
    }
}
```

---

## 8. Error Handling & Retry Logic

### Retry Strategy

| Attempt | Delay | Total Wait |
|---------|-------|------------|
| 1st | Immediate | 0 min |
| 2nd | 1 minute | 1 min |
| 3rd | 2 minutes | 3 min |
| 4th | 4 minutes | 7 min |
| 5th | 8 minutes | 15 min |

### Common Errors & Handling

#### WhatsApp Errors

| Error | Cause | Action |
|-------|-------|--------|
| Device offline | WhatsApp not connected | Notify admin, retry later |
| Invalid number | Wrong phone format | Log error, don't retry |
| Rate limit exceeded | Too many messages | Increase delay, retry |
| Invalid API key | Wrong configuration | Alert admin immediately |

#### Email Errors

| Error | Cause | Action |
|-------|-------|--------|
| Connection timeout | SMTP server down | Retry with backoff |
| Invalid recipient | Wrong email | Log error, don't retry |
| Mailbox full | Recipient mailbox full | Retry once after 1 hour |
| Spam blocked | Content detected as spam | Review template, notify admin |

### Alert System

```php
// app/Observers/NotificationLogObserver.php
class NotificationLogObserver
{
    public function updated(NotificationLog $log)
    {
        // Alert if failure rate > 20%
        if ($log->status === 'failed') {
            $recentLogs = NotificationLog::query()
                ->where('channel', $log->channel)
                ->where('created_at', '>=', now()->subHour())
                ->get();
            
            $failureRate = $recentLogs->where('status', 'failed')->count() / $recentLogs->count();
            
            if ($failureRate > 0.2) {
                // Send alert to admin
                Mail::to(config('mail.admin_email'))
                    ->send(new HighFailureRateAlert($log->channel, $failureRate));
            }
        }
    }
}
```

---

## 9. Testing Strategy

### Unit Tests

```php
// tests/Unit/NotificationTest.php
test('formats phone number correctly', function () {
    $job = new SendWhatsAppNotificationJob('081234567890', 'Test');
    
    $formatted = $job->formatPhoneNumber('081234567890');
    
    expect($formatted)->toBe('6281234567890');
});

test('replaces template variables', function () {
    $template = 'Hello {name}, your registration is {registration_number}';
    $variables = ['name' => 'John', 'registration_number' => 'FR5K-0001'];
    
    $result = str_replace(
        array_keys($variables),
        array_values($variables),
        $template
    );
    
    expect($result)->toBe('Hello John, your registration is FR5K-0001');
});
```

### Integration Tests

```php
// tests/Feature/NotificationTest.php
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

test('dispatches notification jobs on registration', function () {
    Queue::fake();
    
    $registration = Registration::factory()->create();
    
    event(new RegistrationCreated($registration));
    
    Queue::assertPushed(SendWhatsAppNotificationJob::class);
    Queue::assertPushed(SendEmailNotificationJob::class);
});

test('sends email with correct content', function () {
    Mail::fake();
    
    $registration = Registration::factory()->create();
    
    $job = new SendEmailNotificationJob(
        'test@example.com',
        new RegistrationConfirmedMail($registration)
    );
    
    $job->handle();
    
    Mail::assertSent(RegistrationConfirmedMail::class);
});
```

### Manual Testing Checklist

- [ ] WhatsApp message received within 10-15 seconds
- [ ] Email received within 5-10 seconds
- [ ] Template variables replaced correctly
- [ ] Attachments (E-Ticket) included
- [ ] Links clickable and work correctly
- [ ] Messages not marked as spam
- [ ] Retry works for failed deliveries
- [ ] Failed notifications logged correctly
- [ ] Admin dashboard shows correct stats

---

## Conclusion

Sistem notifikasi ini dirancang untuk:
- ✅ **Reliable**: Queue + retry mechanism
- ✅ **Scalable**: Handle ribuan notifikasi per hari
- ✅ **Monitored**: Complete logging & dashboard
- ✅ **Compliant**: Anti-spam & anti-ban strategy
- ✅ **Flexible**: Template-based, easy to customize
- ✅ **Maintainable**: Clean code, well-tested

**Production Checklist**:
1. Setup SPF/DKIM/DMARC untuk email domain
2. Configure Fonnte device & API key
3. Test semua templates dengan real data
4. Monitor delivery rates daily
5. Setup alerts untuk high failure rate
6. Keep notification logs untuk audit (retention: 90 days)
