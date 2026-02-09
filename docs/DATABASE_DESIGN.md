# Database Design — Fun Run Event Registration

## Overview

Database dirancang dengan prinsip:
- **Normalization**: Minimal 3NF untuk menghindari data redundancy
- **Integrity**: Foreign keys, unique constraints, check constraints
- **Performance**: Strategic indexing, partitioning ready
- **Scalability**: Support untuk millions of records
- **Auditability**: Timestamps dan soft deletes

---

## Entity Relationship Diagram (ERD)

```
┌─────────────────┐
│     events      │
├─────────────────┤
│ id (PK)         │
│ name            │
│ slug            │
│ description     │
│ event_date      │
│ location        │
│ logo_path       │
│ banner_path     │
│ is_active       │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         │
┌────────▼────────────────┐
│   race_categories       │
├─────────────────────────┤
│ id (PK)                 │
│ event_id (FK)           │
│ name                    │
│ slug                    │
│ description             │
│ distance                │
│ price_individual        │
│ price_collective_5      │
│ price_collective_10     │
│ quota                   │
│ registration_prefix     │
│ bib_start_number        │
│ bib_end_number          │
│ is_active               │
│ registration_open_at    │
│ registration_close_at   │
│ created_at              │
│ updated_at              │
└────────┬────────────────┘
         │
         │ 1:N
         │
┌────────▼─────────────────┐
│    registrations         │
├──────────────────────────┤
│ id (PK)                  │
│ event_id (FK)            │
│ race_category_id (FK)    │
│ registration_number (UK) │
│ registration_type        │──┐ enum: individual, collective_5, collective_10
│ total_participants       │  │
│ total_amount             │  │
│ status                   │──┤ enum: pending_payment, payment_uploaded,
│ payment_verified_at      │  │       payment_verified, expired, cancelled
│ payment_verified_by      │  │
│ expired_at               │  │
│ notes                    │  │
│ created_at               │  │
│ updated_at               │  │
│ deleted_at               │  │
└────────┬─────────────────┘  │
         │                     │
         │ 1:N                 │
         │                     │
┌────────▼──────────────────┐ │
│     participants          │ │
├───────────────────────────┤ │
│ id (PK)                   │ │
│ registration_id (FK)      │ │
│ bib_number (UK, nullable) │ │
│ is_pic                    │ │
│ full_name                 │ │
│ email                     │ │
│ whatsapp                  │ │
│ gender                    │─┤ enum: male, female
│ date_of_birth             │ │
│ jersey_size               │─┤ enum: xs, s, m, l, xl, xxl, xxxl
│ identity_number           │ │
│ emergency_contact_name    │ │
│ emergency_contact_phone   │ │
│ created_at                │ │
│ updated_at                │ │
└────────┬──────────────────┘ │
         │                     │
         │ 1:1                 │
         │                     │
┌────────▼──────────────────┐ │
│       payments            │ │
├───────────────────────────┤ │
│ id (PK)                   │ │
│ registration_id (FK, UK)  │ │
│ amount                    │ │
│ payment_method            │─┤ enum: qris, bank_transfer
│ payment_proof_path        │ │
│ payment_date              │ │
│ notes                     │ │
│ verified_at               │ │
│ verified_by (FK)          │ │
│ rejection_reason          │ │
│ created_at                │ │
│ updated_at                │ │
└───────────────────────────┘ │
                               │
┌────────────────────────────┐│
│       checkins            │ │
├────────────────────────────┤│
│ id (PK)                    ││
│ participant_id (FK, UK)    ││
│ checked_in_at              ││
│ checked_in_by              ││
│ location                   ││
│ notes                      ││
│ created_at                 ││
│ updated_at                 ││
└────────────────────────────┘│
                               │
┌───────────────────────────┐ │
│  registration_sequences   │ │
├───────────────────────────┤ │
│ id (PK)                   │ │
│ race_category_id (FK, UK) │ │
│ last_number               │ │
│ updated_at                │ │
└───────────────────────────┘ │
                               │
┌───────────────────────────┐ │
│   notification_logs       │ │
├───────────────────────────┤ │
│ id (PK)                   │ │
│ registration_id (FK)      │ │
│ participant_id (FK)       │ │
│ channel                   │─┤ enum: whatsapp, email
│ recipient                 │ │
│ message                   │ │
│ status                    │─┤ enum: pending, sent, failed
│ sent_at                   │ │
│ failed_reason             │ │
│ retry_count               │ │
│ created_at                │ │
│ updated_at                │ │
└───────────────────────────┘ │
                               │
┌───────────────────────────┐ │
│    payment_settings       │ │
├───────────────────────────┤ │
│ id (PK)                   │ │
│ qris_image_path           │ │
│ bank_name                 │ │
│ account_number            │ │
│ account_holder_name       │ │
│ payment_instructions      │ │
│ payment_deadline_hours    │ │
│ created_at                │ │
│ updated_at                │ │
└───────────────────────────┘ │
                               │
┌─────────────────────────────┐
│   notification_settings     │
├─────────────────────────────┤
│ id (PK)                     │
│ fonnte_api_key              │
│ fonnte_device_id            │
│ whatsapp_enabled            │
│ whatsapp_delay_seconds      │
│ whatsapp_max_per_minute     │
│ whatsapp_retry_limit        │
│ email_enabled               │
│ email_delay_seconds         │
│ email_max_per_minute        │
│ email_from_address          │
│ email_from_name             │
│ registration_template_wa    │
│ registration_template_email │
│ payment_upload_template_wa  │
│ payment_upload_template_email│
│ payment_verified_template_wa │
│ payment_verified_template_email│
│ created_at                  │
│ updated_at                  │
└─────────────────────────────┘

┌───────────────────────────┐
│    checkin_settings       │
├───────────────────────────┤
│ id (PK)                   │
│ pin_code                  │
│ location_name             │
│ is_active                 │
│ created_at                │
│ updated_at                │
└───────────────────────────┘

┌───────────────────────────┐
│         users             │
├───────────────────────────┤
│ id (PK)                   │
│ name                      │
│ email (UK)                │
│ password                  │
│ remember_token            │
│ created_at                │
│ updated_at                │
└───────────────────────────┘
```

