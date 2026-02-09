<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\RegistrationService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalRegistrations = Registration::count();
        $verifiedPayments = Registration::where('status', PaymentStatus::PaymentVerified)->count();
        $pendingPayments = Registration::where('status', PaymentStatus::PendingPayment)->count();
        $uploadedPayments = Registration::where('status', PaymentStatus::PaymentUploaded)->count();
        $totalParticipants = Participant::count();

        // Calculate available slots across all active categories
        $service = app(RegistrationService::class);
        $availableSlots = \App\Models\RaceCategory::where('is_active', true)
            ->get()
            ->sum(fn ($category) => $service->getAvailableSlots($category));

        return [
            Stat::make('Total Registrations', $totalRegistrations)
                ->description('All time registrations')
                ->icon(Heroicon::OutlinedClipboardDocumentList)
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('Verified Payments', $verifiedPayments)
                ->description("{$pendingPayments} pending, {$uploadedPayments} uploaded")
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->descriptionIcon(Heroicon::OutlinedClock),

            Stat::make('Total Participants', $totalParticipants)
                ->description('Registered participants')
                ->icon(Heroicon::OutlinedUsers)
                ->color('warning'),

            Stat::make('Available Slots', $availableSlots)
                ->description('Across active categories')
                ->icon(Heroicon::OutlinedTicket)
                ->color($availableSlots <= 50 ? 'danger' : 'info'),
        ];
    }
}
