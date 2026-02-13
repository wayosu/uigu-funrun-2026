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

class SendEmailNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public string $queue = 'emails';

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
        public string $subject,
        public string $message,
        public ?Registration $registration = null,
        public ?Participant $participant = null,
        public string $type = 'general',
        public bool $attachTicketPdf = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        $service->sendEmail(
            $this->recipient,
            $this->subject,
            $this->message,
            $this->registration,
            $this->participant,
            $this->type,
            $this->attachTicketPdf
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
