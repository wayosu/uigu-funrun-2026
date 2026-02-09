<?php

namespace App\Filament\Pages;

use App\Models\CheckinSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CheckinSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Check-in Settings';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.checkin-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = CheckinSetting::firstOrNew();
        $this->form->fill($settings->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Check-in Configuration')
                    ->description('Configure check-in system settings and access control')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Enable Check-in System')
                            ->default(false)
                            ->columnSpanFull(),

                        TextInput::make('pin_code')
                            ->label('PIN Code')
                            ->required()
                            ->numeric()
                            ->minLength(4)
                            ->maxLength(6)
                            ->mask('999999'),

                        DateTimePicker::make('checkin_start_time')
                            ->label('Check-in Start Time')
                            ->seconds(false),

                        DateTimePicker::make('checkin_end_time')
                            ->label('Check-in End Time')
                            ->seconds(false),

                        TextInput::make('check_in_location')
                            ->label('Check-in Location')
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('instructions')
                            ->label('Check-in Instructions')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('QR Code Settings')
                    ->description('Configure QR code scanning behavior')
                    ->schema([
                        Toggle::make('allow_duplicate_scan')
                            ->label('Allow Duplicate Scans')
                            ->default(false),

                        Toggle::make('require_photo_verification')
                            ->label('Require Photo Verification')
                            ->default(false),

                        Toggle::make('auto_print_bib')
                            ->label('Auto Print BIB')
                            ->default(false),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = CheckinSetting::firstOrNew();
        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->title('Settings Saved')
            ->body('Check-in settings have been saved successfully.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->action('save'),

            Action::make('reset')
                ->label('Reset PIN')
                ->icon(Heroicon::OutlinedKey)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset PIN Code')
                ->modalDescription('This will generate a new random PIN code. Current PIN will be invalidated.')
                ->action(function () {
                    $settings = CheckinSetting::firstOrNew();
                    $settings->pin_code = str_pad((string) rand(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $settings->save();

                    $this->mount();

                    Notification::make()
                        ->title('PIN Reset')
                        ->body("New PIN code: {$settings->pin_code}")
                        ->success()
                        ->duration(10000)
                        ->send();
                }),
        ];
    }
}
