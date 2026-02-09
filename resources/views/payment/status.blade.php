@extends('layouts.app')

@php
    use App\Enums\PaymentStatus;
    use Carbon\Carbon;

    // Status Logic Helper
    $statusEnum = $registration->status;
    $latestPayment = $registration->payments()->latest()->first();
    $isRejected = $latestPayment && !empty($latestPayment->rejection_reason);

    $statusTitle = 'Menunggu Pembayaran';
    $statusDesc = 'Silakan selesaikan pembayaran Anda.';
    $statusColor = 'bg-yellow-100 text-yellow-700';
    $statusIcon = 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'; // Clock

    if ($latestPayment) {
        if ($statusEnum === PaymentStatus::PaymentVerified) {
            $statusTitle = 'Pembayaran Berhasil';
            $statusDesc = 'Pendaftaran Anda telah dikonfirmasi.';
            $statusColor = 'bg-fun-green/10 text-fun-green';
            $statusIcon = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'; // Check Circle
        } elseif ($statusEnum === PaymentStatus::PaymentUploaded) {
             $statusTitle = 'Pembayaran Sedang Diverifikasi';
             $statusDesc = 'Bukti pembayaran Anda sedang kami cek.';
             $statusColor = 'bg-blue-100 text-blue-700';
             $statusIcon = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'; // Document
        } elseif ($isRejected) {
             $statusTitle = 'Pembayaran Ditolak';
             $statusDesc = 'Mohon unggah ulang bukti pembayaran yang valid.';
             $statusColor = 'bg-red-100 text-red-700';
             $statusIcon = 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'; // X Circle
        }
    }
@endphp

