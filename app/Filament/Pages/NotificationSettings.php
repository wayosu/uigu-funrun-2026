<?php

namespace App\Filament\Pages;

use App\Models\NotificationSetting;
use App\Services\NotificationService;
use Filament\Actions\Action;
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
use Illuminate\Support\Facades\Cache;

class NotificationSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.notification-settings';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedCog8Tooth;

    protected static \UnitEnum|string|null $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Notification Settings';

    protected static ?int $navigationSort = 6;

    public ?array $data = [];

    public function mount(): void
    {
        $service = app(NotificationService::class);
        $whatsappSettings = NotificationSetting::firstOrCreate(
            ['channel' => 'whatsapp'],
            [
                'delay_seconds' => 5,
                'max_send_per_minute' => 60,
                'retry_limit' => 3,
                'is_active' => true,
            ]
        );

        $emailSettings = NotificationSetting::firstOrCreate(
            ['channel' => 'email'],
            [
                'delay_seconds' => 0,
                'max_send_per_minute' => 100,
                'retry_limit' => 3,
                'is_active' => true,
            ]
        );

        $this->form->fill([
            // WhatsApp Settings
            'whatsapp_enabled' => $whatsappSettings->is_active,
            'whatsapp_fonnte_token' => $whatsappSettings->fonnte_token,
            'whatsapp_delay' => $whatsappSettings->delay_seconds,
            'whatsapp_retry_limit' => $whatsappSettings->retry_limit,

            // Email Settings
            'email_enabled' => $emailSettings->is_active,
            'email_delay' => $emailSettings->delay_seconds,
            'email_retry_limit' => $emailSettings->retry_limit,

            // Templates
            'whatsapp_template_registration_created' => Cache::get(
                $service->getTemplateCacheKey('whatsapp', 'registration_created'),
                $service->getDefaultTemplate('whatsapp', 'registration_created')
            ),
            'whatsapp_template_payment_uploaded' => Cache::get(
                $service->getTemplateCacheKey('whatsapp', 'payment_uploaded'),
                $service->getDefaultTemplate('whatsapp', 'payment_uploaded')
            ),
            'whatsapp_template_payment_verified' => Cache::get(
                $service->getTemplateCacheKey('whatsapp', 'payment_verified'),
                $service->getDefaultTemplate('whatsapp', 'payment_verified')
            ),
            'whatsapp_template_payment_rejected' => Cache::get(
                $service->getTemplateCacheKey('whatsapp', 'payment_rejected'),
                $service->getDefaultTemplate('whatsapp', 'payment_rejected')
            ),
            'email_template_registration_created' => Cache::get(
                $service->getTemplateCacheKey('email', 'registration_created'),
                $service->getDefaultTemplate('email', 'registration_created')
            ),
            'email_template_payment_uploaded' => Cache::get(
                $service->getTemplateCacheKey('email', 'payment_uploaded'),
                $service->getDefaultTemplate('email', 'payment_uploaded')
            ),
            'email_template_payment_verified' => Cache::get(
                $service->getTemplateCacheKey('email', 'payment_verified'),
                $service->getDefaultTemplate('email', 'payment_verified')
            ),
            'email_template_payment_rejected' => Cache::get(
                $service->getTemplateCacheKey('email', 'payment_rejected'),
                $service->getDefaultTemplate('email', 'payment_rejected')
            ),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('WhatsApp Configuration (Fonnte)')
                    ->description('Configure WhatsApp notifications via Fonnte API')
                    ->icon(Heroicon::ChatBubbleLeftRight)
                    ->schema([
                        Toggle::make('whatsapp_enabled')
                            ->label('Enable WhatsApp Notifications')
                            ->helperText('Turn on/off WhatsApp notifications')
                            ->default(true)
                            ->live(),

                        TextInput::make('whatsapp_fonnte_token')
                            ->label('Fonnte API Token')
                            ->helperText('Get your API token from Fonnte dashboard')
                            ->placeholder('xxxxxxxxxxxxxxxxxx')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->required(fn ($get) => $get('whatsapp_enabled')),

                        TextInput::make('whatsapp_delay')
                            ->label('Delay (seconds)')
                            ->helperText('Delay between messages to avoid spam')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->default(5)
                            ->suffix('seconds')
                            ->required(),

                        TextInput::make('whatsapp_retry_limit')
                            ->label('Retry Limit')
                            ->helperText('Number of retry attempts for failed messages')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(3)
                            ->suffix('attempts')
                            ->required(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Email Configuration')
                    ->description('Configure email notifications via SMTP')
                    ->icon(Heroicon::Envelope)
                    ->schema([
                        Toggle::make('email_enabled')
                            ->label('Enable Email Notifications')
                            ->helperText('Turn on/off email notifications')
                            ->default(true),

                        TextInput::make('email_delay')
                            ->label('Delay (seconds)')
                            ->helperText('Delay between emails')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(60)
                            ->default(0)
                            ->suffix('seconds')
                            ->required(),

                        TextInput::make('email_retry_limit')
                            ->label('Retry Limit')
                            ->helperText('Number of retry attempts for failed emails')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(3)
                            ->suffix('attempts')
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Message Templates')
                    ->description('Customize notification message templates. Use {variable_name} for dynamic content.')
                    ->icon(Heroicon::DocumentText)
                    ->schema([
                        Textarea::make('whatsapp_template_registration_created')
                            ->label('WhatsApp - Registration Created')
                            ->helperText('Variables: {registration_number}, {pic_name}, {event_name}, {category}, {distance}, {type}, {participants}, {total}, {expiry}, {payment_url}, {status_url}, {ticket_url}')
                            ->rows(8)
                            ->columnSpanFull(),
                        Textarea::make('whatsapp_template_payment_uploaded')
                            ->label('WhatsApp - Payment Uploaded')
                            ->helperText('Variables: {registration_number}, {pic_name}, {payment_status}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('whatsapp_template_payment_verified')
                            ->label('WhatsApp - Payment Verified')
                            ->helperText('Variables: {registration_number}, {pic_name}, {payment_status}, {ticket_url}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('whatsapp_template_payment_rejected')
                            ->label('WhatsApp - Payment Rejected')
                            ->helperText('Variables: {registration_number}, {pic_name}, {reason}, {payment_url}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),

                        Textarea::make('email_template_registration_created')
                            ->label('Email - Registration Created')
                            ->helperText('Variables: {registration_number}, {pic_name}, {event_name}, {category}, {distance}, {type}, {participants}, {total}, {expiry}, {payment_url}, {status_url}, {ticket_url}')
                            ->rows(8)
                            ->columnSpanFull(),
                        Textarea::make('email_template_payment_uploaded')
                            ->label('Email - Payment Uploaded')
                            ->helperText('Variables: {registration_number}, {pic_name}, {payment_status}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('email_template_payment_verified')
                            ->label('Email - Payment Verified (includes PDF ticket)')
                            ->helperText('Variables: {registration_number}, {pic_name}, {payment_status}, {ticket_url}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),
                        Textarea::make('email_template_payment_rejected')
                            ->label('Email - Payment Rejected')
                            ->helperText('Variables: {registration_number}, {pic_name}, {reason}, {payment_url}, {status_url}')
                            ->rows(6)
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->statePath('data');
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
                ->label('Reset to Default')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reset to Default Settings')
                ->modalDescription('This will reset all notification settings to their default values. Are you sure?')
                ->action('resetSettings'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $service = app(NotificationService::class);

        // Update WhatsApp settings
        $whatsappSettings = NotificationSetting::where('channel', 'whatsapp')->first();
        $whatsappSettings->update([
            'is_active' => $data['whatsapp_enabled'],
            'fonnte_token' => $data['whatsapp_fonnte_token'] ?? null,
            'delay_seconds' => $data['whatsapp_delay'],
            'retry_limit' => $data['whatsapp_retry_limit'],
        ]);

        // Update Email settings
        $emailSettings = NotificationSetting::where('channel', 'email')->first();
        $emailSettings->update([
            'is_active' => $data['email_enabled'],
            'delay_seconds' => $data['email_delay'],
            'retry_limit' => $data['email_retry_limit'],
        ]);

        // Save templates to Cache
        Cache::put($service->getTemplateCacheKey('whatsapp', 'registration_created'), $data['whatsapp_template_registration_created']);
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_uploaded'), $data['whatsapp_template_payment_uploaded']);
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_verified'), $data['whatsapp_template_payment_verified']);
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_rejected'), $data['whatsapp_template_payment_rejected']);
        Cache::put($service->getTemplateCacheKey('email', 'registration_created'), $data['email_template_registration_created']);
        Cache::put($service->getTemplateCacheKey('email', 'payment_uploaded'), $data['email_template_payment_uploaded']);
        Cache::put($service->getTemplateCacheKey('email', 'payment_verified'), $data['email_template_payment_verified']);
        Cache::put($service->getTemplateCacheKey('email', 'payment_rejected'), $data['email_template_payment_rejected']);

        Notification::make()
            ->title('Settings Saved')
            ->body('Notification settings have been updated successfully.')
            ->success()
            ->send();
    }

    public function resetSettings(): void
    {
        $service = app(NotificationService::class);
        // Reset WhatsApp settings
        NotificationSetting::where('channel', 'whatsapp')->update([
            'delay_seconds' => 5,
            'max_send_per_minute' => 60,
            'retry_limit' => 3,
            'is_active' => true,
            'fonnte_token' => null,
        ]);

        // Reset Email settings
        NotificationSetting::where('channel', 'email')->update([
            'delay_seconds' => 0,
            'max_send_per_minute' => 100,
            'retry_limit' => 3,
            'is_active' => true,
        ]);

        // Reset template cache to defaults
        Cache::put($service->getTemplateCacheKey('whatsapp', 'registration_created'), $service->getDefaultTemplate('whatsapp', 'registration_created'));
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_uploaded'), $service->getDefaultTemplate('whatsapp', 'payment_uploaded'));
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_verified'), $service->getDefaultTemplate('whatsapp', 'payment_verified'));
        Cache::put($service->getTemplateCacheKey('whatsapp', 'payment_rejected'), $service->getDefaultTemplate('whatsapp', 'payment_rejected'));
        Cache::put($service->getTemplateCacheKey('email', 'registration_created'), $service->getDefaultTemplate('email', 'registration_created'));
        Cache::put($service->getTemplateCacheKey('email', 'payment_uploaded'), $service->getDefaultTemplate('email', 'payment_uploaded'));
        Cache::put($service->getTemplateCacheKey('email', 'payment_verified'), $service->getDefaultTemplate('email', 'payment_verified'));
        Cache::put($service->getTemplateCacheKey('email', 'payment_rejected'), $service->getDefaultTemplate('email', 'payment_rejected'));

        $this->mount();

        Notification::make()
            ->title('Settings Reset')
            ->body('Notification settings have been reset to default values.')
            ->success()
            ->send();
    }

}
