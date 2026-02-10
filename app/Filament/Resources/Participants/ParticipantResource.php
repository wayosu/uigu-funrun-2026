<?php

namespace App\Filament\Resources\Participants;

use App\Enums\Gender;
use App\Enums\PaymentStatus;
use App\Exports\ParticipantsExport;
use App\Filament\Resources\Participants\Pages\ManageParticipants;
use App\Models\JerseySize;
use App\Models\Participant;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantResource extends Resource
{
    protected static ?string $model = Participant::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Operations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(mixed $record): bool
    {
        return true;
    }

    public static function canDelete(mixed $record): bool
    {
        /** @var Participant $record */
        return $record->registration->status === PaymentStatus::Cancelled;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Full Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('bib_name')
                    ->label('BIB Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Name to be printed on the BIB'),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('Phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Select::make('gender')
                    ->label('Gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ])
                    ->required(),
                DatePicker::make('birth_date')
                    ->label('Birth Date')
                    ->required()
                    ->maxDate(now()->subYears(5)),
                Select::make('jersey_size')
                    ->label('Jersey Size')
                    ->options(fn () => \App\Models\JerseySize::query()
                        ->active()
                        ->ordered()
                        ->pluck('name', 'code')
                    )
                    ->required()
                    ->searchable(),
                TextInput::make('identity_number')
                    ->label('Identity Number')
                    ->nullable()
                    ->maxLength(50),
                TextInput::make('emergency_name')
                    ->label('Emergency Contact Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('emergency_phone')
                    ->label('Emergency Contact Phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                TextInput::make('emergency_relation')
                    ->label('Emergency Contact Relation')
                    ->required()
                    ->maxLength(50),
                TextInput::make('bib_number')
                    ->label('BIB Number')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('BIB Number cannot be edited'),
                Toggle::make('is_pic')
                    ->label('Is PIC')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration.registration_number')
                    ->label('Registration #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('bib_number')
                    ->label('BIB')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->placeholder('Not assigned')
                    ->icon(fn ($state) => $state ? Heroicon::OutlinedCheckBadge : null),
                IconColumn::make('is_pic')
                    ->label('PIC')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('bib_name')
                    ->label('BIB Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('registration.raceCategory.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('gender')
                    ->badge()
                    ->formatStateUsing(fn (Gender $state): string => $state->label())
                    ->color(fn (Gender $state): string => match ($state) {
                        Gender::Male => 'info',
                        Gender::Female => 'pink',
                    }),
                TextColumn::make('birth_date')
                    ->label('Age')
                    ->date('d M Y')
                    ->description(fn (Participant $record): string => $record->getAge().' years'
                    )
                    ->toggleable(),
                TextColumn::make('jersey_size')
                    ->label('Jersey')
                    ->formatStateUsing(function (string $state): string {
                        $jerseySize = JerseySize::query()->where('code', $state)->first();

                        return $jerseySize ? $jerseySize->name : strtoupper($state);
                    })
                    ->badge()
                    ->color('gray'),
                TextColumn::make('registration.status')
                    ->label('Payment Status')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->toggleable(),
                IconColumn::make('checkin_status')
                    ->label('Checked In')
                    ->boolean()
                    ->getStateUsing(fn (Participant $record): bool => $record->isCheckedIn())
                    ->alignCenter()
                    ->toggleable(),
                TextColumn::make('last_exported_at')
                    ->label('Last Exported')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('Never')
                    ->description(fn (Participant $record): ?string => $record->export_count > 0 ? "Exported {$record->export_count}x" : null),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('gender')
                    ->options(collect(Gender::cases())
                        ->mapWithKeys(fn ($gender) => [$gender->value => $gender->label()])
                    ),
                SelectFilter::make('jersey_size')
                    ->label('Jersey Size')
                    ->options(fn () => \App\Models\JerseySize::query()
                        ->active()
                        ->ordered()
                        ->pluck('name', 'code')
                    )
                    ->searchable(),
                SelectFilter::make('race_category_id')
                    ->label('Category')
                    ->options(fn () => \App\Models\RaceCategory::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('registration', function (Builder $query) use ($data) {
                            $query->where('race_category_id', $data['value']);
                        });
                    }),
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(collect(PaymentStatus::cases())
                        ->mapWithKeys(fn ($status) => [$status->value => $status->label()])
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('registration', function (Builder $q) use ($data) {
                            $q->where('status', $data['value']);
                        });
                    }),
                Filter::make('has_bib')
                    ->label('Has BIB Number')
                    ->query(fn (Builder $query): Builder => $query->hasBibNumber()),
                Filter::make('no_bib')
                    ->label('No BIB Number')
                    ->query(fn (Builder $query): Builder => $query->withoutBibNumber()),
                Filter::make('checked_in')
                    ->label('Checked In')
                    ->query(fn (Builder $query): Builder => $query->checkedIn()),
                Filter::make('not_checked_in')
                    ->label('Not Checked In')
                    ->query(fn (Builder $query): Builder => $query->notCheckedIn()),
                Filter::make('is_pic')
                    ->label('PIC Only')
                    ->query(fn (Builder $query): Builder => $query->pic()),
                Filter::make('exported')
                    ->label('Already Exported')
                    ->query(fn (Builder $query): Builder => $query->exported()),
                Filter::make('not_exported')
                    ->label('Not Exported Yet')
                    ->query(fn (Builder $query): Builder => $query->notExported()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Participant $record): bool => $record->registration->status === PaymentStatus::Cancelled),
            ])
            ->bulkActions([
                BulkAction::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Radio::make('export_mode')
                            ->label('Export Mode')
                            ->options([
                                'all' => 'All Participants - Export all participants (including previously exported)',
                                'new_only' => 'New Only - Export only participants that have NEVER been exported',
                                'updated_since_last' => 'Updated Since Last - Export new or updated participants since last export',
                            ])
                            ->default('all')
                            ->required()
                            ->helperText('Choose export mode to prevent duplicate data sent to vendors'),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $filename = 'participants_'.($data['export_mode'] ?? 'all').'_'.now()->format('Y-m-d_His').'.xlsx';
                        $query = Participant::query()->whereIn('id', $records->pluck('id'));

                        $export = new ParticipantsExport($query, $data['export_mode'] ?? 'all');
                        $result = Excel::download($export, $filename);

                        // Mark participants as exported
                        $records->each(fn ($participant) => $participant->markAsExported());

                        return $result;
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->toolbarActions([
                //
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->form([
                        \Filament\Forms\Components\Radio::make('export_mode')
                            ->label('Export Mode')
                            ->options([
                                'all' => 'All Participants - Export all participants (including previously exported)',
                                'new_only' => 'New Only - Export only participants that have NEVER been exported',
                                'updated_since_last' => 'Updated Since Last - Export new or updated participants since last export',
                            ])
                            ->default('new_only')
                            ->required()
                            ->helperText('⚠️ RECOMMENDED: Use "New Only" for vendor data to prevent duplicates!'),
                    ])
                    ->action(function ($livewire, array $data) {
                        $filename = 'participants_'.($data['export_mode'] ?? 'all').'_'.now()->format('Y-m-d_His').'.xlsx';
                        $query = $livewire->getFilteredTableQuery();

                        // Apply export mode filter to query
                        if ($data['export_mode'] === 'new_only') {
                            $query->notExported();
                        } elseif ($data['export_mode'] === 'updated_since_last') {
                            $query->newOrUpdatedSinceLastExport();
                        }

                        $export = new ParticipantsExport($query, $data['export_mode'] ?? 'all');
                        $result = Excel::download($export, $filename);

                        // Mark participants as exported
                        $query->get()->each(fn ($participant) => $participant->markAsExported());

                        return $result;
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageParticipants::route('/'),
        ];
    }
}
