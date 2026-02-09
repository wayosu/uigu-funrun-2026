@extends('layouts.app')

@php
    use App\Enums\PaymentStatus;
    use Carbon\Carbon;

    // Status Logic Helper
    $statusEnum = $registration->payment ? $registration->payment->status : PaymentStatus::PendingPayment;
    $isRejected = $registration->payment && !empty($registration->payment->rejection_reason);

    $statusTitle = 'Menunggu Pembayaran';
    $statusDesc = 'Silakan selesaikan pembayaran Anda.';
    $statusColor = 'bg-yellow-100 text-yellow-700';
    $statusIcon = 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'; // Clock

    if ($registration->payment) {
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
    <div class="min-h-screen bg-gray-50/50 py-12">
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

                        @if($isRejected)
                            <div class="mt-4 bg-white/20 backdrop-blur-sm rounded-xl p-4 max-w-lg mx-auto border border-white/30">
                                <p class="text-white font-medium">Alasan: {{ $registration->payment->rejection_reason ?? 'Bukti pembayaran tidak terbaca atau tidak valid.' }}</p>
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
                                @if($registration->payment)
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
                                        <p class="text-base font-bold text-gray-900">{{ $registration->user?->name ?? '-' }}</p>
                                        <p class="text-sm text-gray-600">{{ $registration->user?->email ?? '-' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start group">
                                     <div class="w-10 h-10 rounded-full bg-fun-green/10 flex items-center justify-center flex-shrink-0 text-fun-green mr-4 group-hover:bg-fun-green group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Nomor Telepon</p>
                                        <p class="text-base font-medium text-gray-900">{{ $registration->user?->phone_number ?? '-' }}</p>
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

                                <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                                    <span class="text-gray-600 font-medium">Total Tagihan</span>
                                    <span class="text-2xl font-heading font-bold text-fun-green">
                                        Rp {{ number_format($registration->total_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                             </div>
                        </div>
                    </div>

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
    </div>
@endsection
