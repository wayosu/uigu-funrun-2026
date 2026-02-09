<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RegistrationChartWidget extends ChartWidget
{
    protected ?string $heading = 'Registrations Over Time';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getRegistrationsPerDay();

        return [
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => $data['counts'],
                    'fill' => true,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getRegistrationsPerDay(): array
    {
        $days = 14; // Last 14 days
        $labels = [];
        $counts = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $counts[] = Registration::whereDate('created_at', $date)->count();
        }

        return [
            'labels' => $labels,
            'counts' => $counts,
        ];
    }
}
