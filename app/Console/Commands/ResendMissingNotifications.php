<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Registration;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ResendMissingNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:resend-missing
                            {--dry-run : Show what would be sent without actually sending}
                            {--type=payment_verified : Type of notification to resend (payment_verified, payment_uploaded, registration_created)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend notifications to verified payments that are missing WhatsApp/Email notifications (e.g., when Redis was down)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Searching for registrations missing '{$type}' notifications...");
        $this->newLine();

        // Find registrations that should have this notification type but don't
        $registrations = $this->findMissingNotifications($type);

        if ($registrations->isEmpty()) {
            $this->info('✅ No missing notifications found!');

            return Command::SUCCESS;
        }

        $this->warn("Found {$registrations->count()} registration(s) without sent '{$type}' notifications:");
        $this->newLine();

        $table = [];
        foreach ($registrations as $registration) {
            $pic = $registration->participants()->pic()->first();

            if (! $pic) {
                $this->warn("⚠️  Registration {$registration->registration_number} has no PIC - skipping");

                continue;
            }

            $table[] = [
                $registration->registration_number,
                $pic->name,
                $pic->phone,
                $pic->email,
                $registration->participants_count.' participant(s)',
                $registration->updated_at->format('Y-m-d H:i'),
            ];
        }

        $this->table(
            ['Reg Number', 'PIC Name', 'Phone', 'Email', 'Participants', 'Last Updated'],
            $table
        );

        if ($dryRun) {
            $this->warn('🔸 DRY RUN - No notifications will be sent');
            $this->info('Run without --dry-run to actually send notifications');

            return Command::SUCCESS;
        }

        if (! $this->confirm('Do you want to resend notifications to these registrations?', true)) {
            $this->info('Cancelled.');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info('📤 Sending notifications...');
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        $service = app(NotificationService::class);

        foreach ($registrations as $registration) {
            $pic = $registration->participants()->pic()->first();

            if (! $pic) {
                continue;
            }

            try {
                $variables = $service->buildTemplateVariables($registration, $pic);

                // Get templates
                $whatsappTemplate = $service->getTemplate('whatsapp', $type);
                $emailTemplate = $service->getTemplate('email', $type);

                $whatsappMessage = $service->processTemplate($whatsappTemplate, $variables);
                $emailMessage = $service->processTemplate($emailTemplate, $variables);
                $emailSubject = $this->getEmailSubject($type, $registration->registration_number);

                // Dispatch jobs
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

                $this->info("✅ {$registration->registration_number} - Queued for {$pic->name}");
                $successCount++;
            } catch (\Exception $e) {
                $this->error("❌ {$registration->registration_number} - Failed: {$e->getMessage()}");
                $errorCount++;
            }
        }

        $this->newLine();
        $this->info('📊 Results:');
        $this->info("   ✅ Queued: {$successCount}");

        if ($errorCount > 0) {
            $this->warn("   ❌ Failed: {$errorCount}");
        }

        $this->newLine();
        $this->info('✅ Done! Notifications have been queued. Check queue worker status.');
        $this->info('💡 Tip: Run "php artisan queue:work --queue=notifications" if not running');

        return Command::SUCCESS;
    }

    protected function findMissingNotifications(string $type): \Illuminate\Database\Eloquent\Collection
    {
        $query = Registration::query();

        // Filter based on type
        if ($type === 'payment_verified') {
            $query->where('status', PaymentStatus::PaymentVerified);
        } elseif ($type === 'payment_uploaded') {
            $query->where('status', PaymentStatus::PaymentUploaded);
        } elseif ($type === 'registration_created') {
            // All registrations
            $query->whereNotNull('id');
        }

        // Find registrations without SENT notifications of this type
        $query->whereDoesntHave('notificationLogs', function ($subQuery) use ($type) {
            $subQuery->where('type', $type)
                ->where('status', 'sent');
        });

        return $query->with(['participants' => function ($q) {
            $q->where('is_pic', true);
        }])->get();
    }

    protected function getEmailSubject(string $type, string $registrationNumber): string
    {
        return match ($type) {
            'registration_created' => "Registration Successful - {$registrationNumber}",
            'payment_uploaded' => "Payment Proof Received - {$registrationNumber}",
            'payment_verified' => "Payment Verified - {$registrationNumber}",
            'payment_rejected' => "Payment Rejected - {$registrationNumber}",
            default => "Notification from UIGU Fun Run - {$registrationNumber}",
        };
    }
}
