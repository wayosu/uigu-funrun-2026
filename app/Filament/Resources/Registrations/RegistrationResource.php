<?php

namespace App\Filament\Resources\Registrations;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationType;
use App\Filament\Resources\Registrations\Pages\ManageRegistrations;
use App\Filament\Resources\Registrations\RegistrationResource\RelationManagers\ParticipantsRelationManager;
use App\Filament\Resources\Registrations\RegistrationResource\RelationManagers\PaymentsRelationManager;
use App\Models\Registration;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RegistrationResource extends Resource
{
    protected static ?string $model = Registration::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Operations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Form is mostly read-only via RelationManagers, but we can add basic info here
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon(Heroicon::OutlinedDocumentText),
                TextColumn::make('registration_type')
                    ->badge()
                    ->formatStateUsing(fn (RegistrationType $state): string => $state->label())
                    ->color(fn (RegistrationType $state): string => match ($state) {
                        RegistrationType::Individual => 'info',
                        RegistrationType::Collective5 => 'warning',
                        RegistrationType::Collective10 => 'success',
                    }),
                TextColumn::make('raceCategory.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('participants_count')
                    ->counts('participants')
                    ->label('Participants')
                    ->alignCenter()
                    ->icon(Heroicon::OutlinedUsers),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->icon(fn (PaymentStatus $state): string => $state->icon()),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expired_at')
                    ->label('Deadline')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color(fn (Registration $record): string => $record->isExpired() ? 'danger' : 'gray'
                    ),
                TextColumn::make('created_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(PaymentStatus::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                    )
                    ->multiple(),
                SelectFilter::make('registration_type')
                    ->options(collect(RegistrationType::cases())
                        ->mapWithKeys(fn ($type) => [$type->value => $type->label()])
                    )
                    ->label('Type'),
                SelectFilter::make('race_category_id')
                    ->relationship('raceCategory', 'name')
                    ->label('Category')
                    ->searchable()
                    ->preload(),
                Filter::make('expired')
                    ->label('Expired Only')
                    ->query(fn (Builder $query): Builder => $query->expired()),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->bulkActions([
                BulkAction::make('cancel')
                    ->label('Cancel Selected')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $records->each(function (Registration $record) {
                            if (! in_array($record->status, [PaymentStatus::Cancelled, PaymentStatus::PaymentVerified])) {
                                $record->update(['status' => PaymentStatus::Cancelled]);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Selected registrations have been cancelled'),

                BulkAction::make('expire')
                    ->label('Mark as Expired')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records): void {
                        $records->each(function (Registration $record) {
                            if ($record->status === PaymentStatus::PendingPayment) {
                                $record->update(['status' => PaymentStatus::Expired]);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion()
                    ->successNotificationTitle('Selected registrations have been marked as expired'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ParticipantsRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRegistrations::route('/'),
        ];
    }
}
