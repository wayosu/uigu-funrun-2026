<?php

namespace App\Filament\Widgets;

use App\Models\NotificationLog;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecentNotificationsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $today = NotificationLog::whereDate('created_at', today());
        $sentToday = $today->clone()->where('status', 'sent')->count();
        $failedToday = $today->clone()->where('status', 'failed')->count();
        $totalToday = $today->count();
        $successRate = $totalToday > 0 ? round(($sentToday / $totalToday) * 100, 1) : 0;

        return [
            Stat::make('Sent Today', $sentToday)
                ->description("Out of {$totalToday} total notifications")
                ->descriptionIcon(Heroicon::OutlinedPaperAirplane)
                ->color('success')
                ->chart($this->getLastSevenDaysChart('sent')),

            Stat::make('Failed Today', $failedToday)
                ->description($failedToday > 0 ? 'Requires attention' : 'All successful')
                ->descriptionIcon($failedToday > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($failedToday > 0 ? 'danger' : 'success')
                ->chart($this->getLastSevenDaysChart('failed')),

            Stat::make('Success Rate', "{$successRate}%")
                ->description('Last 24 hours')
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color($successRate >= 95 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger')),

            Stat::make('Total Notifications', NotificationLog::count())
                ->description('All time')
                ->descriptionIcon(Heroicon::OutlinedBell)
                ->color('info'),
        ];
    }

    protected function getLastSevenDaysChart(string $status): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) use ($status) {
            return NotificationLog::whereDate('created_at', today()->subDays($daysAgo))
                ->where('status', $status)
                ->count();
        });

        return $days->toArray();
    }
}
