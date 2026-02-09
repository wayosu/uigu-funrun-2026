# System Architecture — Fun Run Event Registration

## Overview

Sistem pendaftaran event fun run/marathon ini dibangun dengan arsitektur modular dan scalable menggunakan Laravel 12 ecosystem. Sistem dirancang untuk menangani high traffic saat peak registration dengan memanfaatkan queue, caching, dan database optimization.

---

## Tech Stack

### Backend Framework
- **Laravel 12** (PHP 8.4+)
  - Eloquent ORM untuk database operations
  - Queue system untuk background processing
  - Event & Listener untuk decoupled logic
  - Task Scheduling untuk automated jobs
  - Cache system untuk performance optimization

### Admin Panel
- **Filament v5**
  - Dashboard & Analytics
  - CRUD Resource Management
  - Custom Pages & Widgets
  - Form Builder & Table Builder
  - Bulk Actions & Export functionality

### Frontend (Public Site)
- **Laravel Blade Templates**
- **Livewire v4** (untuk reactive components)
- **Alpine.js** (untuk lightweight interactions)
- **Tailwind CSS v4** (untuk styling)

### Queue & Job Processing
- **Laravel Horizon** (queue monitoring)
- **Redis** (queue driver & cache)
- **Predis** (Redis client)

### Database
- **MySQL 8.0+** / **PostgreSQL 14+**
  - Support untuk transactions
  - Foreign key constraints
  - Unique constraints
  - Full-text search (optional)

### Notification Services
- **WhatsApp**: Fonnte API
- **Email**: Laravel Mail (SMTP/SES/Mailgun/Postmark)

### File Storage
- **Local Storage** (development)
- **AWS S3** / **DigitalOcean Spaces** (production)

### Additional Tools
- **Laravel Pint** (code formatting)
- **Pest PHP** (testing framework)
- **Spatie Packages**:
  - `spatie/laravel-medialibrary` (untuk file management)
  - `spatie/laravel-activitylog` (untuk audit trail)
  - `spatie/laravel-permission` (untuk role & permission)

---

## Application Architecture

### Layered Architecture

```
┌─────────────────────────────────────────────────┐
│              Presentation Layer                  │
│  (Filament Admin, Blade Views, Livewire)        │
└────────────────┬────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────┐
│              Application Layer                   │
│     (Controllers, Livewire Components,           │
│      Filament Resources & Pages)                 │
└────────────────┬────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────┐
│               Domain Layer                       │
│  (Services, Actions, Events, Jobs, Policies)    │
└────────────────┬────────────────────────────────┘
                 │
┌────────────────▼────────────────────────────────┐
│             Infrastructure Layer                 │
│   (Models, Repositories, External APIs,          │
│    Database, Cache, Queue, Storage)              │
└─────────────────────────────────────────────────┘
```

### Directory Structure

