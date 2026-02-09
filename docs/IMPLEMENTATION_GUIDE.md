# Implementation Guide — Fun Run Event Registration

## Overview

Panduan step-by-step untuk mengimplementasikan sistem dari nol hingga production-ready.

---

## Implementation Phases

### Phase 1: Foundation (Week 1)
- Database setup & migrations
- Models & relationships
- Seeders & factories

### Phase 2: Core Features (Week 2-3)
- Event & race category management
- Public registration flow
- Payment system

### Phase 3: Admin Panel (Week 4)
- Filament resources
- Dashboard & widgets
- Reports & exports

### Phase 4: Notifications (Week 5)
- WhatsApp integration
- Email templates
- Queue setup

### Phase 5: Advanced Features (Week 6)
- E-Ticket generation
- Check-in system
- Auto-expiry scheduler

### Phase 6: Testing & Polish (Week 7)
- Unit & feature tests
- Performance optimization
- Bug fixes

### Phase 7: Deployment (Week 8)
- Server setup
- Production deployment
- Monitoring setup

---

## Phase 1: Foundation (Week 1)

### Task 1.1: Project Setup

```bash
# Clone or check existing project
cd /path/to/project

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database configuration
# Edit .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=funrun_db
DB_USERNAME=root
DB_PASSWORD=

# Create database
mysql -u root -p
CREATE DATABASE funrun_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Task 1.2: Create Migrations

**Order of creation**:

1. **Users table** (already exists, modify if needed)
```bash
php artisan make:migration add_additional_fields_to_users_table --table=users
```

2. **Settings tables**
```bash
php artisan make:migration create_payment_settings_table
php artisan make:migration create_notification_settings_table
php artisan make:migration create_checkin_settings_table
```

3. **Core tables**
```bash
php artisan make:migration create_events_table
php artisan make:migration create_race_categories_table
php artisan make:migration create_registration_sequences_table
```

4. **Registration flow tables**
```bash
php artisan make:migration create_registrations_table
php artisan make:migration create_participants_table
php artisan make:migration create_payments_table
```

5. **Operational tables**
```bash
php artisan make:migration create_checkins_table
php artisan make:migration create_notification_logs_table
```

**Example Migration** (registrations):
```php
// database/migrations/2024_02_08_000005_create_registrations_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('race_category_id')->constrained()->restrictOnDelete();
            $table->string('registration_number', 50)->unique();
            $table->enum('registration_type', ['individual', 'collective_5', 'collective_10']);
            $table->unsignedTinyInteger('total_participants');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending_payment',
                'payment_uploaded',
                'payment_verified',
                'expired',
                'cancelled',
            ])->default('pending_payment');
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expired_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index(['race_category_id', 'status']);
            $table->index('expired_at');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
