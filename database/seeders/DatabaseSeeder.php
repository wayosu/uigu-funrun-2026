<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\CheckinSetting;
use App\Models\Event;
use App\Models\JerseySize;
use App\Models\PaymentSetting;
use App\Models\RaceCategory;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding database...');

        // 1. Create Admin User
        $this->command->info('Creating admin user...');
        User::factory()->create([
            'name' => 'Admin UIGU',
            'email' => 'admin@uigu.com',
            'password' => Hash::make('password'),
        ]);

        // 2. Create Event
        $this->command->info('Creating event...');
        $event = Event::create([
            'name' => 'UIGU Fun Run 2026',
            'date' => now()->addMonths(2),
            'location' => 'Universitas Islam Global Utama, Tasikmalaya',
            'description' => '<p>UIGU Fun Run adalah acara lari tahunan yang diselenggarakan oleh Universitas Islam Global Utama untuk mempromosikan gaya hidup sehat dan mempererat komunitas.</p><p>Acara ini terbuka untuk umum dengan berbagai kategori jarak yang bisa dipilih sesuai kemampuan.</p>',
            'is_active' => true,
        ]);

        // 3. Create Race Categories
        $this->command->info('Creating race categories...');

        RaceCategory::create([
            'event_id' => $event->id,
            'name' => '5K Fun Run',
            'slug' => '5k-fun-run',
            'distance' => '5 KM',
            'description' => 'Kategori lari santai 5 kilometer, cocok untuk pemula dan keluarga.',
            'price_individual' => 150000,
            'price_collective_5' => 650000,
            'price_collective_10' => 1200000,
            'quota' => 500,
            'registration_prefix' => '5K',
            'bib_start_number' => 1001,
            'bib_end_number' => 1500,
            'registration_open_at' => now()->subDays(30),
            'registration_close_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RaceCategory::create([
            'event_id' => $event->id,
            'name' => '10K Run',
            'slug' => '10k-run',
            'distance' => '10 KM',
            'description' => 'Kategori lari 10 kilometer untuk pelari yang lebih berpengalaman.',
            'price_individual' => 200000,
            'price_collective_5' => 900000,
            'price_collective_10' => 1700000,
            'quota' => 300,
            'registration_prefix' => '10K',
            'bib_start_number' => 2001,
            'bib_end_number' => 2300,
            'registration_open_at' => now()->subDays(30),
            'registration_close_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        RaceCategory::create([
            'event_id' => $event->id,
            'name' => 'Half Marathon',
            'slug' => 'half-marathon',
            'distance' => '21 KM',
            'description' => 'Kategori half marathon untuk pelari profesional dan atlet.',
            'price_individual' => 300000,
            'price_collective_5' => null,
            'price_collective_10' => null,
            'quota' => 150,
            'registration_prefix' => 'HM',
            'bib_start_number' => 3001,
            'bib_end_number' => 3150,
            'registration_open_at' => now()->subDays(30),
            'registration_close_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        // 4. Create Payment Settings
        $this->command->info('Creating payment settings...');

        PaymentSetting::create([
            'bank_name' => 'Bank BCA',
            'account_number' => '1234567890',
            'account_name' => 'Yayasan UIGU',
            'is_active' => true,
        ]);

        PaymentSetting::create([
            'bank_name' => 'Bank Mandiri',
            'account_number' => '0987654321',
            'account_name' => 'Yayasan UIGU',
            'is_active' => true,
        ]);

        PaymentSetting::create([
            'bank_name' => 'Bank BNI',
            'account_number' => '5555666677',
            'account_name' => 'Yayasan UIGU',
            'is_active' => false,
        ]);

        // 5. Create Check-in Settings
        $this->command->info('Creating check-in settings...');

        CheckinSetting::create([
            'pin_code' => '1234',
            'is_active' => true,
            'checkin_start_time' => now()->addMonths(2)->setHour(6)->setMinute(0),
            'checkin_end_time' => now()->addMonths(2)->setHour(10)->setMinute(0),
            'check_in_location' => 'Gate Utama, Universitas Islam Global Utama',
            'instructions' => 'Silakan tunjukkan QR Code e-ticket Anda kepada petugas. Pastikan membawa KTP/identitas untuk verifikasi.',
            'allow_duplicate_scan' => false,
            'require_photo_verification' => false,
            'auto_print_bib' => true,
        ]);

        // 6. Create Notification Settings
        $this->command->info('Creating notification settings...');

        // WhatsApp Notification Settings
        \App\Models\NotificationSetting::create([
            'channel' => 'whatsapp',
            'fonnte_token' => env('FONNTE_TOKEN', null), // Will be null by default, admin must configure
            'fonnte_device_id' => env('FONNTE_DEVICE_ID', null),
            'delay_seconds' => 5,
            'max_send_per_minute' => 60,
            'retry_limit' => 3,
            'is_active' => true,
        ]);

        // Email Notification Settings
        \App\Models\NotificationSetting::create([
            'channel' => 'email',
            'delay_seconds' => 0,
            'max_send_per_minute' => 100,
            'retry_limit' => 3,
            'is_active' => true,
        ]);

        // Message Templates (Cache)
        Cache::put('notification.whatsapp_template_welcome', "PENDAFTARAN BERHASIL ✅\n\n📋 Nomor Registrasi: {registration_number}\n🏃‍♂️ Kategori: {category}\n👥 Tipe: {type}\n💳 Total Pembayaran: Rp {total}\n\nSilakan lakukan pembayaran sebelum:\n⏰ {expiry}\n\nTerima kasih telah mendaftar!");

        Cache::put('notification.email_template_welcome', "Thank you for registering!\n\nRegistration Number: {registration_number}\nCategory: {category}\nType: {type}\nTotal Payment: Rp {total}\n\nPlease complete payment before:\n{expiry}\n\nBest regards,\nUIGU Fun Run Team");

        // 7. Seed Jersey Sizes
        $this->command->info('Seeding jersey sizes...');
        $this->call(JerseySizeSeeder::class);

        // 8. Create Sample Registrations with Participants and Payments
        $this->command->info('Creating sample registrations...');

        $categories = RaceCategory::all();

        foreach ($categories as $category) {
            // Create 3 individual registrations (verified)
            for ($i = 1; $i <= 3; $i++) {
                $registration = Registration::factory()
                    ->for($category, 'raceCategory')
                    ->create([
                        'registration_type' => 'individual',
                        'status' => PaymentStatus::PaymentVerified->value,
                        'participants_count' => 1,
                    ]);

                // Create participant
                $registration->participants()->create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years'),
                    'identity_number' => fake()->numerify('################'),
                    'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
                    'jersey_size' => JerseySize::query()->active()->inRandomOrder()->first()?->code ?? 'm',
                    'emergency_name' => fake()->name(),
                    'emergency_phone' => fake()->phoneNumber(),
                    'emergency_relation' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                    'bib_number' => $category->bib_start_number + ($i - 1),
                    'is_pic' => true,
                ]);

                // Create verified payment
                $registration->payments()->create([
                    'amount' => $category->price_individual,
                    'proof_path' => 'payments/proofs/sample-proof.jpg',
                    'verified_at' => now(),
                ]);
            }

            // Create 2 pending registrations
            for ($i = 1; $i <= 2; $i++) {
                $registration = Registration::factory()
                    ->for($category, 'raceCategory')
                    ->create([
                        'registration_type' => 'individual',
                        'status' => PaymentStatus::PendingPayment->value,
                        'participants_count' => 1,
                    ]);

                $registration->participants()->create([
                    'name' => fake()->name(),
                    'email' => fake()->unique()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'gender' => fake()->randomElement(['male', 'female']),
                    'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years'),
                    'identity_number' => fake()->numerify('################'),
                    'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
                    'jersey_size' => JerseySize::query()->active()->inRandomOrder()->first()?->code ?? 'm',
                    'emergency_name' => fake()->name(),
                    'emergency_phone' => fake()->phoneNumber(),
                    'emergency_relation' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                    'is_pic' => true,
                ]);

                $registration->payments()->create([
                    'amount' => $category->price_individual,
                    'proof_path' => null,
                ]);
            }

            // Create 1 collective registration (5 people, verified)
            if ($category->price_collective_5) {
                $registration = Registration::factory()
                    ->for($category, 'raceCategory')
                    ->create([
                        'registration_type' => 'collective_5',
                        'status' => PaymentStatus::PaymentVerified->value,
                        'participants_count' => 5,
                    ]);

                for ($j = 0; $j < 5; $j++) {
                    $registration->participants()->create([
                        'name' => fake()->name(),
                        'email' => fake()->unique()->safeEmail(),
                        'phone' => fake()->phoneNumber(),
                        'gender' => fake()->randomElement(['male', 'female']),
                        'birth_date' => fake()->dateTimeBetween('-40 years', '-18 years'),
                        'identity_number' => fake()->numerify('################'),
                        'blood_type' => fake()->randomElement(['A', 'B', 'AB', 'O']),
                        'jersey_size' => JerseySize::query()->active()->inRandomOrder()->first()?->code ?? 'm',
                        'emergency_name' => fake()->name(),
                        'emergency_phone' => fake()->phoneNumber(),
                        'emergency_relation' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
                        'bib_number' => $category->bib_start_number + 10 + $j,
                        'is_pic' => $j === 0,
                    ]);
                }

                $registration->payments()->create([
                    'amount' => $category->price_collective_5,
                    'proof_path' => 'payments/proofs/sample-proof.jpg',
                    'verified_at' => now(),
                ]);
            }
        }

        $this->command->info('✅ Database seeding completed!');
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->info('- Users: '.User::count());
        $this->command->info('- Events: '.Event::count());
        $this->command->info('- Race Categories: '.RaceCategory::count());
        $this->command->info('- Payment Settings: '.PaymentSetting::count());
        $this->command->info('- Registrations: '.Registration::count());
        $this->command->info('- Participants: '.\App\Models\Participant::count());
        $this->command->newLine();
        $this->command->info('🔐 Admin Login:');
        $this->command->info('Email: admin@uigu.com');
        $this->command->info('Password: password');
    }
}
