<?php

namespace App\Filament\Resources\RaceCategories;

use App\Filament\Resources\RaceCategories\Pages\ManageRaceCategories;
use App\Models\RaceCategory;
use App\Services\RegistrationService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class RaceCategoryResource extends Resource
{
    protected static ?string $model = RaceCategory::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Event Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Race Categories';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->description('Define the race category details and requirements')
                    ->schema([
                        Select::make('event_id')
                            ->label('Event')
                            ->relationship('event', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),

                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., 5K Fun Run')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., 5k-fun-run')
                            ->helperText('Auto-generated from category name')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('distance')
                            ->label('Race Distance')
                            ->maxLength(50)
                            ->placeholder('e.g., 5 KM')
                            ->helperText('Optional: Display distance for this category'),

                        TextInput::make('registration_prefix')
                            ->label('Registration Prefix')
                            ->required()
                            ->maxLength(10)
                            ->placeholder('e.g., 5K')
                            ->helperText('Used in registration numbers (e.g., 5K-001)')
                            ->alphaDash(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Brief description about this race category...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Pricing & Registration Types')
                    ->description('Set pricing for different registration types')
                    ->schema([
                        TextInput::make('price_individual')
                            ->label('Individual Registration')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('150000')
                            ->minValue(0)
                            ->step(1000)
                            ->helperText('Price for single participant'),

                        TextInput::make('price_collective_5')
                            ->label('Collective 5 People')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('650000')
                            ->minValue(0)
                            ->step(1000)
                            ->helperText('Price for group of 5 participants'),

                        TextInput::make('price_collective_10')
                            ->label('Collective 10 People')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('1200000')
                            ->minValue(0)
                            ->step(1000)
                            ->helperText('Price for group of 10 participants'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Quota Management')
                    ->description('Define participant limits for this category')
                    ->schema([
                        TextInput::make('quota')
                            ->label('Total Quota')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('slots')
                            ->placeholder('500')
                            ->helperText('Maximum number of participants allowed'),

                        TextInput::make('bib_start_number')
                            ->label('BIB Start Number')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('1001')
                            ->helperText('Starting BIB number for this category'),

                        TextInput::make('bib_end_number')
                            ->label('BIB End Number')
                            ->required()
                            ->numeric()
                            ->gte('bib_start_number')
                            ->placeholder('2000')
                            ->helperText('Ending BIB number for this category'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Registration Period')
                    ->description('Set when participants can register for this category')
                    ->schema([
                        DateTimePicker::make('registration_open_at')
                            ->label('Opens At')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->helperText('When registration opens for this category'),

                        DateTimePicker::make('registration_close_at')
                            ->label('Closes At')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->after('registration_open_at')
                            ->helperText('When registration closes for this category'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Category')
                            ->default(true)
                            ->helperText('Only active categories are available for registration')
                            ->inline(false),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->icon(Heroicon::OutlinedTag)
                    ->description(fn (RaceCategory $record): ?string => $record->distance),

                TextColumn::make('event.name')
                    ->label('Event')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('quota')
                    ->label('Quota')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('available_slots')
                    ->label('Available')
                    ->alignCenter()
                    ->badge()
                    ->state(function (RaceCategory $record) {
                        $service = app(RegistrationService::class);

                        return $service->getAvailableSlots($record);
                    })
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),

                TextColumn::make('registered_count')
                    ->label('Registered')
                    ->alignCenter()
                    ->badge()
                    ->state(function (RaceCategory $record) {
                        $service = app(RegistrationService::class);

                        return $record->quota - $service->getAvailableSlots($record);
                    })
                    ->color('info'),

                TextColumn::make('price_individual')
                    ->label('Individual')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price_collective_5')
                    ->label('Group 5')
                    ->money('IDR')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('price_collective_10')
                    ->label('Group 10')
                    ->money('IDR')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('registration_period')
                    ->label('Registration Period')
                    ->state(function (RaceCategory $record): string {
                        $start = $record->registration_open_at->format('d M');
                        $end = $record->registration_close_at->format('d M Y');

                        return "{$start} - {$end}";
                    })
                    ->icon(Heroicon::OutlinedCalendar)
                    ->toggleable(),

                TextColumn::make('bib_range')
                    ->label('BIB Range')
                    ->state(fn (RaceCategory $record): string => "{$record->bib_start_number} - {$record->bib_end_number}")
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->alignCenter()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->preload()
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All categories')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                TernaryFilter::make('has_quota')
                    ->label('Availability')
                    ->placeholder('All categories')
                    ->queries(
                        true: fn ($query) => $query->whereRaw('quota > (SELECT COALESCE(SUM(participants_count), 0) FROM registrations WHERE race_category_id = race_categories.id)'),
                        false: fn ($query) => $query->whereRaw('quota <= (SELECT COALESCE(SUM(participants_count), 0) FROM registrations WHERE race_category_id = race_categories.id)'),
                    )
                    ->trueLabel('Has slots available')
                    ->falseLabel('Full/No slots'),
            ])
            ->recordActions([
                EditAction::make()
                    ->iconButton(),
                Action::make('toggle_status')
                    ->label(fn (RaceCategory $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn (RaceCategory $record) => $record->is_active ? Heroicon::OutlinedXCircle : Heroicon::OutlinedCheckCircle)
                    ->color(fn (RaceCategory $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (RaceCategory $record) {
                        $record->update(['is_active' => ! $record->is_active]);
                    })
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\Action::make('activate')
                        ->label('Activate Selected')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true])),

                    \Filament\Actions\Action::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false])),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No race categories yet')
            ->emptyStateDescription('Create your first race category to start accepting registrations.')
            ->emptyStateIcon(Heroicon::OutlinedTag);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRaceCategories::route('/'),
        ];
    }
}
