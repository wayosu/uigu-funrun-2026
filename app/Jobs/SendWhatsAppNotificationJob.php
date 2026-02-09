<?php

namespace App\Jobs;

use App\Models\Participant;
use App\Models\Registration;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $recipient,
        public string $message,
        public ?Registration $registration = null,
        public ?Participant $participant = null,
        public string $type = 'general'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        $service->sendWhatsApp(
            $this->recipient,
            $this->message,
            $this->registration,
            $this->participant,
            $this->type
        );
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new ThrottlesExceptions(10, 5 * 60))->backoff(60),
        ];
    }
}
