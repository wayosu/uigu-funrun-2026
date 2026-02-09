<?php

namespace App\Filament\Resources\Participants;

use App\Enums\Gender;
use App\Enums\PaymentStatus;
use App\Filament\Exports\ParticipantExporter;
use App\Filament\Resources\Participants\Pages\ManageParticipants;
use App\Models\JerseySize;
use App\Models\Participant;
use BackedEnum;
use Filament\Actions\ExportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
        return false;
    }

    public static function canDelete(mixed $record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('email'),
                TextInput::make('phone'),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                    ]),
                DatePicker::make('birth_date'),
                TextInput::make('jersey_size'),
                TextInput::make('identity_number'),
                TextInput::make('blood_type'),
                TextInput::make('emergency_contact'),
                TextInput::make('bib_number'),
                Toggle::make('is_pic'),
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
                TextColumn::make('date_of_birth')
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
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(ParticipantExporter::class),
            ]);
        // Note: ExportBulkAction requires Filament Tables ExportBulkAction which is likely Filament\Tables\Actions\ExportBulkAction
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageParticipants::route('/'),
        ];
    }
}
