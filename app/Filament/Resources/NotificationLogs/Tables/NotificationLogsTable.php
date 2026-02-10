<?php

namespace App\Filament\Resources\NotificationLogs\Tables;

use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendWhatsAppNotificationJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class NotificationLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration.registration_number')
                    ->label('Registration')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->url(fn ($record) => $record->registration_id
                        ? route('filament.admin.resources.registrations.index', [
                            'tableSearch' => $record->registration->registration_number,
                        ])
                        : null
                    )
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('channel')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'email' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'whatsapp' => Heroicon::OutlinedChatBubbleLeftRight->getIconForSize(IconSize::Medium),
                        'email' => Heroicon::OutlinedEnvelope->getIconForSize(IconSize::Medium),
                        default => Heroicon::OutlinedBell->getIconForSize(IconSize::Medium),
                    })
                    ->sortable(),

                TextColumn::make('recipient')
                    ->searchable()
                    ->copyable()
                    ->limit(30),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'sent' => Heroicon::OutlinedCheckCircle->getIconForSize(IconSize::Medium),
                        'failed' => Heroicon::OutlinedXCircle->getIconForSize(IconSize::Medium),
                        'pending' => Heroicon::OutlinedClock->getIconForSize(IconSize::Medium),
                        default => Heroicon::OutlinedQuestionMarkCircle->getIconForSize(IconSize::Medium),
                    })
                    ->sortable(),

                TextColumn::make('message_body')
                    ->label('Message')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('response_data')
                    ->label('Error')
                    ->state(fn ($record) => data_get($record->response_data, 'reason'))
                    ->limit(40)
                    ->placeholder('-')
                    ->color('danger')
                    ->toggleable(),

                TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Not sent')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('channel')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'email' => 'Email',
                    ]),

                SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ])
                    ->multiple(),

                Filter::make('sent')
                    ->label('Successfully Sent')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'sent')),

                Filter::make('failed')
                    ->label('Failed')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'failed')),

                Filter::make('today')
                    ->label('Today')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),

                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ])),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn ($record) => "Notification Details - {$record->channel}")
                    ->schema([
                        Section::make('Summary')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('channel')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'whatsapp' => 'success',
                                                'email' => 'info',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('type')
                                            ->label('Type')
                                            ->placeholder('-'),
                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'sent' => 'success',
                                                'failed' => 'danger',
                                                'pending' => 'warning',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('recipient')
                                            ->copyable(),
                                        TextEntry::make('registration.registration_number')
                                            ->label('Registration')
                                            ->placeholder('-'),
                                        TextEntry::make('sent_at')
                                            ->label('Sent At')
                                            ->dateTime('d M Y H:i:s')
                                            ->placeholder('Not sent yet'),
                                        TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime('d M Y H:i:s'),
                                    ]),
                            ])
                            ->columns(1),
                        Section::make('Message')
                            ->schema([
                                TextEntry::make('message_body')
                                    ->label('Message')
                                    ->columnSpanFull()
                                    ->placeholder('-')
                                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                            ]),
                        Section::make('Response')
                            ->schema([
                                TextEntry::make('response_data')
                                    ->label('Response')
                                    ->columnSpanFull()
                                    ->placeholder('-')
                                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : '-'),
                            ]),
                    ])
                    ->modalWidth('2xl'),

                Action::make('retry')
                    ->label('Retry')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === 'failed')
                    ->requiresConfirmation()
                    ->modalHeading('Retry Failed Notification')
                    ->modalDescription('Are you sure you want to retry sending this notification?')
                    ->action(function ($record) {
                        if ($record->channel === 'whatsapp') {
                            SendWhatsAppNotificationJob::dispatch(
                                $record->recipient,
                                $record->message_body,
                                $record->registration,
                                null,
                                $record->type ?? 'general'
                            )->onQueue('notifications');
                        } elseif ($record->channel === 'email') {
                            SendEmailNotificationJob::dispatch(
                                $record->recipient,
                                'Notification from UIGU Fun Run',
                                $record->message_body,
                                $record->registration,
                                null,
                                $record->type ?? 'general'
                            )->onQueue('notifications');
                        }

                        Notification::make()
                            ->title('Notification Queued')
                            ->body('The notification has been queued for retry.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Action::make('export')
                    ->label('Export to CSV')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();

                        $filename = 'notification-logs-'.now()->format('Y-m-d-His').'.csv';
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($records) {
                            $file = fopen('php://output', 'w');
                            fputcsv($file, ['ID', 'Registration', 'PIC Name', 'Channel', 'Type', 'Recipient', 'Status', 'Message', 'Error', 'Sent At', 'Created At']);

                            foreach ($records as $record) {
                                fputcsv($file, [
                                    $record->id,
                                    $record->registration?->registration_number ?? '-',
                                    $record->registration?->pic_name ?? '-',
                                    $record->channel,
                                    $record->type ?? '-',
                                    $record->recipient,
                                    $record->status,
                                    $record->message_body,
                                    data_get($record->response_data, 'reason') ?? '-',
                                    $record->sent_at?->format('Y-m-d H:i:s') ?? '-',
                                    $record->created_at->format('Y-m-d H:i:s'),
                                ]);
                            }

                            fclose($file);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),

                Action::make('clear_old')
                    ->label('Clear Old Logs')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Clear Old Notification Logs')
                    ->modalDescription('This will permanently delete notification logs older than 90 days. Are you sure?')
                    ->action(function () {
                        $deleted = \App\Models\NotificationLog::where('created_at', '<', now()->subDays(90))->delete();

                        Notification::make()
                            ->title('Old Logs Cleared')
                            ->body("{$deleted} notification logs older than 90 days have been deleted.")
                            ->success()
                            ->send();
                    }),

                BulkActionGroup::make([
                    BulkAction::make('retry_failed')
                        ->label('Retry Failed')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Retry Failed Notifications')
                        ->modalDescription('Are you sure you want to retry all selected failed notifications?')
                        ->action(function (Collection $records) {
                            $retried = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'failed') {
                                    continue;
                                }

                                if ($record->channel === 'whatsapp') {
                                    SendWhatsAppNotificationJob::dispatch(
                                        $record->recipient,
                                        $record->message_body,
                                        $record->registration,
                                        null,
                                        $record->type ?? 'general'
                                    )->onQueue('notifications');
                                    $retried++;
                                } elseif ($record->channel === 'email') {
                                    SendEmailNotificationJob::dispatch(
                                        $record->recipient,
                                        'Notification from UIGU Fun Run',
                                        $record->message_body,
                                        $record->registration,
                                        null,
                                        $record->type ?? 'general'
                                    )->onQueue('notifications');
                                    $retried++;
                                }
                            }

                            Notification::make()
                                ->title('Notifications Queued')
                                ->body("{$retried} failed notifications have been queued for retry.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ]);
    }
}