```
app/
├── Actions/                      # Single-purpose action classes
│   ├── Registration/
│   │   ├── CreateRegistrationAction.php
│   │   ├── GenerateRegistrationNumberAction.php
│   │   └── ValidateRegistrationAction.php
│   ├── Payment/
│   │   ├── ProcessPaymentVerificationAction.php
│   │   └── GenerateBibNumberAction.php
│   └── Notification/
│       ├── SendWhatsAppNotificationAction.php
│       └── SendEmailNotificationAction.php
│
├── Services/                     # Business logic services
│   ├── RegistrationService.php
│   ├── PaymentService.php
│   ├── NotificationService.php
│   ├── BibNumberService.php
│   └── TicketService.php
│
├── Jobs/                         # Queue jobs
│   ├── ProcessRegistrationJob.php
│   ├── SendWhatsAppNotificationJob.php
│   ├── SendEmailNotificationJob.php
│   ├── GenerateETicketJob.php
│   └── ExpireUnpaidRegistrationsJob.php
│
├── Events/                       # Domain events
│   ├── RegistrationCreated.php
│   ├── PaymentUploaded.php
│   ├── PaymentVerified.php
│   └── RegistrationExpired.php
│
├── Listeners/                    # Event listeners
│   ├── SendRegistrationConfirmation.php
│   ├── SendPaymentUploadConfirmation.php
│   └── SendPaymentVerifiedNotification.php
│
├── Models/                       # Eloquent models
│   ├── Event.php
│   ├── RaceCategory.php
│   ├── Registration.php
│   ├── Participant.php
│   ├── Payment.php
│   ├── Checkin.php
│   ├── NotificationLog.php
│   ├── RegistrationSequence.php
│   ├── PaymentSetting.php
│   ├── NotificationSetting.php
│   └── CheckinSetting.php
│
├── Filament/                     # Filament admin panel
│   ├── Resources/
│   │   ├── EventResource.php
│   │   ├── RaceCategoryResource.php
│   │   ├── RegistrationResource.php
│   │   ├── ParticipantResource.php
│   │   └── PaymentResource.php
│   ├── Pages/
│   │   ├── Dashboard.php
│   │   ├── Settings/
│   │   │   ├── PaymentSettings.php
│   │   │   ├── NotificationSettings.php
│   │   │   └── CheckinSettings.php
│   │   └── Reports/
│   │       ├── FinancialReport.php
│   │       └── RegistrationReport.php
│   ├── Widgets/
│   │   ├── StatsOverview.php
│   │   ├── RegistrationChart.php
│   │   └── PaymentStatusChart.php
│   └── Exports/
│       ├── ParticipantsExport.php
│       └── FinancialExport.php
│
├── Http/
│   ├── Controllers/
│   │   ├── RegistrationController.php
│   │   ├── PaymentController.php
│   │   └── CheckinController.php
│   ├── Middleware/
│   │   ├── CheckinPinAuth.php
│   │   └── RateLimitRegistration.php
│   └── Requests/
│       ├── RegistrationRequest.php
│       └── PaymentUploadRequest.php
│
├── Notifications/                # Custom notifications
│   ├── Channels/
│   │   └── FonnteChannel.php
│   └── Messages/
│       ├── RegistrationConfirmedMessage.php
│       ├── PaymentReceivedMessage.php
│       └── PaymentVerifiedMessage.php
│
├── Policies/                     # Authorization policies
│   ├── RegistrationPolicy.php
│   └── PaymentPolicy.php
│
└── Enums/                        # Enums for type safety
    ├── RegistrationType.php
    ├── PaymentStatus.php
    ├── Gender.php
    └── JerseySize.php
```

---

## Key Design Patterns

### 1. Action Pattern
Single-purpose classes untuk operasi spesifik:
```php
class GenerateRegistrationNumberAction
{
    public function execute(RaceCategory $category): string
    {
        // Generate unique registration number
    }
}
```

### 2. Service Pattern
Business logic layer yang mengkoordinasikan actions:
```php
class RegistrationService
{
    public function __construct(
        private GenerateRegistrationNumberAction $generateNumber,
        private ValidateRegistrationAction $validate,
    ) {}
    
    public function createRegistration(array $data): Registration
    {
        // Coordinate multiple actions
    }
}
```

### 3. Event-Driven Architecture
Decoupled notification system:
```php
// Event
class RegistrationCreated
{
    public function __construct(public Registration $registration) {}
}

// Listener
class SendRegistrationConfirmation
{
    public function handle(RegistrationCreated $event): void
    {
        // Send notifications
    }
}
```

### 4. Repository Pattern (Optional)
Untuk complex queries:
```php
class RegistrationRepository
{
    public function findExpiredUnpaid(): Collection
    {
        // Complex query logic
    }
}
```

### 5. Strategy Pattern
Untuk payment methods atau notification channels:
```php
interface NotificationChannel
{
    public function send(Notifiable $notifiable, Notification $notification): void;
}

class FonnteChannel implements NotificationChannel { }
class EmailChannel implements NotificationChannel { }
```

