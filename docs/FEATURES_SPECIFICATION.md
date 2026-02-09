# Features Specification — Fun Run Event Registration

## Overview

Dokumen ini menjelaskan secara detail setiap fitur dalam sistem, termasuk use cases, business rules, validation rules, dan flow diagram.

---

## Table of Contents

1. [Event Management](#1-event-management)
2. [Race Category Management](#2-race-category-management)
3. [Public Registration](#3-public-registration)
4. [Payment System](#4-payment-system)
5. [BIB Number Generation](#5-bib-number-generation)
6. [E-Ticket System](#6-e-ticket-system)
7. [Check-In System](#7-check-in-system)
8. [Notification System](#8-notification-system)
9. [Auto Expire System](#9-auto-expire-system)
10. [Admin Dashboard](#10-admin-dashboard)
11. [Reporting & Export](#11-reporting--export)
12. [Settings Management](#12-settings-management)

---

## 1. Event Management

### Feature Description
Admin dapat mengelola informasi event fun run/marathon.

### User Roles
- **Admin**: Full access (CRUD)

### Use Cases

#### UC-1.1: Create Event
**Actor**: Admin  
**Precondition**: User logged in sebagai admin  
**Flow**:
1. Admin mengakses menu "Events"
2. Klik "New Event"
3. Mengisi form:
   - Nama event (required)
   - Slug (auto-generated, editable)
   - Deskripsi (optional)
   - Tanggal event (required, future date)
   - Lokasi (required)
   - Upload logo (optional, max 2MB, image only)
   - Upload banner (optional, max 5MB, image only)
   - Status aktif (default: true)
4. Klik "Create"
5. System validates input
6. System saves event
7. Redirect ke event detail page

**Validation Rules**:
```php
'name' => 'required|string|max:255',
'slug' => 'required|alpha_dash|unique:events,slug',
'description' => 'nullable|string',
'event_date' => 'required|date|after:today',
'location' => 'required|string|max:255',
'logo_path' => 'nullable|image|max:2048',
'banner_path' => 'nullable|image|max:5120',
'is_active' => 'boolean',
```

**Business Rules**:
- Hanya 1 event yang bisa aktif dalam satu waktu
- Event date harus di masa depan
- Slug harus unique

#### UC-1.2: Edit Event
**Actor**: Admin  
**Flow**: Similar to Create, dengan pre-filled form

#### UC-1.3: Delete Event
**Actor**: Admin  
**Precondition**: Event belum memiliki registrasi  
**Flow**:
1. Admin klik "Delete" pada event
2. System cek apakah event punya registrasi
3. Jika ada registrasi, tampilkan error: "Cannot delete event with existing registrations"
4. Jika tidak ada, tampilkan confirmation dialog
5. Admin confirm
6. System soft delete event (jika punya data) atau hard delete (jika kosong)

**Business Rules**:
- Event dengan registrasi tidak bisa dihapus (protect data integrity)
- Event dapat di-deactivate tanpa menghapus

---

## 2. Race Category Management

### Feature Description
Admin mengelola kategori lomba dalam event (5K, 10K, Marathon, dll).

### User Roles
- **Admin**: Full access (CRUD)

### Use Cases

#### UC-2.1: Create Race Category
**Actor**: Admin  
**Precondition**: Event sudah dibuat  
**Flow**:
1. Admin mengakses event detail
2. Klik "Add Race Category"
3. Mengisi form:
   - Event (pre-selected)
   - Nama kategori (required)
   - Slug (auto-generated)
   - Deskripsi (optional)
   - Jarak (e.g., "5K", "10K")
   - Harga individual (required)
   - Harga kolektif 5 orang (optional)
   - Harga kolektif 10 orang (optional)
   - Kuota peserta (required)
   - Prefix nomor registrasi (required, unique)
   - BIB start number (required)
   - BIB end number (required, >= start)
   - Tanggal buka pendaftaran (optional)
   - Tanggal tutup pendaftaran (optional)
   - Status aktif (default: true)
4. System validates
5. System saves category
6. System creates registration_sequence record dengan last_number = 0

**Validation Rules**:
```php
'event_id' => 'required|exists:events,id',
'name' => 'required|string|max:255',
'slug' => 'required|alpha_dash|unique:race_categories,slug,NULL,id,event_id,{event_id}',
'distance' => 'nullable|string|max:50',
'price_individual' => 'required|numeric|min:0',
'price_collective_5' => 'nullable|numeric|min:0',
'price_collective_10' => 'nullable|numeric|min:0',
'quota' => 'required|integer|min:1',
'registration_prefix' => [
    'required',
    'alpha_dash',
    'max:20',
    'unique:race_categories,registration_prefix',
],
'bib_start_number' => 'required|integer|min:1',
'bib_end_number' => 'required|integer|gte:bib_start_number',
'registration_open_at' => 'nullable|date',
'registration_close_at' => 'nullable|date|after:registration_open_at',
```

**Business Rules**:
- BIB range tidak boleh overlap dengan category lain dalam event yang sama
- Prefix harus unique across all categories
- Kuota harus cukup untuk BIB range (quota >= bib_end - bib_start + 1)
- Jika price_collective tidak diisi, tipe kolektif tidak bisa dipilih saat registrasi

**Custom Validation**:
```php
// Validate BIB range tidak overlap
$overlapping = RaceCategory::query()
    ->where('event_id', $eventId)
    ->where('id', '!=', $this->id)
    ->where(function ($query) use ($bibStart, $bibEnd) {
        $query->whereBetween('bib_start_number', [$bibStart, $bibEnd])
            ->orWhereBetween('bib_end_number', [$bibStart, $bibEnd])
            ->orWhere(function ($q) use ($bibStart, $bibEnd) {
                $q->where('bib_start_number', '<=', $bibStart)
                  ->where('bib_end_number', '>=', $bibEnd);
            });
    })
    ->exists();

if ($overlapping) {
    throw ValidationException::withMessages([
        'bib_start_number' => 'BIB range overlaps with another category',
    ]);
}
```

#### UC-2.2: Check Available Slots
**Actor**: System / Public User  
**Flow**:
1. User membuka halaman registrasi
2. System query race categories dengan count registrations
3. Calculate available slots per category:
   ```php
   $availableSlots = $category->quota - $category->confirmed_registrations_count;
   ```
4. Display categories dengan status:
   - "Available" jika slots > 10
   - "Limited" jika slots <= 10
   - "Full" jika slots = 0

**Business Rules**:
- Hanya hitung registrations dengan status: `payment_verified`
- Registrations dengan status `expired` atau `cancelled` tidak dihitung
- Real-time check saat submit registration

---

## 3. Public Registration

### Feature Description
User publik dapat mendaftar event melalui website.

### User Roles
- **Public User**: Dapat register tanpa login

### Use Cases

#### UC-3.1: View Registration Page
**Actor**: Public User  
**Flow**:
1. User akses URL: `/register` atau `/events/{slug}/register`
2. System query active event dengan race categories
3. Display:
   - Event information
   - Race categories list dengan:
     - Nama & jarak
     - Harga per tipe
     - Available slots
     - Status (Open/Full/Closed)
4. User pilih category
5. System tampilkan form registration

#### UC-3.2: Individual Registration
**Actor**: Public User  
**Precondition**: Category available & registration open  
**Flow**:
1. User pilih "Individual Registration"
2. System tampilkan form dengan fields:
   - Full Name (required)
   - Email (required, email format)
   - WhatsApp Number (required, 10-15 digits)
   - Gender (required, radio: Male/Female)
   - Date of Birth (required, datepicker, must be > 5 years old)
   - Jersey Size (required, select: XS-XXXL)
   - Identity Number / NIK (optional)
   - Emergency Contact Name (required)
   - Emergency Contact Phone (required)
   - Terms & Conditions (required, checkbox)
3. User mengisi form
4. User klik "Submit Registration"
5. System validates all fields
6. System checks available slots (dengan locking)
7. System starts DB transaction:
   - Generate unique registration number
   - Create registration record
   - Create participant record
   - Set expired_at = now + 24 hours
   - Commit transaction
8. System dispatch events:
   - `RegistrationCreated` event
9. System redirect ke payment page
10. Background: Send welcome notification (WhatsApp + Email)

**Validation Rules**:
```php
'full_name' => 'required|string|max:255',
'email' => 'required|email|max:255',
'whatsapp' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
'gender' => 'required|in:male,female',
'date_of_birth' => 'required|date|before:-5 years',
'jersey_size' => 'required|in:xs,s,m,l,xl,xxl,xxxl',
'identity_number' => 'nullable|string|max:50',
'emergency_contact_name' => 'required|string|max:255',
'emergency_contact_phone' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/'],
'terms_accepted' => 'required|accepted',
```

**Business Logic (Service)**:
```php
class RegistrationService
{
    public function createIndividualRegistration(
        RaceCategory $category,
        array $participantData
    ): Registration {
        return DB::transaction(function () use ($category, $participantData) {
            // 1. Lock & check slots
            $this->checkAvailableSlots($category, 1);
            
            // 2. Generate registration number
            $registrationNumber = $this->generateRegistrationNumber($category);
            
            // 3. Create registration
            $registration = Registration::create([
                'event_id' => $category->event_id,
                'race_category_id' => $category->id,
                'registration_number' => $registrationNumber,
                'registration_type' => RegistrationType::Individual,
                'total_participants' => 1,
                'total_amount' => $category->price_individual,
                'status' => PaymentStatus::PendingPayment,
                'expired_at' => now()->addHours(
                    PaymentSetting::first()->payment_deadline_hours
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
    
    private function checkAvailableSlots(RaceCategory $category, int $needed): void
    {
        $confirmed = $category->registrations()
            ->where('status', PaymentStatus::PaymentVerified)
            ->sum('total_participants');
        
        $available = $category->quota - $confirmed;
        
        if ($available < $needed) {
            throw new InsufficientSlotsException(
                "Only {$available} slots available, but {$needed} needed"
            );
        }
    }
}
```

#### UC-3.3: Collective Registration (5 People)
**Actor**: Public User (as PIC)  
**Flow**:
1. User pilih "Collective Registration (5 People)"
2. System tampilkan form dengan:
   - **PIC Data Section** (same fields as individual)
   - **Team Member 1-4 Sections** (each has same fields except emergency contact)
3. User mengisi semua data
4. System validates all 5 participants
5. System checks available slots (need 5 slots)
6. System creates:
   - 1 registration record (registration_type = collective_5)
   - 5 participant records (1 dengan is_pic = true)
7. Calculate total_amount = category.price_collective_5
8. Rest same as individual

**Validation Rules**:
```php
'pic' => 'required|array',
'pic.full_name' => 'required|string|max:255',
// ... (same as individual)

'members' => 'required|array|size:4',
'members.*.full_name' => 'required|string|max:255',
'members.*.email' => 'required|email|max:255|distinct',
'members.*.whatsapp' => ['required', 'regex:/^(\+?62|0)[0-9]{9,13}$/', 'distinct'],
// ... (same fields except emergency contact)

// Global validation
'members.*.email' => [
    'distinct', // Unique within members array
    function ($attribute, $value, $fail) {
        // Check against PIC email
        if ($value === request('pic.email')) {
            $fail('Member email must be different from PIC');
        }
    },
],
```

**Business Rules**:
- PIC dan members tidak boleh punya email/whatsapp yang sama
- Jika 1 validation gagal, semua gagal (atomic)
- Semua 5 participant dapet registration_number yang sama
- Semua 5 participant dapet BIB number yang berbeda (saat payment verified)

#### UC-3.4: Collective Registration (10 People)
**Flow**: Same as UC-3.3, tapi dengan 9 members + 1 PIC

---

## 4. Payment System

### Feature Description
Manual payment dengan upload bukti transfer, verifikasi admin.

### User Roles
- **Public User**: Upload bukti bayar
- **Admin**: Verify payment

### Use Cases

#### UC-4.1: View Payment Page
**Actor**: Public User  
**Precondition**: Baru selesai registrasi  
**Flow**:
1. System redirect ke `/payment/{registrationNumber}`
2. System query registration dengan participants
3. Display:
   - Registration details
   - Participant names
   - Total amount
   - Payment deadline countdown (24 jam dari created_at)
   - Payment methods:
     - QRIS image (dari payment_settings)
     - Bank transfer details (dari payment_settings)
   - Payment instructions
   - Upload form
4. User download/screenshot payment details

**Page Components**:
```blade
<div class="payment-info">
    <h2>Registration: {{ $registration->registration_number }}</h2>
    <p>Category: {{ $registration->raceCategory->name }}</p>
    <p>Participants: {{ $registration->total_participants }}</p>
    <h3>Total Amount: Rp {{ number_format($registration->total_amount) }}</h3>
    
    <div class="deadline-warning">
        ⚠️ Please complete payment within: <countdown to="{{ $registration->expired_at }}"/>
        <p>Your registration will be automatically cancelled if payment is not uploaded before deadline.</p>
    </div>
    
    <div class="payment-methods">
        <div class="qris">
            <img src="{{ $paymentSetting->qris_image_url }}" />
        </div>
        <div class="bank-transfer">
            <p>Bank: {{ $paymentSetting->bank_name }}</p>
            <p>Account: {{ $paymentSetting->account_number }}</p>
            <p>Name: {{ $paymentSetting->account_holder_name }}</p>
        </div>
    </div>
    
    @if(!$registration->payment)
        <form wire:submit="uploadPayment">
            <select name="payment_method" required>
                <option value="qris">QRIS</option>
                <option value="bank_transfer">Bank Transfer</option>
            </select>
            <input type="file" name="payment_proof" accept="image/*" required />
            <input type="datetime-local" name="payment_date" required />
            <textarea name="notes" placeholder="Optional notes"></textarea>
            <button type="submit">Upload Payment Proof</button>
        </form>
    @else
        <div class="status-pending">
            ✅ Payment proof uploaded. Waiting for admin verification.
        </div>
    @endif
</div>
```

#### UC-4.2: Upload Payment Proof
**Actor**: Public User  
**Precondition**: Registration belum expired  
**Flow**:
1. User mengisi form upload:
   - Payment method (select: QRIS/Bank Transfer)
   - Payment proof image (required, image only, max 2MB)
   - Payment date & time (required)
   - Notes (optional)
2. User klik "Upload"
3. System validates:
   - File type is image
   - File size <= 2MB
   - Registration belum expired
   - Belum ada payment record
4. System stores file di private storage
5. System creates payment record:
   ```php
   Payment::create([
       'registration_id' => $registration->id,
       'amount' => $registration->total_amount,
       'payment_method' => $request->payment_method,
       'payment_proof_path' => $path,
       'payment_date' => $request->payment_date,
       'notes' => $request->notes,
   ]);
   ```
6. System updates registration status → `payment_uploaded`
7. System fires `PaymentUploaded` event
8. Background: Send confirmation notification
9. System redirects ke waiting page

**Validation Rules**:
```php
'payment_method' => 'required|in:qris,bank_transfer',
'payment_proof' => 'required|image|max:2048',
'payment_date' => 'required|date|before_or_equal:now',
'notes' => 'nullable|string|max:500',
```

**Business Rules**:
- 1 registration hanya bisa upload 1 payment proof
- Jika mau upload ulang, harus reject dulu yang lama
- Tidak bisa upload setelah expired

#### UC-4.3: Admin Verify Payment
**Actor**: Admin  
**Flow**:
1. Admin akses "Payment Verification Queue"
2. System tampilkan list payments dengan status `payment_uploaded`
3. Admin klik "Review" pada 1 payment
4. System tampilkan modal dengan:
   - Registration details
   - Participant list
   - Payment proof image (zoomable)
   - Amount
   - Payment method
   - Payment date
   - Notes
   - Actions: [Approve] [Reject]
5. Admin klik "Approve"
6. System starts transaction:
   - Update payment.verified_at = now
   - Update payment.verified_by = admin.id
   - Update registration.status = payment_verified
   - Update registration.payment_verified_at = now
   - Generate BIB numbers untuk semua participants
   - Commit
7. System dispatch job: `GenerateETicketJob`
8. System fires `PaymentVerified` event
9. Background: Send success notification dengan E-Ticket

**Alternative Flow (Reject)**:
1. Admin klik "Reject"
2. System tampilkan form rejection reason (required)
3. Admin mengisi alasan, submit
4. System:
   - Delete payment record
   - Update registration.status = pending_payment
   - Send rejection notification dengan alasan
5. User bisa upload bukti baru

**Business Logic**:
```php
class PaymentService
{
    public function verifyPayment(Payment $payment, User $admin): void
    {
        DB::transaction(function () use ($payment, $admin) {
            // 1. Update payment
            $payment->update([
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);
            
            // 2. Update registration
            $registration = $payment->registration;
            $registration->update([
                'status' => PaymentStatus::PaymentVerified,
                'payment_verified_at' => now(),
                'payment_verified_by' => $admin->id,
            ]);
            
            // 3. Generate BIB numbers
            $this->bibNumberService->generateForRegistration($registration);
            
            // 4. Fire event
            event(new PaymentVerified($registration));
        });
        
        // 5. Queue E-Ticket generation
        GenerateETicketJob::dispatch($payment->registration);
    }
    
    public function rejectPayment(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $registration = $payment->registration;
            
            // Store rejection reason
            $payment->update(['rejection_reason' => $reason]);
            
            // Delete payment (allow re-upload)
            $payment->delete();
            
            // Reset registration status
            $registration->update([
                'status' => PaymentStatus::PendingPayment,
            ]);
            
            // Send notification
            // ...
        });
    }
}
```

---

## 5. BIB Number Generation

### Feature Description
Generate unique BIB number untuk setiap participant setelah payment verified.

### User Roles
- **System**: Automatic generation

### Use Cases

#### UC-5.1: Generate BIB Numbers
**Actor**: System  
**Trigger**: Payment verified  
**Precondition**: Race category punya BIB range  
**Flow**:
1. System query race category
2. Get BIB range: bib_start_number to bib_end_number
3. Query used BIB numbers dalam category tersebut
4. Untuk setiap participant dalam registration:
   - Find next available BIB in range
   - Assign BIB dengan row locking (prevent race condition)
   - Update participant.bib_number
5. Return list of generated BIBs

**Algorithm**:
```php
class BibNumberService
{
    public function generateForRegistration(Registration $registration): array
    {
        return DB::transaction(function () use ($registration) {
            $category = $registration->raceCategory;
            $participants = $registration->participants;
            
            // Get used BIBs
            $usedBibs = Participant::query()
                ->whereHas('registration', function ($q) use ($category) {
                    $q->where('race_category_id', $category->id)
                      ->where('status', PaymentStatus::PaymentVerified);
                })
                ->whereNotNull('bib_number')
                ->pluck('bib_number')
                ->toArray();
            
            // Generate available BIBs pool
            $availableBibs = range(
                $category->bib_start_number,
                $category->bib_end_number
            );
            $availableBibs = array_diff($availableBibs, $usedBibs);
            
            if (count($availableBibs) < $participants->count()) {
                throw new InsufficientBibNumbersException();
            }
            
            // Assign BIBs
            $assignedBibs = [];
            foreach ($participants as $participant) {
                $bibNumber = array_shift($availableBibs);
                
                $participant->update(['bib_number' => $bibNumber]);
                
                $assignedBibs[] = $bibNumber;
            }
            
            return $assignedBibs;
        });
    }
}
```

**Race Condition Prevention**:
```php
// Option 1: Transaction Isolation Level
DB::transaction(function () {
    // Generate BIBs
}, attempts: 3);

// Option 2: Database Lock
Participant::query()
    ->where('id', $participantId)
    ->lockForUpdate()
    ->first();

// Option 3: Optimistic Locking
try {
    $participant->update(['bib_number' => $bib]);
} catch (QueryException $e) {
    if ($e->getCode() === '23000') { // Duplicate entry
        // Retry dengan BIB lain
    }
}
```

**Business Rules**:
- BIB number unique per participant
- BIB hanya di-generate setelah payment verified
- BIB tidak berubah setelah di-assign
- Jika participant di-cancel, BIB tidak di-reuse dalam event yang sama

---

## 6. E-Ticket System

### Feature Description
Generate PDF E-Ticket yang dikirim via email dan WhatsApp setelah payment verified.

### User Roles
- **System**: Auto-generate
- **Public User**: Download/print

### Use Cases

#### UC-6.1: Generate E-Ticket
**Actor**: System  
**Trigger**: Payment verified & BIB assigned  
**Flow**:
1. Job `GenerateETicketJob` dijalankan
2. Untuk setiap participant dalam registration:
   - Generate QR code berisi: participant_id (encrypted)
   - Generate PDF dengan layout:
     - Event logo & name
     - Participant name
     - Registration number
     - BIB number (large, prominent)
     - Race category
     - QR code
     - Terms & conditions
   - Store PDF di private storage
3. Send E-Tickets via email & WhatsApp
4. Update notification logs

**PDF Layout** (using DomPDF):
```php
// app/Services/TicketService.php
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketService
{
    public function generateForParticipant(Participant $participant): string
    {
        $registration = $participant->registration;
        $event = $registration->event;
        
        // Generate QR Code
        $qrCode = QrCode::format('svg')
            ->size(200)
            ->generate(
                encrypt($participant->id)
            );
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.eticket', [
            'event' => $event,
            'registration' => $registration,
            'participant' => $participant,
            'qrCode' => $qrCode,
        ]);
        
        // Store PDF
        $filename = "ticket-{$registration->registration_number}-{$participant->bib_number}.pdf";
        $path = "tickets/{$registration->id}/{$filename}";
        
        Storage::disk('private')->put(
            $path,
            $pdf->output()
        );
        
        return $path;
    }
}
```

**E-Ticket View** (resources/views/pdf/eticket.blade.php):
```blade
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 200px; }
        .bib-number { font-size: 72px; font-weight: bold; text-align: center; }
        .participant-info { margin: 20px 0; }
        .qr-code { text-align: center; margin: 30px 0; }
    </style>
</head>
<body>
    <div class="header">
        @if($event->logo_path)
            <img src="{{ Storage::url($event->logo_path) }}" class="logo" />
        @endif
        <h1>{{ $event->name }}</h1>
        <p>{{ $event->event_date->format('d F Y') }} | {{ $event->location }}</p>
    </div>
    
    <div class="bib-number">
        BIB {{ $participant->bib_number }}
    </div>
    
    <div class="participant-info">
        <table width="100%">
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{ $participant->full_name }}</td>
            </tr>
            <tr>
                <td><strong>Registration:</strong></td>
                <td>{{ $registration->registration_number }}</td>
            </tr>
            <tr>
                <td><strong>Category:</strong></td>
                <td>{{ $registration->raceCategory->name }}</td>
            </tr>
            <tr>
                <td><strong>Gender:</strong></td>
                <td>{{ $participant->gender }}</td>
            </tr>
            <tr>
                <td><strong>Jersey Size:</strong></td>
                <td>{{ strtoupper($participant->jersey_size) }}</td>
            </tr>
        </table>
    </div>
    
    <div class="qr-code">
        {!! $qrCode !!}
        <p>Show this QR code during race pack collection</p>
    </div>
    
    <div class="terms">
        <small>
            This is your official e-ticket. Please bring a printed copy or digital version during race pack collection.
        </small>
    </div>
</body>
</html>
```

#### UC-6.2: Download E-Ticket
**Actor**: Public User  
**Flow**:
1. User menerima email/WhatsApp dengan link download
2. User klik link: `/tickets/download/{registrationNumber}/{token}`
3. System validates token
4. System streams PDF file
5. User download/save PDF

**Security**:
- Link menggunakan signed URL (expire 7 days)
- Token harus valid
- Rate limiting: max 10 downloads per hour per IP

---

## 7. Check-In System

### Feature Description
External app untuk scan QR code E-Ticket saat race pack collection.

### User Roles
- **Check-In Staff**: Scan QR code

### Use Cases

#### UC-7.1: Access Check-In App
**Actor**: Check-In Staff  
**Flow**:
1. Staff akses `/checkin`
2. System tampilkan PIN form
3. Staff enter PIN (dari checkin_settings)
4. System validates PIN
5. System create session
6. Redirect ke scanner page

**Security**:
- PIN 6 digit (configurable dari admin)
- Session expire setelah 8 jam
- Rate limiting: max 5 attempts per 15 minutes

#### UC-7.2: Scan QR Code
**Actor**: Check-In Staff  
**Precondition**: Staff sudah login dengan PIN  
**Flow**:
1. System tampilkan camera view untuk scan QR
2. Staff scan QR code dari E-Ticket participant
3. System decrypt participant_id dari QR
4. System query participant dengan registration
5. System validates:
   - Participant exists
   - Registration status = payment_verified
   - Belum pernah check-in sebelumnya
6. If valid:
   - Create checkin record
   - Display success dengan participant info
   - Auto-dismiss setelah 3 detik
7. If invalid:
   - Display error message
   - Show reason (not verified / already checked in / etc)

**Check-In View** (Livewire Component):
```php
class CheckInScanner extends Component
{
    public function scanQr(string $qrData): void
    {
        try {
            $participantId = decrypt($qrData);
            
            $participant = Participant::with('registration.raceCategory')
                ->findOrFail($participantId);
            
            // Validate
            if ($participant->registration->status !== PaymentStatus::PaymentVerified) {
                throw new \Exception('Payment not verified');
            }
            
            if ($participant->checkin) {
                throw new \Exception('Already checked in');
            }
            
            // Create check-in
            Checkin::create([
                'participant_id' => $participant->id,
                'checked_in_at' => now(),
                'checked_in_by' => session('checkin_staff_name'),
                'location' => CheckinSetting::first()->location_name,
            ]);
            
            $this->dispatch('checkin-success', [
                'participant' => $participant->toArray(),
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('checkin-error', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
```

**Success Display**:
```blade
<div class="success-modal">
    ✅ Check-In Successful
    
    <div class="participant-info">
        <p class="bib">BIB: {{ $participant->bib_number }}</p>
        <p>{{ $participant->full_name }}</p>
        <p>{{ $participant->registration->raceCategory->name }}</p>
        <p>Jersey: {{ strtoupper($participant->jersey_size) }}</p>
    </div>
</div>
```

#### UC-7.3: Manual Search & Check-In
**Actor**: Check-In Staff  
**Flow** (alternative jika QR tidak bisa di-scan):
1. Staff klik "Manual Search"
2. Staff input BIB number atau nama
3. System search participants
4. Display results
5. Staff pilih participant yang benar
6. System tampilkan confirmation
7. Staff confirm
8. System creates checkin record

---

## 8. Notification System

**[See API_NOTIFICATION_DESIGN.md for detailed specs]**

### Brief Overview

**Channels**: WhatsApp (Fonnte) + Email  
**Triggers**:
1. Registration created → Welcome message dengan payment instructions
2. Payment uploaded → Confirmation & waiting for verification
3. Payment verified → E-Ticket delivery
4. Payment rejected → Rejection reason + re-upload instructions
5. Registration expired → Expiry notice

**Anti-Spam Strategy**:
- Queue-based sending
- Random delay (5-15 seconds)
- Rate limiting (configurable per channel)
- Retry with exponential backoff
- Delivery monitoring & logging

---

## 9. Auto Expire System

### Feature Description
Automatically expire registrations yang belum upload payment dalam 24 jam.

### User Roles
- **System**: Automated job

### Use Cases

#### UC-9.1: Schedule Expiry Check
**Actor**: System  
**Trigger**: Laravel Scheduler (hourly)  
**Flow**:
1. Scheduler runs hourly
2. Dispatch job: `ExpireUnpaidRegistrationsJob`
3. Job queries registrations:
   ```php
   Registration::query()
       ->where('status', PaymentStatus::PendingPayment)
       ->where('expired_at', '<=', now())
       ->whereDoesntHave('payment')
       ->get();
   ```
4. Untuk setiap registration:
   - Update status = expired
   - Soft delete registration (preserve data for audit)
   - Fire `RegistrationExpired` event
   - Send expiry notification (optional)
5. Log results

**Scheduler Configuration**:
```php
// routes/console.php atau bootstrap/app.php
use App\Jobs\ExpireUnpaidRegistrationsJob;

Schedule::job(new ExpireUnpaidRegistrationsJob)
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
```

**Business Rules**:
- Hanya expire registration dengan status `pending_payment`
- Tidak expire jika sudah ada payment record (even if not verified)
- Soft delete untuk audit trail
- Quota dikembalikan (karena registration dianggap batal)

**Expiry Notification** (optional):
```
Subject: Registration Expired

Hi {name},

Your registration {registration_number} has expired because payment was not uploaded within 24 hours.

If you still want to participate, please register again.

Thank you!
```

---

## 10. Admin Dashboard

### Feature Description
Dashboard untuk monitoring event secara real-time.

### User Roles
- **Admin**: View all stats

### Widgets

#### Widget 1: Stats Overview
**Metrics**:
- Total Registrations (count)
- Confirmed Participants (payment_verified)
- Pending Verification (payment_uploaded)
- Total Revenue (sum of verified payments)
- Available Slots (per category)

#### Widget 2: Registration Trend Chart
**Display**: Line chart  
**X-axis**: Date  
**Y-axis**: Number of registrations  
**Period**: Last 30 days

#### Widget 3: Payment Status Breakdown
**Display**: Donut chart  
**Categories**:
- Pending Payment
- Payment Uploaded
- Payment Verified
- Expired

#### Widget 4: Race Category Distribution
**Display**: Bar chart  
**X-axis**: Category names  
**Y-axis**: Number of participants

#### Widget 5: Recent Registrations
**Display**: Table (latest 10)  
**Columns**:
- Registration Number
- Name (PIC)
- Category
- Status
- Created At

#### Widget 6: Pending Verifications
**Display**: Table with action buttons  
**Columns**:
- Registration Number
- Amount
- Uploaded At
- Actions: [View] [Approve] [Reject]

---

## 11. Reporting & Export

### Feature Description
Generate & export data untuk vendor (jersey, medal, dll).

### User Roles
- **Admin**: Export data

### Use Cases

#### UC-11.1: Financial Report
**Actor**: Admin  
**Flow**:
1. Admin akses "Reports > Financial"
2. System tampilkan form filter:
   - Date range
   - Race category
   - Payment status
3. Admin set filter, klik "Generate"
4. System displays report:
   - Total income
   - Income per category
   - Income per day
   - Payment method breakdown
5. Admin klik "Export Excel"
6. System generates Excel file dengan sheets:
   - Summary
   - Detailed Transactions
   - Per Category Breakdown

**Excel Structure**:
```
Sheet 1: Summary
- Total Registrations: 250
- Total Confirmed: 200
- Total Pending: 30
- Total Expired: 20
- Total Revenue: Rp 50,000,000

Sheet 2: Detailed Transactions
| Reg Number | Date | Category | Participants | Amount | Status | Verified Date |
|------------|------|----------|--------------|--------|--------|---------------|
| FR5K-0001  | ...  | 5K       | 1            | 150k   | Paid   | ...           |

Sheet 3: Per Category
| Category | Total Participants | Total Revenue |
|----------|--------------------|---------------|
| 5K       | 100                | 15,000,000    |
| 10K      | 80                 | 20,000,000    |
```

#### UC-11.2: Participant List Export
**Actor**: Admin  
**Flow**:
1. Admin akses "Registrations" atau "Participants"
2. Admin set filters (category, status, dll)
3. Admin klik "Export"
4. System tampilkan modal dengan options:
   - Format: Excel / CSV
   - Columns to include (checkboxes)
   - Group by: Registration / Individual Participants
5. Admin select options, confirm
6. System generates file
7. Auto-download atau kirim via email (jika large)

**Available Columns**:
- Registration Number
- BIB Number
- Full Name
- Email
- WhatsApp
- Gender
- Date of Birth
- Age (calculated)
- Jersey Size
- Category
- Registration Type
- Status
- Payment Status
- Check-In Status
- Created Date

**CSV Format** (untuk vendor jersey):
```csv
BIB Number,Name,Gender,Jersey Size,Category
1001,John Doe,Male,L,5K Fun Run
1002,Jane Smith,Female,M,5K Fun Run
```

#### UC-11.3: Check-In Report
**Actor**: Admin  
**Flow**:
1. Admin akses "Reports > Check-In"
2. System display:
   - Total checked in
   - Total not yet checked in
   - Check-in per hour (chart)
3. Admin export list of:
   - Checked in participants
   - Not yet checked in participants

---

## 12. Settings Management

### Feature Description
Admin mengelola konfigurasi sistem.

### User Roles
- **Admin**: Manage settings

### Settings Sections

#### 12.1: Payment Settings
**Fields**:
- QRIS Image (upload)
- Bank Name
- Account Number
- Account Holder Name
- Payment Instructions (WYSIWYG editor)
- Payment Deadline (hours) - default: 24

**Filament Resource**:
```php
class PaymentSettingsPage extends Page
{
    protected static string $view = 'filament.pages.payment-settings';
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $settings = PaymentSetting::first();
        $this->form->fill($settings?->toArray() ?? []);
    }
    
    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('qris_image_path')
                ->label('QRIS Image')
                ->image()
                ->maxSize(2048),
            
            TextInput::make('bank_name')
                ->label('Bank Name')
                ->required(),
            
            TextInput::make('account_number')
                ->label('Account Number')
                ->required(),
            
            TextInput::make('account_holder_name')
                ->label('Account Holder Name')
                ->required(),
            
            RichEditor::make('payment_instructions')
                ->label('Payment Instructions'),
            
            TextInput::make('payment_deadline_hours')
                ->label('Payment Deadline (Hours)')
                ->numeric()
                ->default(24)
                ->required(),
        ];
    }
    
    public function save(): void
    {
        $data = $this->form->getState();
        
        PaymentSetting::updateOrCreate(
            ['id' => 1],
            $data
        );
        
        Cache::forget('payment_settings');
        
        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }
}
```

#### 12.2: Notification Settings
**Fields**:
- **WhatsApp**:
  - Fonnte API Key
  - Fonnte Device ID
  - Enabled (toggle)
  - Delay Between Messages (seconds)
  - Max Per Minute
  - Retry Limit
- **Email**:
  - From Address
  - From Name
  - Enabled (toggle)
  - Delay Between Messages (seconds)
  - Max Per Minute
- **Templates** (tabs):
  - Registration Confirmation (WA & Email)
  - Payment Upload Confirmation (WA & Email)
  - Payment Verified (WA & Email)

**Template Variables**:
- `{name}`, `{registration_number}`, `{bib_number}`, etc.
- Live preview dengan sample data

#### 12.3: Check-In Settings
**Fields**:
- PIN Code (6 digits)
- Location Name
- Is Active (toggle)

**Validation**:
```php
'pin_code' => ['required', 'regex:/^[0-9]{6}$/'],
'location_name' => 'nullable|string|max:255',
'is_active' => 'boolean',
```

---

## Conclusion

Dokumentasi ini memberikan spesifikasi lengkap untuk setiap fitur. Setiap use case dilengkapi dengan:
- ✅ Flow yang jelas
- ✅ Validation rules
- ✅ Business logic
- ✅ Code examples
- ✅ Edge cases handling
- ✅ Security considerations

Next: Lihat **API_NOTIFICATION_DESIGN.md** untuk detail notifikasi system dan **IMPLEMENTATION_GUIDE.md** untuk step-by-step implementation.
