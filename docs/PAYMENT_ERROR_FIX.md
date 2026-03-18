# Perbaikan Payment Page Error 500

## Ringkasan Masalah
Terdapat error 500 saat mengakses halaman payment (`/payment/5KP3-0011`) dengan dua error utama:
1. **View `payment.expired` tidak ditemukan** - fitur expire sudah dihapus tapi view masih direferensikan
2. **Null Error pada `isExpired()`** - `expired_at` bisa null, sehingga `isPast()` error

## Solusi yang Diterapkan

### 1. **Fix Null Error di Registration Model** ✅
**File**: `app/Models/Registration.php` (Line 97)

**Sebelum**:
```php
public function isExpired(): bool
{
    return $this->status === PaymentStatus::Expired
        || ($this->status === PaymentStatus::PendingPayment && $this->expired_at->isPast());
}
```

**Sesudah** (Menggunakan Null-Safe Operator):
```php
public function isExpired(): bool
{
    return $this->status === PaymentStatus::Expired
        || ($this->status === PaymentStatus::PendingPayment && $this->expired_at?->isPast());
}
```

**Penjelasan**: 
- Operator `?->` adalah null-safe operator PHP 8.0+
- Jika `expired_at` adalah `null`, maka method `isPast()` tidak akan dipanggil
- Ekspresi akan return `null`, yang di-cast ke `false` dalam boolean context

### 2. **Hapus Referensi View yang Tidak Ada** ✅
**File**: `app/Http/Controllers/PaymentController.php` (Lines 31-32)

**Sebelum**:
```php
public function show(Registration $registration): View|RedirectResponse
{
    // If already paid, redirect to status page
    if ($registration->isPaid()) {
        return redirect()->route('payment.status', $registration->registration_number);
    }

    // Check if expired ❌
    if ($registration->isExpired()) {
        return view('payment.expired', compact('registration')); // View tidak ada!
    }

    $paymentSettings = $this->paymentService->getPaymentSettings();

    return view('payment.show', compact('registration', 'paymentSettings'));
}
```

**Sesudah** (Dihapus karena fitur expire sudah tidak digunakan):
```php
public function show(Registration $registration): View|RedirectResponse
{
    // If already paid, redirect to status page
    if ($registration->isPaid()) {
        return redirect()->route('payment.status', $registration->registration_number);
    }

    $paymentSettings = $this->paymentService->getPaymentSettings();

    return view('payment.show', compact('registration', 'paymentSettings'));
}
```

## Files yang Dimodifikasi
1. ✅ `app/Models/Registration.php` - Fix null-safe operator
2. ✅ `app/Http/Controllers/PaymentController.php` - Hapus pengecekan expiration
3. ✅ `tests/Feature/PaymentControllerTest.php` - Test coverage untuk memverifikasi perbaikan

## Testing
Telah ditambahkan comprehensive tests di `tests/Feature/PaymentControllerTest.php` yang mencakup:
- ✅ Payment page loading dengan `expired_at = null`
- ✅ Payment page loading dengan `expired_at` di masa depan
- ✅ Redirect ke payment status ketika sudah paid
- ✅ Method `isExpired()` handle null case
- ✅ Method `isExpired()` handle status Expired

Untuk menjalankan tests:
```bash
composer run test -- --filter PaymentController
# atau
php artisan test --filter PaymentController
```

## Best Practices yang Diterapkan
1. **Null-Safe Operator** - Menggunakan PHP 8.0+ null-safe operator `?->` untuk menghindari null errors
2. **Single Responsibility** - Setiap method memiliki satu tanggung jawab yang jelas
3. **Test Coverage** - Menambahkan tests untuk memverifikasi perbaikan
4. **Clean Code** - Menghapus dead code yang tidak digunakan

## Catatan Penting
- Fitur expire masih bisa digunakan di tempat lain (seperti ExpireUnpaidRegistrationsJob atau Filament)
- Perbaikan ini hanya fokus pada PaymentController dan null handling di `isExpired()` method
- Jika ingin sepenuhnya menghapus fitur expire, pertimbangkan untuk:
  - Hapus migration `expired_at` column
  - Hapus ExpireUnpaidRegistrationsJob
  - Update semua method yang menggunakan expire logic

## Status
✅ **Semua perbaikan selesai dan siap untuk production**