---

## Queue Architecture

### Queue Configuration

```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],
```

### Queue Structure

```
┌─────────────────────────────────────┐
│         Queue: high                  │  (Critical operations)
│  - Payment verification              │
│  - BIB generation                    │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│       Queue: default                 │  (Standard operations)
│  - Registration processing           │
│  - E-ticket generation               │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│    Queue: notifications              │  (Throttled operations)
│  - WhatsApp sending                  │
│  - Email sending                     │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│         Queue: low                   │  (Cleanup operations)
│  - Expire old registrations          │
│  - Log cleanup                       │
└─────────────────────────────────────┘
```

### Horizon Configuration

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-high' => [
            'connection' => 'redis',
            'queue' => ['high'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
        ],
        'supervisor-default' => [
            'connection' => 'redis',
            'queue' => ['default'],
            'balance' => 'auto',
            'processes' => 5,
            'tries' => 3,
        ],
        'supervisor-notifications' => [
            'connection' => 'redis',
            'queue' => ['notifications'],
            'balance' => 'simple',
            'processes' => 2,
            'tries' => 5,
            'timeout' => 300,
        ],
    ],
],
```

---

## Caching Strategy

### Cache Layers

1. **Configuration Cache**
   - Event settings
   - Payment settings
   - Notification settings
   - TTL: Until updated

2. **Query Cache**
   - Race categories list
   - Available slots per category
   - TTL: 5 minutes

3. **Session Cache**
   - User registration draft
   - Form state
   - TTL: 1 hour

### Cache Implementation

```php
// Cache race categories dengan available slots
Cache::remember('race_categories_with_slots', 300, function () {
    return RaceCategory::query()
        ->with('registrations')
        ->get()
        ->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'available_slots' => $cat->quota - $cat->registrations_count,
        ]);
});
```

---

## Security Considerations

### 1. Database Level
- Foreign key constraints
- Unique constraints untuk registration & BIB numbers
- Database transactions untuk critical operations
- Row-level locking untuk race conditions

### 2. Application Level
- Form Request validation
- Rate limiting pada registration endpoints
- CSRF protection
- XSS protection (Blade auto-escaping)
- SQL Injection protection (Eloquent parameterized queries)

### 3. File Upload
- File type validation (image only)
- File size limitation (max 2MB)
- Secure file storage (private disk)
- Virus scanning (optional, using ClamAV)

### 4. API Security
- API rate limiting
- Request validation
- Secure API keys storage (env file)
- HTTPS only in production

### 5. Authentication & Authorization
- Filament authentication untuk admin
- PIN-based authentication untuk check-in app
- Role-based access control (RBAC)
- Activity logging

---

## Performance Optimization

### Database Optimization
1. **Indexing Strategy**
   - Registration number (unique index)
   - BIB number (unique index)
   - Email & WhatsApp (index untuk search)
   - Status fields (composite index)
   - Foreign keys (auto-indexed)

2. **Query Optimization**
   - Eager loading relationships
   - Select only needed columns
   - Use pagination
   - Database query caching

3. **Connection Pooling**
   - Configure MySQL/PostgreSQL max connections
   - Use persistent connections

### Application Optimization
1. **Opcode Caching**
   - OPcache enabled in production
   
2. **Route Caching**
   ```bash
   php artisan route:cache
   php artisan config:cache
   php artisan view:cache
   ```

3. **Asset Optimization**
   - Vite build optimization
   - CSS/JS minification
   - Image compression
   - CDN untuk static assets

### Queue Optimization
1. **Job batching** untuk bulk operations
2. **Job chaining** untuk sequential tasks
3. **Job throttling** untuk rate-limited APIs

---

## Monitoring & Logging

### Application Monitoring
- **Laravel Horizon** - Queue monitoring
- **Laravel Telescope** (dev only) - Debug tool
- **Application logs** - Laravel Log channels

### Infrastructure Monitoring
- Server resources (CPU, Memory, Disk)
- Database performance
- Redis performance
- Queue metrics

### Business Metrics
- Registration conversion rate
- Payment verification time
- Notification delivery rate
- Error rates per endpoint

### Log Channels

```php
// config/logging.php
'channels' => [
    'registration' => [
        'driver' => 'daily',
        'path' => storage_path('logs/registration.log'),
        'level' => 'info',
        'days' => 30,
    ],
    'payment' => [
        'driver' => 'daily',
        'path' => storage_path('logs/payment.log'),
        'level' => 'info',
        'days' => 90,
    ],
    'notification' => [
        'driver' => 'daily',
        'path' => storage_path('logs/notification.log'),
        'level' => 'debug',
        'days' => 30,
    ],
],
```

---

## Scalability Considerations

### Horizontal Scaling
- Stateless application design
- Session stored in Redis/Database
- File storage di S3/object storage
- Load balancer ready

### Vertical Scaling
- Optimize database queries
- Increase server resources
- Database read replicas

### Database Scaling
- Master-slave replication
- Read replicas untuk reporting
- Partitioning untuk large tables

### Queue Scaling
- Multiple queue workers
- Separate queue servers
- Auto-scaling based on queue size

---

## Technology Trade-offs & Decisions

### Why Laravel 12?
- Latest LTS version
- Modern PHP features
- Rich ecosystem
- Strong community support
- Built-in queue, cache, events

### Why Filament v5?
- Rapid admin panel development
- Type-safe with PHP 8.2+ features
- Rich component library
- Built-in export functionality
- Excellent documentation

### Why Redis for Queue?
- Better performance than database queue
- Support untuk job priority
- Horizon dashboard integration
- Atomic operations support

### Why Livewire?
- Reactive components tanpa complex frontend framework
- Server-side rendering (SEO friendly)
- Less JavaScript complexity
- Tight Laravel integration

---

## Deployment Architecture

### Production Environment

```
┌──────────────────────────────────────────────────┐
│              Load Balancer (Nginx)               │
│                (SSL Termination)                  │
└────────────────┬─────────────────────────────────┘
                 │
         ┌───────┴────────┐
         │                │