@section('content')
    <div x-data="{ showProofModal: false }" class="min-h-screen bg-gray-50/50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Status Card Header --}}
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden mb-8 border border-gray-100">
                <div class="bg-gradient-to-r from-fun-green to-fun-teal p-8 text-center relative overflow-hidden">
                    {{-- Decorative Shapes --}}
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 bg-fun-yellow/20 rounded-full blur-3xl"></div>

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-20 h-20 rounded-full bg-white flex items-center justify-center mb-4 shadow-lg animate-bounce-slow">
                            <svg class="w-10 h-10 {{ $isRejected ? 'text-red-500' : ($statusEnum === PaymentStatus::PaymentVerified ? 'text-fun-green' : 'text-fun-teal') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-heading font-bold text-white mb-2">{{ $statusTitle }}</h1>
                        <p class="text-fun-yellow font-medium text-lg">{{ $statusDesc }}</p>

                        {{-- Expired Warning or Upload Button --}}
                        @if($statusEnum === PaymentStatus::PendingPayment && !$latestPayment)
                            <div class="mt-4 bg-white/20 backdrop-blur-sm rounded-xl p-4 max-w-lg mx-auto border border-white/30">
                                @if($registration->expired_at)
                                    <p class="text-white font-medium mb-3">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Batas Waktu: <span class="font-bold">{{ $registration->expired_at->format('d M Y H:i') }}</span>
                                    </p>
                                    @if($registration->expired_at->isFuture())
                                        <p class="text-fun-yellow text-sm mb-3">{{ $registration->expired_at->diffForHumans() }}</p>
                                    @endif
                                @endif
                                <a href="{{ route('payment.show', $registration->registration_number) }}" class="inline-block px-6 py-2 bg-white text-fun-green font-bold rounded-full hover:bg-gray-100 transition-colors shadow-md">
                                    Upload Bukti Pembayaran
                                </a>
                            </div>
                        @endif

                        @if($isRejected)
                            <div class="mt-4 bg-white/20 backdrop-blur-sm rounded-xl p-4 max-w-lg mx-auto border border-white/30">
                                <p class="text-white font-medium">Alasan: {{ $latestPayment->rejection_reason ?? 'Bukti pembayaran tidak terbaca atau tidak valid.' }}</p>
                                <a href="{{ route('payment.show', ['registration' => $registration->registration_number]) }}" class="mt-3 inline-block px-6 py-2 bg-white text-red-600 font-bold rounded-full hover:bg-gray-100 transition-colors shadow-md">
                                    Unggah Ulang Bukti
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status Timeline --}}
                <div class="px-8 py-6 bg-white border-b border-gray-100">
                    <div class="relative">
                        <div class="absolute top-4 left-0 w-full h-1 bg-gray-200 rounded-full -z-0"></div>
                        <div class="flex justify-between relative z-10">
                            {{-- Step 1: Registered --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-fun-green text-white flex items-center justify-center ring-4 ring-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="mt-2 text-xs font-bold text-gray-700 uppercase tracking-wide">Daftar</span>
                            </div>

                            {{-- Step 2: Payment --}}
                            <div class="flex flex-col items-center">
                                @if($latestPayment)
                                    @if($isRejected)
                                        <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </div>
                                        <span class="mt-2 text-xs font-bold text-red-500 uppercase tracking-wide">Ditolak</span>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-fun-green text-white flex items-center justify-center ring-4 ring-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <span class="mt-2 text-xs font-bold text-gray-700 uppercase tracking-wide">Bayar</span>
                                    @endif
                                @else
                                    <div class="w-8 h-8 rounded-full bg-white border-2 border-fun-yellow text-gray-400 flex items-center justify-center ring-4 ring-white">
                                        <span class="text-xs font-bold text-fun-yellow">2</span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-gray-400 uppercase tracking-wide">Bayar</span>
                                @endif
                            </div>

                            {{-- Step 3: Verified --}}
                            <div class="flex flex-col items-center">
                                @if($statusEnum === PaymentStatus::PaymentVerified)
                                    <div class="w-8 h-8 rounded-full bg-fun-green text-white flex items-center justify-center ring-4 ring-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-fun-green uppercase tracking-wide">Verifikasi</span>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-white border-2 border-gray-300 text-gray-400 flex items-center justify-center ring-4 ring-white">
                                        <span class="text-xs font-bold">3</span>
                                    </div>
                                    <span class="mt-2 text-xs font-bold text-gray-400 uppercase tracking-wide">Verifikasi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details Section --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-8 py-5 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <h3 class="text-xl font-heading font-bold text-gray-800">Detail Pendaftaran</h3>
                    <span class="font-mono text-sm bg-white border border-gray-200 px-3 py-1 rounded-lg text-gray-600 shadow-sm">
                        ID: <span class="font-bold text-fun-green">{{ $registration->registration_number }}</span>
                    </span>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        {{-- Left Col: Personal Info --}}
                        <div>
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-6 pb-2 border-b border-gray-100">Info Peserta</h4>
                            <div class="space-y-6">
                                <div class="flex items-start group">
                                    <div class="w-10 h-10 rounded-full bg-fun-green/10 flex items-center justify-center flex-shrink-0 text-fun-green mr-4 group-hover:bg-fun-green group-hover:text-white transition-colors">
                                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Nama & Email</p>
                                        <p class="text-base font-bold text-gray-900">{{ $registration->pic_name }}</p>
                                        <p class="text-sm text-gray-600">{{ $registration->pic_email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start group">
                                     <div class="w-10 h-10 rounded-full bg-fun-green/10 flex items-center justify-center flex-shrink-0 text-fun-green mr-4 group-hover:bg-fun-green group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Nomor Telepon</p>
                                        <p class="text-base font-medium text-gray-900">{{ $registration->pic_phone }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Col: Race & Payment --}}
                        <div>
                             <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-6 pb-2 border-b border-gray-100">Kategori & Pembayaran</h4>
                             <div class="bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-100 p-6 space-y-4 shadow-sm">
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-600">Kategori Lari</span>
                                    <span class="px-3 py-1 bg-fun-green/10 text-fun-green rounded-full font-bold text-xs">{{ $registration->raceCategory?->name ?? '-' }}</span>
                                </div>

                                @if(in_array($registration->registration_type, \App\Enums\RegistrationType::collectiveTypes(), true))
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Jenis</span>
                                        <span class="font-medium text-gray-900">Kolektif ({{ $registration->participants->count() }} Pelari)</span>
                                    </div>
                                @endif

                                {{-- Payment Timeline Info --}}
                                @if($latestPayment)
                                    <div class="border-t border-gray-200 pt-4 space-y-3">
                                        <div class="flex items-start text-sm">
                                            <svg class="w-4 h-4 text-gray-400 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <div>
                                                <span class="text-gray-500 block">Bukti Diupload</span>
                                                <span class="text-gray-900 font-medium">{{ $latestPayment->created_at->format('d M Y H:i') }}</span>
                                                <span class="text-gray-400 text-xs block">{{ $latestPayment->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>

                                        @if($latestPayment->verified_at)
                                            <div class="flex items-start text-sm">
                                                <svg class="w-4 h-4 text-fun-green mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <div>
                                                    <span class="text-gray-500 block">Diverifikasi</span>
                                                    <span class="text-fun-green font-medium">{{ $latestPayment->verified_at->format('d M Y H:i') }}</span>
                                                    <span class="text-gray-400 text-xs block">{{ $latestPayment->verified_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                                    <span class="text-gray-600 font-medium">Total Tagihan</span>
                                    <span class="text-2xl font-heading font-bold text-fun-green">
                                        Rp {{ number_format($registration->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                             </div>
                        </div>
                    </div>

                    {{-- Payment Proof Preview --}}
                    @if($latestPayment && $latestPayment->proof_path)
                        <div class="mt-10">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 pb-2 border-b border-gray-100">Bukti Pembayaran</h4>
                            <div class="bg-gray-50 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
                                <div class="relative group cursor-pointer flex-shrink-0" @click="showProofModal = true">
                                    <div class="w-48 h-48 rounded-lg overflow-hidden border-2 border-gray-200 shadow-md">
                                        <img src="{{ Storage::url($latestPayment->proof_path) }}"
                                             alt="Bukti Pembayaran"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 rounded-lg transition-colors duration-300 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <p class="text-sm text-gray-500 mb-1">Status Bukti</p>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusColor }}">
                                                @if($statusEnum === PaymentStatus::PaymentVerified)
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Terverifikasi
                                                @elseif($statusEnum === PaymentStatus::PaymentUploaded)
                                                    <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                    Sedang Diverifikasi
                                                @elseif($isRejected)
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    Ditolak
                                                @endif
                                            </span>
                                        </div>
                                        <button @click="showProofModal = true" class="text-fun-green hover:text-fun-teal text-sm font-medium flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Lihat Detail
                                        </button>
                                    </div>
                                    @if($isRejected && $latestPayment->rejection_reason)
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-3">
                                            <p class="text-xs font-bold text-red-700 uppercase tracking-wide mb-1">Alasan Penolakan</p>
                                            <p class="text-sm text-red-600">{{ $latestPayment->rejection_reason }}</p>
                                        </div>
                                    @endif
                                    @if($latestPayment->verifier)
                                        <p class="text-xs text-gray-500 mt-3">
                                            Diverifikasi oleh: <span class="font-medium text-gray-700">{{ $latestPayment->verifier->name }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Payment History (if multiple uploads) --}}
                    @if($registration->payments->count() > 1)
                        <div class="mt-10" x-data="{ showHistory: false }">
                            <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                                <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Riwayat Upload ({{ $registration->payments->count() }} kali)</h4>
                                <button @click="showHistory = !showHistory" class="text-fun-green hover:text-fun-teal text-sm font-medium flex items-center">
                                    <span x-text="showHistory ? 'Sembunyikan' : 'Tampilkan'"></span>
                                    <svg class="w-4 h-4 ml-1 transition-transform" :class="showHistory && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </div>

                            <div x-show="showHistory"
                                 x-collapse
                                 class="space-y-4">
                                @foreach($registration->payments()->latest()->get() as $index => $payment)
                                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 {{ $loop->first ? 'ring-2 ring-fun-green/20' : '' }}">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center">
                                                <span class="text-xs font-bold text-gray-400 mr-3">#{{ $loop->iteration }}</span>
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900">
                                                        {{ $payment->created_at->format('d M Y, H:i') }}
                                                        @if($loop->first)
                                                            <span class="ml-2 text-xs bg-fun-green text-white px-2 py-0.5 rounded-full">Terbaru</span>
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-500">{{ $payment->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            @if($payment->proof_path)
                                                <a href="{{ Storage::url($payment->proof_path) }}"
                                                   target="_blank"
                                                   class="text-fun-green hover:text-fun-teal text-xs font-medium">
                                                    Lihat
                                                </a>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-500">Jumlah:</span>
                                                <span class="font-medium text-gray-900">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Status:</span>
                                                @if($payment->verified_at)
                                                    <span class="inline-flex items-center text-fun-green font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Terverifikasi
                                                    </span>
                                                @elseif($payment->rejection_reason)
                                                    <span class="inline-flex items-center text-red-600 font-medium">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                        Ditolak
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center text-blue-600 font-medium">
                                                        <svg class="w-3 h-3 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                        Menunggu Verifikasi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($payment->rejection_reason)
                                            <div class="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                                                <p class="text-xs font-bold text-red-700 uppercase tracking-wide mb-1">Alasan Penolakan</p>
                                                <p class="text-xs text-red-600">{{ $payment->rejection_reason }}</p>
                                            </div>
                                        @endif

                                        @if($payment->verifier)
                                            <p class="text-xs text-gray-500 mt-3">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                                {{ $payment->verifier->name }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Team Members Table --}}
                    @if(in_array($registration->registration_type, \App\Enums\RegistrationType::collectiveTypes(), true) && $registration->participants->count() > 0)
                        <div class="mt-10">
                             <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Anggota Tim</h4>
                             <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5 rounded-xl">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Peserta</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ukuran Jersey</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor BIB</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($registration->participants as $participant)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $participant->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $participant->jersey_size }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-fun-teal font-bold">
                                                     {{ $participant->bib_number ?? '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                             </div>
                        </div>
                    @endif
                </div>

                {{-- Action Footer (Visible only if Verified) --}}
                @if($statusEnum === PaymentStatus::PaymentVerified)
                    <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex flex-col sm:flex-row justify-center items-center gap-4">
                        <a href="{{ route('ticket.download', $registration->registration_number) }}" class="w-full sm:w-auto flex items-center justify-center px-8 py-4 border border-transparent shadow-lg text-base font-bold rounded-full text-fun-green bg-fun-yellow hover:bg-yellow-400 transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Unduh E-Ticket
                        </a>
                    </div>
                @endif
            </div>

            <div class="text-center mt-8">
                 <a href="{{ url('/') }}" class="inline-flex items-center text-gray-500 hover:text-fun-green font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>

        {{-- Payment Proof Modal --}}
        @if($latestPayment && $latestPayment->proof_path)
            <div x-show="showProofModal"
                 x-cloak
                 @click.self="showProofModal = false"
                 @keydown.escape.window="showProofModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4"
                 style="display: none;">
                <div @click.stop class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                    {{-- Modal Header --}}
                    <div class="bg-gradient-to-r from-fun-green to-fun-teal px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <h3 class="text-xl font-heading font-bold text-white">Bukti Pembayaran</h3>
                        </div>
                        <button @click="showProofModal = false" class="text-white hover:text-fun-yellow transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-200px)]">
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <img src="{{ Storage::url($latestPayment->proof_path) }}"
                                 alt="Bukti Pembayaran"
                                 class="w-full h-auto rounded-lg shadow-lg">
                        </div>

                        {{-- Payment Info --}}
                        <div class="grid grid-cols-2 gap-4 bg-white border border-gray-200 rounded-lg p-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Jumlah</p>
                                <p class="text-lg font-bold text-gray-900">Rp {{ number_format($latestPayment->amount, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Upload</p>
                                <p class="text-sm font-medium text-gray-900">{{ $latestPayment->created_at->format('d M Y H:i') }}</p>
                            </div>
                            @if($latestPayment->verified_at)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Verifikasi</p>
                                    <p class="text-sm font-medium text-fun-green">{{ $latestPayment->verified_at->format('d M Y H:i') }}</p>
                                </div>
                            @endif
                            @if($latestPayment->verifier)
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Verifikator</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $latestPayment->verifier->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t border-gray-200">
                        <p class="text-xs text-gray-500">ID: {{ $registration->registration_number }}</p>
                        <button @click="showProofModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
