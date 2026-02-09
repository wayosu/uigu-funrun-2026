<?php

namespace App\Filament\Widgets;

use App\Models\NotificationLog;
use Filament\Widgets\ChartWidget;

class NotificationStatisticsWidget extends ChartWidget
{
    protected ?string $heading = 'Notification Delivery Trends';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = '7days';

    protected function getData(): array
    {
        $days = match ($this->filter) {
            '30days' => 30,
            '7days' => 7,
            default => 7,
        };

        $labels = [];
        $whatsappData = [];
        $emailData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('d M');

            $whatsappData[] = NotificationLog::whereDate('created_at', $date)
                ->where('channel', 'whatsapp')
                ->where('status', 'sent')
                ->count();

            $emailData[] = NotificationLog::whereDate('created_at', $date)
                ->where('channel', 'email')
                ->where('status', 'sent')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'WhatsApp',
                    'data' => $whatsappData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Email',
                    'data' => $emailData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            '7days' => 'Last 7 days',
            '30days' => 'Last 30 days',
        ];
    }
}
