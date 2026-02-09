<?php

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Payments\Pages\ManagePayments;
use App\Models\Payment;
use App\Services\PaymentService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static \UnitEnum|string|null $navigationGroup = 'Operations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $recordTitleAttribute = 'amount';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->prefix('IDR'),
                FileUpload::make('proof_path')
                    ->image()
                    ->directory('payments/proofs')
                    ->columnSpanFull()
                    ->openable(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('registration.registration_number')
                    ->label('Registration #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->weight('bold'),
                TextColumn::make('registration.raceCategory.name')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('registration.pic_name')
                    ->label('PIC Name')
                    ->searchable()
                    ->limit(25),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->weight('semibold'),
                ImageColumn::make('proof_path')
                    ->label('Proof')
                    ->square()
                    ->disk('local')
                    ->defaultImageUrl(url('/images/no-image.png')),
                TextColumn::make('registration.status')
                    ->label('Verification Status')
                    ->badge()
                    ->formatStateUsing(function (PaymentStatus $state): string {
                        return match ($state) {
                            PaymentStatus::PaymentUploaded => 'Needs Verification',
                            PaymentStatus::PaymentVerified => 'Verified',
                            default => $state->label(),
                        };
                    })
                    ->color(fn (PaymentStatus $state): string => $state->color())
                    ->icon(fn (PaymentStatus $state): string => $state->icon())
                    ->description(fn (Payment $record): ?string => $record->rejection_reason
                        ? 'Rejected: '.str($record->rejection_reason)->limit(40)
                        : null
                    )
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn (Payment $record): string => $record->created_at->diffForHumans()),
                TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'asc')
            ->filters([
                SelectFilter::make('verification_status')
                    ->label('Verification Status')
                    ->options([
                        'pending' => '🕐 Needs Verification',
                        'verified' => '✅ Verified',
                        'rejected' => '❌ Rejected',
                    ])
                    ->default('pending')
                    ->query(function ($query, array $data) {
                        if (! isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }

                        return match ($data['value']) {
                            'pending' => $query->whereHas('registration', function ($q) {
                                $q->where('status', PaymentStatus::PaymentUploaded);
                            })->whereNull('rejection_reason'),
                            'verified' => $query->whereHas('registration', function ($q) {
                                $q->where('status', PaymentStatus::PaymentVerified);
                            }),
                            'rejected' => $query->whereNotNull('rejection_reason'),
                            default => $query,
                        };
                    }),
                SelectFilter::make('race_category')
                    ->label('Race Category')
                    ->relationship('registration.raceCategory', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading(fn (Payment $record): string => "Payment Details - {$record->registration->registration_number}")
                    ->schema([
                        Section::make()
                            ->schema([
                                Flex::make([
                                    Grid::make(2)
                                        ->schema([
                                            TextEntry::make('registration.registration_number')
                                                ->label('Registration Number')
                                                ->copyable()
                                                ->icon(Heroicon::OutlinedDocumentText)
                                                ->weight('bold')
                                                ->color('primary'),
                                            TextEntry::make('registration.raceCategory.name')
                                                ->label('Race Category')
                                                ->badge()
                                                ->color('success'),
                                            TextEntry::make('amount')
                                                ->label('Payment Amount')
                                                ->money('IDR')
                                                ->size('lg')
                                                ->weight('bold')
                                                ->color('success'),
                                            TextEntry::make('registration.status')
                                                ->label('Payment Status')
                                                ->badge()
                                                ->formatStateUsing(fn (PaymentStatus $state): string => $state->label())
                                                ->color(fn (PaymentStatus $state): string => $state->color()),
                                            TextEntry::make('created_at')
                                                ->label('Uploaded At')
                                                ->dateTime('d M Y, H:i')
                                                ->icon(Heroicon::OutlinedClock),
                                            TextEntry::make('verified_at')
                                                ->label('Verified At')
                                                ->dateTime('d M Y, H:i')
                                                ->placeholder('Not verified yet')
                                                ->icon(Heroicon::OutlinedCheckCircle),
                                            TextEntry::make('verifier.name')
                                                ->label('Verified By')
                                                ->placeholder('-')
                                                ->icon(Heroicon::OutlinedUser),
                                            TextEntry::make('rejection_reason')
                                                ->label('Rejection Reason')
                                                ->color('danger')
                                                ->visible(fn (Payment $record): bool => ! empty($record->rejection_reason))
                                                ->columnSpanFull(),
                                        ]),
                                    ImageEntry::make('proof_path')
                                        ->label('Payment Proof')
                                        ->disk('local')
                                        ->height(400)
                                        ->hiddenLabel()
                                        ->grow(false),
                                ])->from('md'),
                            ]),
                        Section::make('Participants')
                            ->description('List of participants for this registration')
                            ->schema([
                                TextEntry::make('participants_list')
                                    ->label('')
                                    ->state(function (Payment $record): string {
                                        return $record->registration->participants
                                            ->map(function ($participant, $index) {
                                                $pic = $participant->is_pic ? ' ⭐ (PIC)' : '';
                                                $bib = $participant->bib_number ? " | BIB: {$participant->bib_number}" : '';

                                                return ($index + 1).". {$participant->name}{$pic}{$bib}";
                                            })
                                            ->join("\n");
                                    })
                                    ->html()
                                    ->formatStateUsing(fn (string $state): string => nl2br(e($state))),
                            ])
                            ->collapsible()
                            ->collapsed(fn (Payment $record): bool => $record->registration->participants_count <= 1),
                    ])
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('verify')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Payment')
                    ->modalDescription('Are you sure you want to verify this payment? BIB numbers will be generated automatically.')
                    ->visible(fn (Payment $record) => $record->registration->status->canBeVerified())
                    ->action(function (Payment $record) {
                        $paymentService = app(PaymentService::class);

                        try {
                            $paymentService->verifyPayment(
                                $record,
                                auth()->user(),
                                true
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('Payment Verified Successfully')
                                ->body('BIB numbers have been generated for all participants.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Verification Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Payment')
                    ->modalDescription('Please provide a clear reason for rejecting this payment. The user will see this message.')
                    ->visible(fn (Payment $record) => $record->registration->status->canBeVerified())
                    ->form([
                        Select::make('rejection_reason_template')
                            ->label('Quick Reason (Optional)')
                            ->placeholder('Select a common reason...')
                            ->options([
                                'Bukti transfer tidak jelas atau tidak terbaca' => 'Bukti transfer tidak jelas atau tidak terbaca',
                                'Nominal transfer tidak sesuai dengan biaya pendaftaran' => 'Nominal transfer tidak sesuai dengan biaya pendaftaran',
                                'Transfer dari rekening atas nama berbeda' => 'Transfer dari rekening atas nama berbeda',
                                'Bukti transfer sudah kadaluarsa/expired' => 'Bukti transfer sudah kadaluarsa/expired',
                                'Rekening tujuan transfer tidak sesuai' => 'Rekening tujuan transfer tidak sesuai',
                                'Bukti transfer tampak palsu atau sudah digunakan' => 'Bukti transfer tampak palsu atau sudah digunakan',
                            ])
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('rejection_reason', $state);
                                }
                            }),
                        Textarea::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3)
                            ->placeholder('Please provide a reason for rejecting this payment...'),
                    ])
                    ->action(function (array $data, Payment $record) {
                        $paymentService = app(PaymentService::class);

                        try {
                            $paymentService->verifyPayment(
                                $record,
                                auth()->user(),
                                false,
                                $data['rejection_reason']
                            );

                            \Filament\Notifications\Notification::make()
                                ->title('Payment Rejected')
                                ->body('The user can re-upload their payment proof.')
                                ->warning()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Rejection Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkAction::make('bulk_verify')
                    ->label('Verify Selected Payments')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk Verify Payments')
                    ->modalDescription(fn ($records) => 'Are you sure you want to verify '.$records->count().' payment(s)? BIB numbers will be generated automatically for all participants. This action cannot be undone.')
                    ->action(function ($records) {
                        $paymentService = app(PaymentService::class);
                        $successCount = 0;
                        $failCount = 0;
                        $errors = [];

                        foreach ($records as $payment) {
                            try {
                                if ($payment->registration->status->canBeVerified()) {
                                    $paymentService->verifyPayment(
                                        $payment,
                                        auth()->user(),
                                        true
                                    );
                                    $successCount++;
                                } else {
                                    $failCount++;
                                    $errors[] = "REG-{$payment->registration->registration_number}: Cannot verify (status: {$payment->registration->status->label()})";
                                }
                            } catch (\Exception $e) {
                                $failCount++;
                                $errors[] = "REG-{$payment->registration->registration_number}: {$e->getMessage()}";
                            }
                        }

                        if ($successCount > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title("Successfully verified {$successCount} payment(s)")
                                ->body($failCount > 0 ? "{$failCount} payment(s) failed to verify." : null)
                                ->success()
                                ->send();
                        }

                        if ($failCount > 0 && $successCount === 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('All verifications failed')
                                ->body(implode("\n", array_slice($errors, 0, 3)))
                                ->danger()
                                ->send();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePayments::route('/'),
        ];
    }
}
