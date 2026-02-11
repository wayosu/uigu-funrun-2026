# Panduan Deployment ke Rumahweb (Unlimited Hosting)

Panduan ini dikhususkan untuk deployment aplikasi Laravel UIGU Fun Run ke layanan **Shared Hosting Rumahweb**.

## Prasyarat

Sebelum memulai, pastikan:
1.  Anda memiliki akses ke **cPanel** Rumahweb.
2.  Anda memiliki akses **SSH** (Terminal) - *Sangat disarankan untuk memintanya ke CS Rumahweb jika belum aktif*.
3.  Project sudah berjalan lancar di local.

---

## Tahap 1: Persiapan Aplikasi (di Local)

Karena keterbatasan server shared hosting (node.js mungkin tidak ada), kita build aset di local.

1.  **Bersihkan Cache config local:**
    ```bash
    php artisan optimize:clear
    ```

2.  **Build Frontend Assets:**
    Jalankan perintah ini untuk mengompilasi Tailwind & JS untuk production.
    ```bash
    npm run build
    ```

3.  **Install Dependencies untuk Production:**
    Hapus dev dependencies agar ukuran file lebih kecil.
    ```bash
    composer install --optimize-autoloader --no-dev
    ```

4.  **Hapus Folder yang Tidak Perlu:**
    Hapus folder `node_modules`. Folder `vendor` **JANGAN** dihapus karena kita butuh library PHP-nya.
    
5.  **Compress Project:**
    Zip semua file project **kecuali**:
    - `.git` (folder hidden)
    - `.env` (kita buat baru di server nanti)
    - `node_modules`
    - `tests`

    *Tips: Anda bisa men-zip isi foldernya saja, bukan folder utamanya.*

---

## Tahap 2: Setup cPanel Rumahweb

### 1. Setting Versi PHP
Menu: **Select PHP Version**
- Pilih **PHP 8.2** atau **8.3** (sesuai kebutuhan `composer.json` minimal 8.2).
- Pastikan tab **Extensions** diaktifkan untuk: `fileinfo`, `pdo_mysql`, `mbstring`, `openssl`, `intl`, `gd`, `exif`.

### 2. Membuat Database
Menu: **MySQL® Database Wizard**
1.  Buat Database baru (misal: `uigu_db`).
2.  Buat User baru (misal: `uigu_user`).
3.  **PENTING:** Berikan user tersebut hak akses **ALL PRIVILEGES** ke database tadi.
4.  Catat Password, Nama Database, dan User Database.

---

## Tahap 3: Upload File

Menu: **File Manager**

Untuk keamanan terbaik, kita **TIDAK** menaruh kode aplikasi langsung di dalam `public_html`.

1.  Di level paling atas (sejajar dengan `public_html`), buat folder baru, misalnya `uigu-app`.
2.  Upload file `.zip` projek anda ke dalam folder `uigu-app`.
3.  Extract file tersebut.
4.  Struktur direktori akan terlihat seperti ini:
    ```
    /home/username/
    ├── public_html/
    ├── uigu-app/        <-- Folder aplikasi kita
    │   ├── app/
    │   ├── bootstrap/
    │   ├── config/
    │   ├── public/      <-- Folder public asli
    │   ├── vendor/
    │   └── ...
    ```

---

## Tahap 4: Konfigurasi Akses Public

Kita perlu menghubungkan `public_html` (yang diakses pengunjung) ke folder `public` di dalam aplikasi kita.

**Metode: Symlink (Paling Direkomendasikan)**
Jika Anda memiliki akses Terminal/SSH (bisa request user/pass SSH di client area Rumahweb):

1.  Login via SSH (pakai Putty atau Terminal VS Code).
2.  Hapus folder `public_html` bawaan (pastikan kosong atau backup dulu):
    ```bash
    rm -rf public_html
    ```
3.  Buat symlink dari folder public aplikasi ke public_html:
    ```bash
    ln -s uigu-app/public public_html
    ```

**Jika TIDAK ada akses SSH (Metode PHP):**
1.  Buat file `link.php` di `public_html`.
2.  Isi dengan kode:
    ```php
    <?php
    symlink('/home/usernamecpanel/uigu-app/public', '/home/usernamecpanel/public_html');
    echo "Symlink Created";
    ?>
    ```
    *(Ganti `usernamecpanel` dengan username cPanel asli Anda)*.
