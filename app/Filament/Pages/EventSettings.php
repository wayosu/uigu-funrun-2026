<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EventSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static \UnitEnum|string|null $navigationGroup = 'Event Management';

    protected static ?string $navigationLabel = 'Event Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.event-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $event = Event::firstOrNew();
        $this->form->fill($event->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Information')
                    ->description('Configure event details and settings')
                    ->schema([
                        TextInput::make('name')
                            ->label('Event Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        DatePicker::make('date')
                            ->label('Event Date')
                            ->required(),

                        TextInput::make('location')
                            ->label('Location')
                            ->required()
                            ->maxLength(255),

                        RichEditor::make('description')
                            ->label('Description')
                            ->columnSpanFull(),

                        FileUpload::make('logo')
                            ->label('Event Logo')
                            ->image()
                            ->directory('events/logos')
                            ->visibility('public'),

                        FileUpload::make('banner')
                            ->label('Event Banner')
                            ->image()
                            ->directory('events/banners')
                            ->visibility('public'),

                        Toggle::make('is_active')
                            ->label('Event Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Event')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->color('success')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $event = Event::firstOrNew();
        $event->fill($data);
        $event->save();

        Notification::make()
            ->title('Event Saved')
            ->body('Event information has been saved successfully.')
            ->success()
            ->send();
    }
}