```

### Task 1.3: Create Models

**Generate models**:
```bash
php artisan make:model Event
php artisan make:model RaceCategory
php artisan make:model Registration
php artisan make:model Participant
php artisan make:model Payment
php artisan make:model Checkin
php artisan make:model NotificationLog
php artisan make:model RegistrationSequence
php artisan make:model PaymentSetting
php artisan make:model NotificationSetting
php artisan make:model CheckinSetting
```

**Example Model** (Registration):
```php
// app/Models/Registration.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'race_category_id',
        'registration_number',
        'registration_type',
        'total_participants',
        'total_amount',
        'status',
        'payment_verified_at',
        'payment_verified_by',
        'expired_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'payment_verified_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    // Relationships
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function raceCategory(): BelongsTo
    {
        return $this->belongsTo(RaceCategory::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_verified_by');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    // Scopes
    public function scopePendingPayment($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopePaymentVerified($query)
    {
        return $query->where('status', 'payment_verified');
    }

    public function scopeExpired($query)
    {
        return $query->where('expired_at', '<=', now())
            ->where('status', 'pending_payment');
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function getPicAttribute(): ?Participant
    {
        return $this->participants()->where('is_pic', true)->first();
    }
}
```

### Task 1.4: Create Factories

```bash
php artisan make:factory EventFactory
php artisan make:factory RaceCategoryFactory
php artisan make:factory RegistrationFactory
php artisan make:factory ParticipantFactory
php artisan make:factory PaymentFactory
```

**Example Factory** (Registration):
```php
// database/factories/RegistrationFactory.php
namespace Database\Factories;

use App\Models\Event;
use App\Models\RaceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegistrationFactory extends Factory
{
    public function definition(): array
    {
        $category = RaceCategory::inRandomOrder()->first() ?? RaceCategory::factory()->create();
        $type = fake()->randomElement(['individual', 'collective_5', 'collective_10']);
        
        $participants = match($type) {
            'individual' => 1,
            'collective_5' => 5,
            'collective_10' => 10,
        };
        
        $amount = match($type) {
            'individual' => $category->price_individual,
            'collective_5' => $category->price_collective_5,
            'collective_10' => $category->price_collective_10,
        };

        return [
            'event_id' => $category->event_id,
            'race_category_id' => $category->id,
            'registration_number' => $category->registration_prefix . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'registration_type' => $type,
            'total_participants' => $participants,
            'total_amount' => $amount,
            'status' => fake()->randomElement([
                'pending_payment',
                'payment_uploaded',
                'payment_verified',
            ]),
            'expired_at' => now()->addHours(24),
        ];
    }

    public function withParticipants(): static
    {
        return $this->afterCreating(function (Registration $registration) {
            $count = $registration->total_participants;
            
            for ($i = 0; $i < $count; $i++) {
                Participant::factory()->create([
                    'registration_id' => $registration->id,
                    'is_pic' => $i === 0,
                ]);
            }
        });
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'payment_verified',
            'payment_verified_at' => now(),
        ]);
    }
}
```

### Task 1.5: Create Seeders

```bash
php artisan make:seeder DatabaseSeeder
php artisan make:seeder SettingsSeeder
php artisan make:seeder EventSeeder
```

**Example Seeder**:
```php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@funrun.test',
            'password' => bcrypt('password'),
        ]);

        // 2. Settings
        $this->call(SettingsSeeder::class);

        // 3. Event & categories
        $this->call(EventSeeder::class);

        // 4. Sample registrations (dev only)
        if (app()->environment('local')) {
            \App\Models\Registration::factory()
                ->count(50)
                ->withParticipants()
                ->create();
        }
    }
}
```

### Task 1.6: Run Migrations & Seed

```bash
php artisan migrate:fresh --seed
```

---

## Phase 2: Core Features (Week 2-3)

### Task 2.1: Create Enums

```bash
php artisan make:enum RegistrationType
php artisan make:enum PaymentStatus
php artisan make:enum Gender
php artisan make:enum JerseySize
php artisan make:enum PaymentMethod
```

**Example Enum**:
```php
// app/Enums/PaymentStatus.php
namespace App\Enums;

enum PaymentStatus: string
{
    case PendingPayment = 'pending_payment';
    case PaymentUploaded = 'payment_uploaded';
    case PaymentVerified = 'payment_verified';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PendingPayment => 'Pending Payment',
            self::PaymentUploaded => 'Payment Uploaded',
            self::PaymentVerified => 'Payment Verified',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PendingPayment => 'warning',
            self::PaymentUploaded => 'info',
            self::PaymentVerified => 'success',
            self::Expired => 'danger',
            self::Cancelled => 'secondary',
        };
    }
}
```

### Task 2.2: Create Actions

```bash
php artisan make:class Actions/Registration/GenerateRegistrationNumberAction
php artisan make:class Actions/Registration/CreateRegistrationAction
php artisan make:class Actions/Registration/ValidateAvailableSlotsAction
php artisan make:class Actions/Payment/ProcessPaymentVerificationAction
php artisan make:class Actions/Payment/GenerateBibNumberAction
```

**Example Action**:
```php
// app/Actions/Registration/GenerateRegistrationNumberAction.php
namespace App\Actions\Registration;

use App\Models\RaceCategory;
use App\Models\RegistrationSequence;
use Illuminate\Support\Facades\DB;

