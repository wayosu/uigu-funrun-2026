<?php

namespace App\Filament\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SystemHealthWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            $this->getQueueStatus(),
            $this->getRedisStatus(),
            $this->getDatabaseStatus(),
            $this->getFailedJobsCount(),
        ];
    }

    protected function getQueueStatus(): Stat
    {
        try {
            $queueSize = Redis::connection('default')->llen('queues:notifications');
            $status = $queueSize < 100 ? 'Healthy' : 'High Load';
            $color = $queueSize < 100 ? 'success' : 'warning';

            return Stat::make('Queue Status', $status)
                ->description("{$queueSize} jobs in notifications queue")
                ->descriptionIcon(Heroicon::OutlinedQueueList)
                ->color($color);
        } catch (\Exception $e) {
            return Stat::make('Queue Status', 'Error')
                ->description('Unable to connect to Redis')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger');
        }
    }

    protected function getRedisStatus(): Stat
    {
        try {
            Redis::connection('default')->ping();

            return Stat::make('Redis', 'Connected')
                ->description('Cache and queue operational')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success');
        } catch (\Exception $e) {
            return Stat::make('Redis', 'Disconnected')
                ->description('Connection failed')
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->color('danger');
        }
    }

    protected function getDatabaseStatus(): Stat
    {
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();

            return Stat::make('Database', 'Connected')
                ->description("Connected to {$dbName}")
                ->descriptionIcon(Heroicon::OutlinedCircleStack)
                ->color('success');
        } catch (\Exception $e) {
            return Stat::make('Database', 'Disconnected')
                ->description('Connection failed')
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->color('danger');
        }
    }

    protected function getFailedJobsCount(): Stat
    {
        try {
            $failedCount = DB::table('failed_jobs')->count();
            $description = $failedCount > 0 ? 'Requires attention' : 'No failed jobs';
            $color = $failedCount > 0 ? 'warning' : 'success';
            $icon = $failedCount > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle;

            return Stat::make('Failed Jobs', $failedCount)
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color);
        } catch (\Exception $e) {
            return Stat::make('Failed Jobs', 'Error')
                ->description('Unable to fetch data')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color('danger');
        }
    }
}
