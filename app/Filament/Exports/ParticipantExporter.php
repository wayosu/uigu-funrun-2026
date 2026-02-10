<?php

namespace App\Filament\Exports;

use App\Models\Participant;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;

class ParticipantExporter extends Exporter
{
    protected static ?string $model = Participant::class;

    public static function getColumns(): array
    {
        return [
            // Registration Information
            ExportColumn::make('registration.registration_number')
                ->label('Registration Number'),
            ExportColumn::make('registration.raceCategory.event.name')
                ->label('Event Name'),
            ExportColumn::make('registration.raceCategory.name')
                ->label('Race Category'),
            ExportColumn::make('registration.raceCategory.distance')
                ->label('Distance'),
            ExportColumn::make('registration.registration_type')
                ->label('Registration Type')
                ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),

            // Participant Information
            ExportColumn::make('bib_number')
                ->label('BIB Number'),
            ExportColumn::make('is_pic')
                ->label('Is PIC')
                ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
            ExportColumn::make('name')
                ->label('Full Name'),
            ExportColumn::make('bib_name')
                ->label('BIB Name'),
            ExportColumn::make('email')
                ->label('Email Address'),
            ExportColumn::make('phone')
                ->label('Phone Number'),
            ExportColumn::make('gender')
                ->label('Gender')
                ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),
            ExportColumn::make('birth_date')
                ->label('Birth Date')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y') ?? '-'),
            ExportColumn::make('age')
                ->label('Age')
                ->state(function (Participant $record): string {
                    return $record->birth_date ? $record->getAge().' years' : '-';
                }),
            ExportColumn::make('identity_number')
                ->label('Identity Number'),
            ExportColumn::make('blood_type')
                ->label('Blood Type'),
            ExportColumn::make('jersey_size')
                ->label('Jersey Size')
                ->formatStateUsing(function ($state) {
                    if (! $state) {
                        return '-';
                    }
                    $jerseySize = \App\Models\JerseySize::query()->where('code', $state)->first();

                    return $jerseySize ? $jerseySize->name : strtoupper($state);
                }),
            ExportColumn::make('emergency_contact')
                ->label('Emergency Contact'),

            // Payment & Registration Status
            ExportColumn::make('registration.status')
                ->label('Payment Status')
                ->formatStateUsing(fn ($state) => $state?->label() ?? '-'),
            ExportColumn::make('registration.total_amount')
                ->label('Total Amount')
                ->formatStateUsing(fn ($state) => $state ? 'Rp '.number_format((float) $state, 0, ',', '.') : '-'),
            ExportColumn::make('registration.expired_at')
                ->label('Payment Expiry')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i') ?? '-'),

            // Check-in Status
            ExportColumn::make('checkin_status')
                ->label('Check-in Status')
                ->state(fn (Participant $record): string => $record->isCheckedIn() ? 'Checked In' : 'Not Checked In'),
            ExportColumn::make('latest_checkin')
                ->label('Check-in Time')
                ->state(function (Participant $record): string {
                    $latestCheckin = $record->checkins()->latest()->first();

                    return $latestCheckin ? $latestCheckin->checked_in_at->format('d/m/Y H:i:s') : '-';
                }),

            // Timestamps
            ExportColumn::make('created_at')
                ->label('Registered At')
                ->formatStateUsing(fn ($state) => $state?->format('d/m/Y H:i:s') ?? '-'),
        ];
    }

    public function getFileName(Export $export): string
    {
        $timestamp = now()->format('Y-m-d_His');

        return "participants_export_{$timestamp}";
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Participant export completed successfully!';
        $body .= "\n\n".'✅ Successfully exported: '.Number::format($export->successful_rows).' '.str('participant')->plural($export->successful_rows);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= "\n".'❌ Failed to export: '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount);
        }

        return $body;
    }

    public function getOptions(): array
    {
        return [
            'chunkSize' => 500,
        ];
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Xlsx,
        ];
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return (new Style)
            ->setFontBold()
            ->setFontSize(12)
            ->setBackgroundColor(Color::rgb(0, 154, 166))
            ->setFontColor(Color::rgb(255, 255, 255));
    }
}
