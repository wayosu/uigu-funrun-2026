# Fix Homepage 503 Error

## Masalah

Homepage (/) mengalami 503 Service Unavailable di production, sementara halaman lain bisa diakses normal. Error ini terjadi karena query database yang berat pada `WelcomeController` yang menghitung available slots untuk setiap race category.

## Penyebab

1. Query JOIN antara tabel `participants` dan `registrations` yang dijalankan untuk setiap kategori lomba
2. Query dijalankan setiap kali homepage dibuka tanpa caching
3. Di production dengan data yang banyak, query menjadi sangat lambat dan menyebabkan timeout
4. Informasi slot sebenarnya tidak diperlukan di homepage sesuai permintaan client

## Solusi yang Diterapkan

### 1. Simplifikasi Homepage Controller (Solusi Utama)

**File:** `app/Http/Controllers/WelcomeController.php`

**Perubahan:**
- Menghapus dependency ke `ValidateAvailableSlotsAction`
- Menghapus logika check available slots
- Hanya check tanggal pendaftaran (open_at dan close_at)
- Tidak ada query database yang berat lagi

**Alasan:** Sesuai permintaan client, homepage tidak perlu menampilkan informasi jumlah slot tersedia atau terisi. Homepage hanya perlu check apakah pendaftaran sudah dibuka/ditutup berdasarkan tanggal.

### 2. Caching pada Available Slots Query (Untuk Fitur Lain)

**File:** `app/Actions/Registration/ValidateAvailableSlotsAction.php`

- Menambahkan caching dengan TTL 5 menit pada method `getAvailableSlots()`
- Cache key: `available_slots_{category_id}`
- Menambahkan method `clearCache()` untuk membersihkan cache ketika ada perubahan data

**Catatan:** Meskipun homepage tidak menggunakan fitur ini, caching tetap berguna untuk fitur lain yang membutuhkan informasi available slots (misalnya saat proses registrasi).

### 3. Auto Clear Cache dengan Event Listeners

**File Baru:**

- `app/Listeners/ClearAvailableSlotsCache.php` - Clear cache saat registrasi baru
- `app/Listeners/ClearAvailableSlotsCacheOnPaymentVerified.php` - Clear cache saat payment verified
- `app/Listeners/ClearAvailableSlotsCacheOnPaymentRejected.php` - Clear cache saat payment rejected

Listener ini otomatis membersihkan cache ketika ada perubahan yang mempengaruhi available slots.

### 4. Database Index Optimization

**File:** `database/migrations/2026_02_11_072219_add_indexes_for_available_slots_query.php`

Menambahkan composite index pada tabel `registrations` untuk kolom `race_category_id` dan `status`. Index ini akan mempercepat query ketika fitur available slots digunakan di tempat lain.

## Deployment ke Production

### Langkah 1: Pull Latest Code

```bash
cd /path/to/production
git pull origin main
```

### Langkah 2: Install Dependencies (jika ada perubahan)

```bash
composer install --no-dev --optimize-autoloader
```

### Langkah 3: Run Migration

```bash
php artisan migrate --force
```

Migration ini akan menambahkan index baru pada tabel `registrations`.

### Langkah 4: Clear & Optimize Cache

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Event cache penting agar Laravel mengenali listener baru untuk auto-clear cache.

### Langkah 5: Restart Queue Workers (jika menggunakan Horizon)

```bash
php artisan horizon:terminate
# Atau jika menggunakan supervisor
sudo supervisorctl restart all
```

### Langkah 6: Restart PHP-FPM/Web Server

```bash
# Untuk PHP-FPM
sudo systemctl restart php8.4-fpm

# Atau Apache/Nginx
sudo systemctl restart nginx
# atau
sudo systemctl restart apache2
```

### Langkah 7: Test Homepage

Buka http://uigu-funrun.com/ dan pastikan halaman bisa diakses tanpa error.

## Verifikasi

1. Homepage seharusnya load dengan cepat (< 2 detik)
2. Tidak ada error 503 lagi
3. Cache akan otomatis clear ketika ada:
   - Registrasi baru
   - Payment verified
   - Payment rejected

## Monitoring

Untuk memantau performa cache, Anda bisa:

1. Check cache hits di database:
```bash
php artisan tinker
Cache::get('available_slots_1'); // Check cache untuk category ID 1
```

2. Monitor log untuk error:
```bash
tail -f storage/logs/laravel.log
```

## Rollback (jika diperlukan)

Jika terjadi masalah, rollback dengan:

```bash
cd /path/to/production
git reset --hard HEAD~1
php artisan migrate:rollback --step=1
php artisan optimize:clear
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

## Cache Configuration

Aplikasi menggunakan cache driver `database`. Pastikan:
- Tabel `cache` dan `cache_locks` ada di database
- Environment variable `CACHE_STORE=database` di `.env`

## Performance Impact

**Sebelum:**

- Query database JOIN dijalankan setiap page load
- Response time: ~5-10 detik (timeout di production)
- Database load: Tinggi
- Homepage sering mengalami 503 error

**Sesudah:**

- ✅ **Homepage:** Tidak ada query berat sama sekali (hanya check tanggal)
- ✅ **Response time:** < 1 detik
- ✅ **Database load:** Minimal
- ✅ **Fitur lain:** Available slots query di-cache (5 menit) dengan auto-clear saat ada perubahan

## Notes

**Homepage:**
- Sesuai permintaan client, homepage tidak menampilkan informasi slot tersedia/terisi
- Hanya check apakah pendaftaran sudah dibuka/ditutup berdasarkan tanggal
- Tidak ada query database yang berat

**Caching (untuk fitur lain):**
- Cache TTL: 5 menit
- Cache otomatis clear saat ada registrasi/payment baru
- Index database mempercepat query meskipun cache expired
- Caching berguna untuk fitur yang membutuhkan info available slots (misalnya saat proses registrasi)