class GenerateRegistrationNumberAction
{
    public function execute(RaceCategory $category): string
    {
        return DB::transaction(function () use ($category) {
            // Get or create sequence
            $sequence = RegistrationSequence::query()
                ->where('race_category_id', $category->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['race_category_id' => $category->id],
                    ['last_number' => 0]
                );

            // Increment
            $sequence->increment('last_number');
            $number = $sequence->last_number;

            // Format: PREFIX-0001
            return $category->registration_prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
        });
    }
}
```

### Task 2.3: Create Services

```bash
php artisan make:class Services/RegistrationService
php artisan make:class Services/PaymentService
php artisan make:class Services/BibNumberService
php artisan make:class Services/TicketService
```

**Example Service**:
```php
// app/Services/RegistrationService.php
namespace App\Services;

use App\Actions\Registration\GenerateRegistrationNumberAction;
use App\Actions\Registration\ValidateAvailableSlotsAction;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationType;
use App\Events\RegistrationCreated;
use App\Models\PaymentSetting;
use App\Models\RaceCategory;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class RegistrationService
{
    public function __construct(
        private GenerateRegistrationNumberAction $generateNumber,
        private ValidateAvailableSlotsAction $validateSlots,
    ) {}

    public function createIndividualRegistration(
        RaceCategory $category,
        array $participantData
    ): Registration {
        return DB::transaction(function () use ($category, $participantData) {
            // 1. Validate slots
            $this->validateSlots->execute($category, 1);

            // 2. Generate registration number
            $registrationNumber = $this->generateNumber->execute($category);

            // 3. Create registration
            $registration = Registration::create([
                'event_id' => $category->event_id,
                'race_category_id' => $category->id,
                'registration_number' => $registrationNumber,
                'registration_type' => RegistrationType::Individual->value,
                'total_participants' => 1,
                'total_amount' => $category->price_individual,
                'status' => PaymentStatus::PendingPayment->value,
                'expired_at' => now()->addHours(
                    PaymentSetting::first()->payment_deadline_hours ?? 24
                ),
            ]);

            // 4. Create participant
            $registration->participants()->create([
                'is_pic' => true,
                ...$participantData,
            ]);

            // 5. Fire event
            event(new RegistrationCreated($registration));

            return $registration->load('participants');
        });
    }

    public function createCollectiveRegistration(
        RaceCategory $category,
        array $picData,
        array $membersData,
        string $type
    ): Registration {
        $participantCount = $type === 'collective_5' ? 5 : 10;
        $amount = $type === 'collective_5' 
            ? $category->price_collective_5 
            : $category->price_collective_10;

        return DB::transaction(function () use ($category, $picData, $membersData, $type, $participantCount, $amount) {
            // 1. Validate slots
            $this->validateSlots->execute($category, $participantCount);

            // 2. Generate registration number
            $registrationNumber = $this->generateNumber->execute($category);

            // 3. Create registration
            $registration = Registration::create([
                'event_id' => $category->event_id,
                'race_category_id' => $category->id,
                'registration_number' => $registrationNumber,
                'registration_type' => $type,
                'total_participants' => $participantCount,
                'total_amount' => $amount,
                'status' => PaymentStatus::PendingPayment->value,
                'expired_at' => now()->addHours(
                    PaymentSetting::first()->payment_deadline_hours ?? 24
                ),
            ]);

            // 4. Create PIC
            $registration->participants()->create([
                'is_pic' => true,
                ...$picData,
            ]);

            // 5. Create members
            foreach ($membersData as $memberData) {
                $registration->participants()->create([
                    'is_pic' => false,
                    ...$memberData,
                ]);
            }

            // 6. Fire event
            event(new RegistrationCreated($registration));

            return $registration->load('participants');
        });
    }
}
```

### Task 2.4: Create Form Requests

```bash
php artisan make:request RegistrationRequest
php artisan make:request PaymentUploadRequest
```

**Example Request**:
```php
// app/Http/Requests/RegistrationRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'race_category_id' => 'required|exists:race_categories,id',
            'registration_type' => 'required|in:individual,collective_5,collective_10',
        ];

        // PIC data (always required)
        $rules = array_merge($rules, $this->participantRules('pic'));

        // Members data (if collective)
        if (str_starts_with($this->registration_type, 'collective')) {
            $memberCount = $this->registration_type === 'collective_5' ? 4 : 9;
            
            $rules['members'] = "required|array|size:{$memberCount}";
            
            foreach (range(0, $memberCount - 1) as $i) {
                foreach ($this->participantRules("members.{$i}", false) as $key => $rule) {
                    $rules[$key] = $rule;
                }
            }
            
            // Ensure unique emails & whatsapp
            $rules['members.*.email'] = array_merge(
                $rules['members.*.email'] ?? [],
                ['distinct', 'different:pic.email']
            );
            $rules['members.*.whatsapp'] = array_merge(
                $rules['members.*.whatsapp'] ?? [],
                ['distinct', 'different:pic.whatsapp']
            );
        }

        return $rules;
    }

    private function participantRules(string $prefix, bool $includePic = true): array
    {
        return [
            "{$prefix}.full_name" => 'required|string|max:255',
            "{$prefix}.email" => 'required|email|max:255',
            "{$prefix}.whatsapp" => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
            "{$prefix}.gender" => 'required|in:male,female',
            "{$prefix}.date_of_birth" => 'required|date|before:-5 years',
            "{$prefix}.jersey_size" => 'required|in:xs,s,m,l,xl,xxl,xxxl',
            "{$prefix}.identity_number" => 'nullable|string|max:50',
            "{$prefix}.emergency_contact_name" => 'required|string|max:255',
            "{$prefix}.emergency_contact_phone" => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'pic.email.required' => 'PIC email is required',
            'members.*.email.distinct' => 'All team members must have unique email addresses',
            'members.*.email.different' => 'Team member email must be different from PIC email',
        ];
    }
}
```

### Task 2.5: Create Controllers

```bash
php artisan make:controller RegistrationController
php artisan make:controller PaymentController
```

**Example Controller**:
```php
// app/Http/Controllers/RegistrationController.php
namespace App\Http\Controllers;