3.  Akses `domainanda.com/link.php` di browser. Jika sukses, hapus file `link.php`.

---

## Tahap 5: Konfigurasi Environment (.env)

1.  Masuk ke **File Manager** -> folder `uigu-app`.
2.  Cari file `.env.example`, copy atau rename menjadi `.env`.
3.  Edit file `.env` tersebut:

    ```env
    APP_NAME="UIGU Fun Run"
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://domainanda.com

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=usernamecpanel_uigu_db
    DB_USERNAME=usernamecpanel_uigu_user
    DB_PASSWORD=password_database_anda
    
    # Driver Queue & Session (Sangat disarankan pakai main database di shared hosting)
    QUEUE_CONNECTION=database
    SESSION_DRIVER=database
    CACHE_STORE=database
    ```

4.  **Generate App Key:**
    Jika punya SSH: `php artisan key:generate`.
    Jika tidak, copy `APP_KEY` dari file `.env` lokal anda ke `.env` server.

---

## Tahap 6: Migrasi Database & Setup Storage

### Jika via SSH (Disarankan):
```bash
cd ~/uigu-app
php artisan migrate --force
php artisan storage:link
php artisan filament:optimize
```

### Jika TANPA SSH (Via Route):
Karena shared hosting terbatas, kita bisa buat route sementara di `routes/web.php` *di local sebelum upload*, atau edit file di File Manager cPanel `uigu-app/routes/web.php`.

Tambahkan route sementara:
```php
Route::get('/setup-app', function () {
    // 1. Storage Link
    Artisan::call('storage:link');
    
    // 2. Migrate Database
    Artisan::call('migrate', ['--force' => true]);
    
    // 3. Optimize Cache
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache'); // Khusus Filament

    return 'Setup Complete: <pre>' . Artisan::output() . '</pre>';
});
```
Lalu akses `domainanda.com/setup-app`. **Segera HAPUS route ini setelah selesai.**

---

## Tahap 7: Setting Cron Job (Scheduler)

Scheduler penting untuk menjalankan perintah otomatis Laravel (seperti membersihkan token, menjalankan queue worker sementara).

1.  Buka menu **Cron Jobs** di cPanel.
2.  Add New Cron Job.
3.  **Common Settings:** Pilih "Once Per Minute" (`* * * * *`).
4.  **Command:**
    ```bash
    /usr/local/bin/php /home/usernamecpanel/uigu-app/artisan schedule:run >> /dev/null 2>&1
    ```
    *(Path ke PHP `/usr/local/bin/php` bisa berbeda, cek di halaman Cron Job biasanya ada contoh path php umum, atau gunakan `/opt/alt/php82/usr/bin/php` untuk versi spesifik).*

---

## Tahap 8: Menjalankan Queue (Penting untuk Email/Proses Latar Belakang)

Pada shared hosting, kita tidak bisa menggunakan Supervisor. Solusinya:

1.  Pastikan di `.env` kita set `QUEUE_CONNECTION=database`.
2.  Pastikan tabel `jobs` sudah ada (otomatis created saat migrate).
3.  Kita "meminjam" Cron Job scheduler Laravel untuk memproses queue. Laravel sudah pintar menghandle ini.
4.  Namun, cara paling robust di shared hosting tanpa supervisor adalah menambahkan command ini di Cron Job (terpisah dari schedule:run jika queue sangat padat), atau biarkan `schedule:run` memanggil job lewat kernel jika dikonfigurasi.
    
    Cara paling sederhana: Jalankan queue setiap kali ada akses (tidak direkomendasikan untuk produksi berat) atau gunakan cron job kedua:
    
    **Cron Job 2 (Opsional tapi disarankan jika ada kirim email):**
    ```bash
    /usr/local/bin/php /home/usernamecpanel/uigu-app/artisan queue:work --stop-when-empty --tries=3
    ```

---

## Fix Error 503/500 di Rumahweb (PENTING!)

### Gejala
- Homepage menampilkan error 503 "Service Unavailable" atau 500 "Internal Server Error"
- Halaman lain bisa diakses dengan normal

### Penyebab Umum
1. **Cache Laravel belum di-clear** setelah update code
2. **PHP OPcache masih menyimpan code lama**
3. **Permission file/folder tidak tepat**
4. **PHP memory limit terlalu kecil**
5. **Vite manifest tidak di-build dengan benar**

