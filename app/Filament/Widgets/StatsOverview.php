<?php

namespace App\Filament\Widgets;

use App\Models\Checkin;
use App\Models\Registration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalIncome = Registration::where('status', 'paid')->sum('total_amount');

        $paidCount = Registration::where('status', 'paid')->count();
        $pendingCount = Registration::whereIn('status', ['pending_payment', 'waiting_verification'])->count();

        $totalPaidParticipants = \App\Models\Participant::whereHas('registration', function ($query) {
            $query->where('status', 'paid');
        })->count();

        $checkedInParticipants = Checkin::count();

        $checkinPercentage = $totalPaidParticipants > 0 ? ($checkedInParticipants / $totalPaidParticipants) * 100 : 0;

        return [
            Stat::make('Total Income', 'IDR '.number_format($totalIncome, 0, ',', '.'))
                ->description('From paid registrations')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('Registration Status', $paidCount.' Paid / '.$pendingCount.' Pending')
                ->description('Registrations')
                ->descriptionIcon('heroicon-m-user-group'),
            Stat::make('Check-in Progress', $checkedInParticipants.' / '.$totalPaidParticipants)
                ->description(number_format($checkinPercentage, 1).'% checked in')
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('primary'),
        ];
    }
}