use App\Http\Requests\RegistrationRequest;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Services\RegistrationService;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService
    ) {}

    public function index()
    {
        $event = Event::query()
            ->where('is_active', true)
            ->with(['raceCategories' => function ($query) {
                $query->where('is_active', true)
                    ->withCount(['registrations as confirmed_count' => function ($q) {
                        $q->where('status', 'payment_verified');
                    }]);
            }])
            ->firstOrFail();

        return view('registration.index', compact('event'));
    }

    public function store(RegistrationRequest $request)
    {
        $category = RaceCategory::findOrFail($request->race_category_id);

        try {
            if ($request->registration_type === 'individual') {
                $registration = $this->registrationService->createIndividualRegistration(
                    $category,
                    $request->input('pic')
                );
            } else {
                $registration = $this->registrationService->createCollectiveRegistration(
                    $category,
                    $request->input('pic'),
                    $request->input('members'),
                    $request->registration_type
                );
            }

            return redirect()
                ->route('payment.show', $registration->registration_number)
                ->with('success', 'Registration created successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

### Task 2.6: Create Routes

```php
// routes/web.php
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckinController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/register', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/register', [RegistrationController::class, 'store'])->name('registration.store');

Route::get('/payment/{registrationNumber}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/payment/{registrationNumber}', [PaymentController::class, 'upload'])->name('payment.upload');

Route::get('/tickets/download/{registrationNumber}/{token}', [TicketController::class, 'download'])->name('tickets.download');

// Check-in routes
Route::prefix('checkin')->name('checkin.')->group(function () {
    Route::get('/login', [CheckinController::class, 'login'])->name('login');
    Route::post('/login', [CheckinController::class, 'authenticate'])->name('authenticate');
    
    Route::middleware('checkin.auth')->group(function () {
        Route::get('/', [CheckinController::class, 'scanner'])->name('scanner');
        Route::post('/scan', [CheckinController::class, 'scan'])->name('scan');
        Route::post('/logout', [CheckinController::class, 'logout'])->name('logout');
    });
});
```

---

## Phase 3: Admin Panel (Week 4)

### Task 3.1: Create Filament Resources

```bash
php artisan make:filament-resource Event --generate
php artisan make:filament-resource RaceCategory --generate
php artisan make:filament-resource Registration --generate
php artisan make:filament-resource Participant --generate
php artisan make:filament-resource Payment --generate
```

**Example Resource** (Payment - simplified):
```php
// app/Filament/Resources/PaymentResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Registrations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('registration_id')
                            ->relationship('registration', 'registration_number')
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'qris' => 'QRIS',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->required(),
                        
                        Forms\Components\FileUpload::make('payment_proof_path')
                            ->label('Payment Proof')
                            ->image()
                            ->required(),
                        
                        Forms\Components\DateTimePicker::make('payment_date')
                            ->required(),
                        
                        Forms\Components\Textarea::make('notes')
                            ->rows(3),
                    ]),
                
                Forms\Components\Section::make('Verification')
                    ->schema([
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->disabled(),
                        
                        Forms\Components\Select::make('verified_by')
                            ->relationship('verifiedBy', 'name')
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(3),
                    ])
                    ->hidden(fn (?Payment $record) => !$record || !$record->verified_at),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration.registration_number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge(),
                
                Tables\Columns\IconColumn::make('verified_at')
                    ->boolean()
                    ->label('Verified'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'qris' => 'QRIS',
                        'bank_transfer' => 'Bank Transfer',
                    ]),
                
                Tables\Filters\TernaryFilter::make('verified')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('verified_at'),
                        false: fn ($query) => $query->whereNull('verified_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record) => !$record->verified_at)
                    ->requiresConfirmation()
                    ->action(function (Payment $payment) {
                        app(PaymentService::class)->verifyPayment($payment, auth()->user());
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Payment $record) => !$record->verified_at)
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->required()
                            ->label('Reason for rejection'),
                    ])
                    ->action(function (Payment $payment, array $data) {
                        app(PaymentService::class)->rejectPayment($payment, $data['rejection_reason']);
                    }),
            ]);
    }
}
```

### Task 3.2: Create Custom Pages

```bash
php artisan make:filament-page Settings/PaymentSettings
php artisan make:filament-page Settings/NotificationSettings
php artisan make:filament-page Reports/FinancialReport
```

### Task 3.3: Create Widgets

```bash
php artisan make:filament-widget StatsOverview --stats
php artisan make:filament-widget RegistrationChart --chart
```

---

## Phase 4-7: [Continued in next sections]

Due to length constraints, the remaining phases (4-7) cover:
- **Phase 4**: Notification system implementation
- **Phase 5**: E-Ticket & Check-in system
- **Phase 6**: Testing strategy
- **Phase 7**: Deployment process

See **DEPLOYMENT_GUIDE.md** for production deployment details.

---

## Quick Start Commands

```bash
# Development
php artisan serve
npm run dev
php artisan queue:work
php artisan horizon

# Testing
php artisan test
php artisan test --filter=RegistrationTest

# Database
php artisan migrate:fresh --seed
php artisan db:seed --class=EventSeeder

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue
php artisan queue:work --queue=high,default,notifications
php artisan queue:retry all
php artisan queue:flush

# Horizon
php artisan horizon
php artisan horizon:terminate

# Scheduler
php artisan schedule:work

# Code Quality
vendor/bin/pint
php artisan test --coverage
```

---

## Troubleshooting

### Common Issues

**Issue**: Registration number duplicate  
**Solution**: Ensure RegistrationSequence uses `lockForUpdate()` in transaction

**Issue**: BIB number duplicate  
**Solution**: Add unique constraint + transaction locking

**Issue**: Queue jobs not processing  
**Solution**: Check Redis connection, restart queue worker

**Issue**: WhatsApp not sending  
**Solution**: Verify Fonnte API key & device status

**Issue**: Slow queries  
**Solution**: Add missing indexes, use eager loading

---

## Next Steps

1. ✅ Complete Phase 1-3 implementation
2. ✅ Setup testing environment
3. ✅ Implement notification system
4. ✅ Deploy to staging
5. ✅ User acceptance testing
6. ✅ Production deployment
7. ✅ Monitor & optimize

See **DEPLOYMENT_GUIDE.md** for production deployment checklist.