---

## Table Definitions

### 1. `events`

Menyimpan informasi event utama.

```sql
CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    event_date DATE NOT NULL,
    location VARCHAR(255) NOT NULL,
    logo_path VARCHAR(255),
    banner_path VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_event_date (event_date),
    INDEX idx_is_active (is_active),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules:**
- `name`: required, max:255
- `slug`: required, unique, alpha_dash
- `event_date`: required, date, after:today
- `location`: required, max:255

---

### 2. `race_categories`

Kategori lomba dalam event (5K, 10K, Marathon, dll).

```sql
CREATE TABLE race_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    distance VARCHAR(50),
    price_individual DECIMAL(12, 2) NOT NULL,
    price_collective_5 DECIMAL(12, 2),
    price_collective_10 DECIMAL(12, 2),
    quota INT UNSIGNED NOT NULL,
    registration_prefix VARCHAR(20) NOT NULL,
    bib_start_number INT UNSIGNED NOT NULL,
    bib_end_number INT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT true,
    registration_open_at TIMESTAMP NULL,
    registration_close_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY uk_event_slug (event_id, slug),
    INDEX idx_event_active (event_id, is_active),
    INDEX idx_registration_prefix (registration_prefix),
    
    CHECK (price_individual >= 0),
    CHECK (price_collective_5 >= 0 OR price_collective_5 IS NULL),
    CHECK (price_collective_10 >= 0 OR price_collective_10 IS NULL),
    CHECK (quota > 0),
    CHECK (bib_end_number >= bib_start_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules:**
- `registration_prefix`: required, max:20, alpha_dash
- `quota`: required, integer, min:1
- `bib_start_number`: required, integer, min:1
- `bib_end_number`: required, integer, gte:bib_start_number
- BIB range tidak boleh overlap dengan category lain

---

### 3. `registrations`

Data pendaftaran peserta (bisa individual atau kolektif).

```sql
CREATE TABLE registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    race_category_id BIGINT UNSIGNED NOT NULL,
    registration_number VARCHAR(50) NOT NULL UNIQUE,
    registration_type ENUM('individual', 'collective_5', 'collective_10') NOT NULL,
    total_participants TINYINT UNSIGNED NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending_payment', 'payment_uploaded', 'payment_verified', 'expired', 'cancelled') 
        DEFAULT 'pending_payment',
    payment_verified_at TIMESTAMP NULL,
    payment_verified_by BIGINT UNSIGNED NULL,
    expired_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (race_category_id) REFERENCES race_categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (payment_verified_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY uk_registration_number (registration_number),
    INDEX idx_status (status),
    INDEX idx_category_status (race_category_id, status),
    INDEX idx_expired_at (expired_at),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at),
    
    CHECK (total_participants > 0),
    CHECK (total_amount >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status Flow:**
1. `pending_payment` → Baru dibuat, belum upload bukti
2. `payment_uploaded` → Sudah upload bukti, menunggu verifikasi
3. `payment_verified` → Pembayaran diverifikasi admin, BIB generated
4. `expired` → Melewati deadline tanpa upload bukti
5. `cancelled` → Dibatalkan oleh admin

**Business Rules:**
- `expired_at` = `created_at` + 24 jam (configurable)
- `registration_number` format: `{prefix}-{sequence}`
- `total_participants`: 1 untuk individual, 5 atau 10 untuk kolektif
- `total_amount` dihitung dari race category price × participants

---

### 4. `participants`

Data peserta individual (1 row per peserta).

```sql
CREATE TABLE participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id BIGINT UNSIGNED NOT NULL,
    bib_number INT UNSIGNED NULL UNIQUE,
    is_pic BOOLEAN DEFAULT false,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    whatsapp VARCHAR(20) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    date_of_birth DATE NOT NULL,
    jersey_size ENUM('xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl') NOT NULL,
    identity_number VARCHAR(50),
    emergency_contact_name VARCHAR(255) NOT NULL,
    emergency_contact_phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_bib_number (bib_number),
    INDEX idx_registration (registration_id),
    INDEX idx_email (email),
    INDEX idx_whatsapp (whatsapp),
    INDEX idx_full_name (full_name),
    INDEX idx_is_pic (is_pic)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Validation Rules:**
- `email`: required, email
- `whatsapp`: required, regex:/^[0-9]{10,15}$/
- `date_of_birth`: required, date, before:today
- `identity_number`: nullable, max:50
- Setiap registration harus punya minimal 1 participant dengan `is_pic = true`

---

### 5. `payments`

Bukti pembayaran dan verifikasi.

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id BIGINT UNSIGNED NOT NULL UNIQUE,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method ENUM('qris', 'bank_transfer') NOT NULL,
    payment_proof_path VARCHAR(255) NOT NULL,
    payment_date DATETIME NOT NULL,
    notes TEXT,
    verified_at TIMESTAMP NULL,
    verified_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY uk_registration_payment (registration_id),
    INDEX idx_verified_at (verified_at),
    INDEX idx_payment_date (payment_date),
    
    CHECK (amount > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- 1 registration hanya bisa punya 1 payment record
- `amount` harus sama dengan `registrations.total_amount`
- `payment_proof_path` di-store di private storage
- `verified_at` NULL = belum diverifikasi

---

### 6. `checkins`

Log check-in saat ambil race pack.

```sql
CREATE TABLE checkins (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    participant_id BIGINT UNSIGNED NOT NULL UNIQUE,
    checked_in_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    checked_in_by VARCHAR(255),
    location VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_participant_checkin (participant_id),
    INDEX idx_checked_in_at (checked_in_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- 1 participant hanya bisa check-in 1 kali
- Check-in hanya bisa dilakukan untuk registration dengan status `payment_verified`
- QR code di E-Ticket berisi: `participant_id`

---

### 7. `registration_sequences`

Counter untuk generate registration number (thread-safe).

```sql
CREATE TABLE registration_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    race_category_id BIGINT UNSIGNED NOT NULL UNIQUE,
    last_number INT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (race_category_id) REFERENCES race_categories(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_race_category (race_category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Usage Pattern:**
```php
// Atomic increment dengan row locking
DB::transaction(function () use ($categoryId) {
    $sequence = RegistrationSequence::query()
        ->where('race_category_id', $categoryId)
        ->lockForUpdate()
        ->first();
    
    $sequence->increment('last_number');
    
    return $sequence->last_number;
});
```

---

### 8. `notification_logs`

Log semua notifikasi yang dikirim.

```sql
CREATE TABLE notification_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    registration_id BIGINT UNSIGNED NULL,
    participant_id BIGINT UNSIGNED NULL,
    channel ENUM('whatsapp', 'email') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    failed_reason TEXT,
    retry_count TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    
    INDEX idx_registration (registration_id),
    INDEX idx_participant (participant_id),
    INDEX idx_channel_status (channel, status),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Business Rules:**
- Setiap attempt pengiriman dicatat
- `retry_count` max 3 (configurable)
- Failed notifications dapat di-retry manual dari admin panel

---

### 9. `payment_settings`

Single-row configuration table untuk payment.

```sql
CREATE TABLE payment_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    qris_image_path VARCHAR(255),
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    account_holder_name VARCHAR(255),
    payment_instructions TEXT,
    payment_deadline_hours INT UNSIGNED DEFAULT 24,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Usage:**
- Single row table (enforce via application logic)
- Cached untuk performance
- Diakses via `PaymentSetting::first()` atau Cache

---

### 10. `notification_settings`

Single-row configuration table untuk notification.

```sql
CREATE TABLE notification_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fonnte_api_key VARCHAR(255),
    fonnte_device_id VARCHAR(255),
    whatsapp_enabled BOOLEAN DEFAULT true,
    whatsapp_delay_seconds INT UNSIGNED DEFAULT 10,
    whatsapp_max_per_minute INT UNSIGNED DEFAULT 10,
    whatsapp_retry_limit TINYINT UNSIGNED DEFAULT 3,
    email_enabled BOOLEAN DEFAULT true,
    email_delay_seconds INT UNSIGNED DEFAULT 5,
    email_max_per_minute INT UNSIGNED DEFAULT 20,
    email_from_address VARCHAR(255),
    email_from_name VARCHAR(255),
    registration_template_wa TEXT,
    registration_template_email TEXT,
    payment_upload_template_wa TEXT,
    payment_upload_template_email TEXT,
    payment_verified_template_wa TEXT,
    payment_verified_template_email TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Template Variables:**
- `{name}` - Participant name
- `{registration_number}` - Registration number
- `{bib_number}` - BIB number
- `{event_name}` - Event name
- `{category_name}` - Race category
- `{amount}` - Payment amount
- `{payment_deadline}` - Payment deadline

---

### 11. `checkin_settings`

Configuration untuk check-in app.

```sql
CREATE TABLE checkin_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pin_code VARCHAR(6) NOT NULL,
    location_name VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### 12. `users`

Admin users untuk Filament panel.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY uk_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Indexing Strategy

### Primary Indexes (Already Defined)
- All `id` columns (AUTO_INCREMENT PRIMARY KEY)
- All `UNIQUE` constraints

### Additional Indexes

#### Performance Indexes
```sql
-- Race category filtering
CREATE INDEX idx_race_categories_active ON race_categories(is_active, event_id);

-- Registration searching & filtering
CREATE INDEX idx_registrations_search ON registrations(registration_number, status);
CREATE INDEX idx_registrations_category_date ON registrations(race_category_id, created_at);

-- Participant searching
CREATE INDEX idx_participants_search ON participants(full_name, email, whatsapp);
CREATE INDEX idx_participants_bib ON participants(bib_number) WHERE bib_number IS NOT NULL;

-- Payment verification queue
CREATE INDEX idx_payments_pending ON payments(verified_at, created_at) WHERE verified_at IS NULL;

-- Check-in lookup
CREATE INDEX idx_checkins_lookup ON checkins(checked_in_at, location);

-- Notification monitoring
CREATE INDEX idx_notifications_failed ON notification_logs(status, retry_count, created_at) 
    WHERE status = 'failed';
```

#### Composite Indexes for Complex Queries
```sql
-- Dashboard statistics
CREATE INDEX idx_reg_stats ON registrations(race_category_id, status, created_at);

-- Financial reports
CREATE INDEX idx_payment_reports ON payments(verified_at, payment_method, amount);

-- Notification delivery monitoring
CREATE INDEX idx_notif_delivery ON notification_logs(channel, status, sent_at, retry_count);
```

---

## Data Integrity Constraints

### Foreign Key Constraints
- `ON DELETE CASCADE`: Jika parent dihapus, child ikut terhapus
  - events → race_categories
  - race_categories → registration_sequences
  - registrations → participants, payments, notification_logs
  - participants → checkins

- `ON DELETE RESTRICT`: Prevent delete jika ada child data
  - registrations → race_category (tidak boleh hapus category yang sudah ada registrasinya)

- `ON DELETE SET NULL`: Set null jika parent dihapus
  - users → registrations.payment_verified_by
  - users → payments.verified_by

### Unique Constraints
- `registrations.registration_number` (UNIQUE)
- `participants.bib_number` (UNIQUE, nullable)
- `payments.registration_id` (UNIQUE) - 1:1 relationship
- `checkins.participant_id` (UNIQUE) - 1:1 relationship
- `race_categories.(event_id, slug)` (UNIQUE composite)

### Check Constraints
- Price values >= 0
- Quota > 0
- BIB end number >= start number
- Total participants > 0
- Total amount >= 0
- Payment amount > 0

---

## Database Seeding Strategy

### Development Seeding

```php
// DatabaseSeeder.php
public function run(): void
{
    // 1. Create admin user
    $admin = User::factory()->create([
        'email' => 'admin@funrun.test',
        'name' => 'Admin User',
    ]);
    
    // 2. Create settings
    PaymentSetting::create([...]);
    NotificationSetting::create([...]);
    CheckinSetting::create([...]);
    
    // 3. Create event
    $event = Event::factory()->create([
        'name' => 'Jakarta Fun Run 2026',
        'event_date' => now()->addMonths(3),
    ]);
    
    // 4. Create race categories
    $categories = [
        ['name' => '5K Fun Run', 'prefix' => 'FR5K', 'bib_start' => 1000, 'bib_end' => 1999],
        ['name' => '10K Run', 'prefix' => 'R10K', 'bib_start' => 2000, 'bib_end' => 2999],
        ['name' => 'Half Marathon', 'prefix' => 'HM21', 'bib_start' => 3000, 'bib_end' => 3499],
    ];
    
    foreach ($categories as $cat) {
        $category = RaceCategory::factory()->create([
            'event_id' => $event->id,
            'name' => $cat['name'],
            'registration_prefix' => $cat['prefix'],
            'bib_start_number' => $cat['bib_start'],
            'bib_end_number' => $cat['bib_end'],
        ]);
        
        // Create sequence tracker
        RegistrationSequence::create([
            'race_category_id' => $category->id,
            'last_number' => 0,
        ]);
    }
    
    // 5. Create sample registrations (optional for testing)
    if (app()->environment('local')) {
        // Create 50 sample registrations with various statuses
        Registration::factory()
            ->count(50)
            ->create();
    }
}
```

---

## Migration Plan

### Migration Order

1. **Core Tables** (no dependencies)
   - `users`
   - `events`
   - `payment_settings`
   - `notification_settings`
   - `checkin_settings`

2. **Event Structure**
   - `race_categories`
   - `registration_sequences`

3. **Registration Flow**
   - `registrations`
   - `participants`
   - `payments`

4. **Operational Tables**
   - `checkins`
   - `notification_logs`

### Sample Migration

```php
// 2024_01_01_000001_create_events_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->string('location');
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('event_date');
            $table->index('is_active');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
```

---

## Partitioning Strategy (Future Scaling)

Jika data sudah sangat besar (millions of records), pertimbangkan:

### Time-based Partitioning

```sql
-- Partition registrations by month
ALTER TABLE registrations
PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
    PARTITION p202601 VALUES LESS THAN (202602),
    PARTITION p202602 VALUES LESS THAN (202603),
    PARTITION p202603 VALUES LESS THAN (202604),
    ...
);
```

### Benefits
- Faster queries dengan partition pruning
- Easy archival (drop old partitions)
- Better index management

---

## Backup & Recovery Strategy

### Backup Schedule
- **Full backup**: Daily at 2 AM
- **Incremental backup**: Every 6 hours
- **Binlog backup**: Real-time (for point-in-time recovery)

### Retention Policy
- Daily backups: 30 days
- Weekly backups: 12 weeks
- Monthly backups: 12 months

### Critical Tables (Priority Backup)
1. `registrations`
2. `participants`
3. `payments`
4. `checkins`

---

## Performance Benchmarks

### Expected Query Performance

| Query Type | Target | Notes |
|------------|--------|-------|
| Registration insert | < 100ms | With transaction |
| Registration search | < 50ms | With proper index |
| Payment verification | < 200ms | Includes BIB generation |
| Dashboard stats | < 500ms | With caching |
| Participant list | < 100ms | Paginated |
| Export CSV | < 5s | For 1000 records |

---

## Database Monitoring

### Key Metrics to Monitor

1. **Query Performance**
   - Slow query log (> 1 second)
   - Query execution plan analysis
   - Index usage statistics

2. **Connection Metrics**
   - Active connections
   - Connection pool utilization
   - Connection errors

3. **Storage Metrics**
   - Table sizes
   - Index sizes
   - Disk usage growth rate

4. **Lock Metrics**
   - Lock wait time
   - Deadlock occurrences
   - Row lock contention

---

## Conclusion

Database design ini memenuhi:
- ✅ **Scalability**: Ready untuk jutaan records
- ✅ **Integrity**: Foreign keys, constraints, transactions
- ✅ **Performance**: Strategic indexing, query optimization
- ✅ **Maintainability**: Normalized structure, clear relationships
- ✅ **Auditability**: Timestamps, soft deletes, logs
- ✅ **Flexibility**: Easy to extend dengan additional features