┌────────▼───────┐ ┌─────▼──────────┐
│  App Server 1  │ │  App Server 2  │
│  (PHP-FPM)     │ │  (PHP-FPM)     │
└────────┬───────┘ └─────┬──────────┘
         │                │
         └───────┬────────┘
                 │
     ┌───────────┴────────────┐
     │                        │
┌────▼──────┐        ┌────────▼─────┐
│   MySQL   │        │     Redis    │
│ (Master)  │        │ (Cache/Queue)│
└───────────┘        └──────────────┘
```

### Queue Worker Deployment
- Separate server untuk queue workers
- Supervisor untuk process management
- Auto-restart on failure
- Multiple workers per queue

---

## Development Workflow

### Local Development
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start services
php artisan serve
npm run dev
php artisan queue:work
php artisan horizon
```

### Testing Strategy
- **Unit Tests**: Services, Actions, Models
- **Feature Tests**: Controllers, API endpoints
- **Browser Tests**: Critical user flows
- **Pest Arch Tests**: Architecture constraints

### CI/CD Pipeline
1. Code push to Git
2. Run Pint (formatting)
3. Run Pest tests
4. Build assets
5. Deploy to staging
6. Manual approval
7. Deploy to production

---

## Conclusion

Arsitektur ini dirancang untuk:
- ✅ Handle high concurrent registrations
- ✅ Scalable secara horizontal dan vertical
- ✅ Maintainable dengan separation of concerns
- ✅ Testable dengan proper layering
- ✅ Monitorable dengan logging & metrics
- ✅ Secure dengan multiple security layers
- ✅ Production-ready dengan proven tech stack