### Solusi Quick Fix

#### Via SSH (Recommended)

```bash
# 1. Masuk ke folder aplikasi
cd ~/uigu-app

# 2. Jalankan script deployment otomatis
./deploy-shared.sh
```

Script ini akan otomatis:
- Pull code terbaru (jika ada git)
- Update composer dependencies
- Clear ALL cache (Laravel & PHP)
- Run migration
- Re-optimize untuk production
- Fix permissions

#### Manual Steps (Jika Tidak Ada SSH)

**1. Clear Cache via File Manager:**

Di cPanel File Manager, masuk ke folder `uigu-app`:

```bash
# Hapus folder cache
bootstrap/cache/*.php (hapus semua file .php)
storage/framework/cache/* (hapus semua)
storage/framework/views/* (hapus semua)
```

**2. Clear Cache via Route (Temporary):**

Tambahkan route sementara di `routes/web.php`:

```php
Route::get('/clear-all-cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('event:clear');
    
    // Re-cache untuk production
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('view:cache');
    Artisan::call('event:cache');
    
    return 'Cache cleared and re-optimized!';
});
```

Akses: `https://domain-anda.com/clear-all-cache`

**SEGERA HAPUS** route ini setelah selesai!

**3. Restart PHP-FPM (Via Support):**

Di shared hosting Rumahweb, Anda tidak bisa restart PHP sendiri. Solusi:

- **Option A:** Hubungi Live Chat Rumahweb, minta restart PHP-FPM untuk domain Anda
- **Option B:** Ubah versi PHP di cPanel (Select PHP Version) ke versi lain, tunggu 30 detik, lalu kembalikan ke versi semula. Ini akan trigger restart PHP.

**4. Fix Permissions:**

Di File Manager cPanel:

```
Klik kanan folder: storage → Change Permissions → 755
Klik kanan folder: bootstrap/cache → Change Permissions → 755
```

**5. Rebuild Assets (Jika Error Vite):**

Di komputer local:

```bash
npm run build
```

Upload ulang folder `public/build/` ke server.

### Verification Checklist

Setelah fix, pastikan:

✅ File `.env` ada dan konfigurasi database benar
✅ Folder `storage` dan `bootstrap/cache` permission 755
✅ File `public/build/manifest.json` ada
✅ Symlink `public_html` → `uigu-app/public` benar
✅ Cache sudah di-clear semua
✅ Homepage return HTTP 200 (test via browser Incognito)

---

## Masalah Umum (Troubleshooting)

1.  **Error 500 (Server Error):**
    - Cek folder `storage/logs/laravel.log`.
    - Pastikan permission folder `storage` dan `bootstrap/cache` adalah `775` atau `755`.
    - Pastikan `.env` file ada dan konfigurasi database benar.
    - Clear cache Laravel (lihat section Fix Error 503/500 di atas).

2.  **Error 503 (Service Unavailable):**
    - PHP-FPM overload atau crash. Hubungi support Rumahweb untuk restart.
    - Memory limit terlalu kecil. Request naikkan `memory_limit` di PHP settings.
    - Query database terlalu berat. Pastikan sudah deploy code terbaru yang optimized.

3.  **Gambar tidak muncul (404):**
    - Pastikan symlink sudah benar (Tahap 4 & 6).
    - Cek file di `public_html/storage/`.
    - Jalankan: `php artisan storage:link`

4.  **Tailwind CSS berantakan / Style hilang:**
    - Pastikan sudah menjalankan `npm run build` di local sebelum upload.
    - Pastikan folder `build` di dalam `public` ikut terupload.
    - Clear browser cache (Ctrl + Shift + R).

5.  **Vite Manifest not found:**
    - Sama seperti no 4, folder `public/build/manifest.json` wajib ada.
    - Jalankan `npm run build` di local, upload ulang folder `public/build/`.

6.  **Class not found / Method not found:**
    - Jalankan: `composer dump-autoload`
    - Clear cache: `php artisan optimize:clear`

7.  **Database Connection Error:**
    - Cek `.env`: `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
    - Di Rumahweb, database name dan username biasanya prefix dengan username cPanel
    - Contoh: `cpanelusername_dbname`, `cpanelusername_dbuser`
