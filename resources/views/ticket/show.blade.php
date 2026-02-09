@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8 print:bg-white print:p-0">
    <div class="max-w-3xl mx-auto print:max-w-none">
        <!-- Navigation & Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-6 print:hidden">
            <a href="{{ route('filament.admin.auth.login') }}" class="text-sm font-medium text-gray-500 hover:text-[#00d285] transition-colors mb-4 sm:mb-0">
                &larr; Kembali ke Dashboard
            </a>
            <a href="{{ route('ticket.download', $registration->registration_number) }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-xl text-white bg-[#00d285] hover:bg-[#00b874] shadow-lg shadow-teal-500/20 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00d285]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF
            </a>
        </div>

        <!-- Ticket Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 print:shadow-none print:border print:border-gray-300">
            <!-- Header with Gradient -->
            <div class="relative bg-gradient-to-br from-[#00d285] to-[#009aa6] p-8 sm:p-10 text-white overflow-hidden print:bg-white print:text-black print:border-b print:border-gray-200">
                <!-- Decorative Elements -->
                <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl print:hidden"></div>
                <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-40 h-40 bg-[#f8c400] opacity-20 rounded-full blur-2xl print:hidden"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-4">
                            @if(file_exists(public_path('assets/logo-event-1.png')))
                                <img src="{{ asset('assets/logo-event-1.png') }}" alt="UIGU Logo" class="h-10 bg-white rounded-lg p-1 shadow-sm print:hidden">
                            @endif
                            <span class="bg-[#f8c400] text-gray-900 text-xs font-black px-3 py-1 rounded-lg uppercase tracking-wider shadow-sm print:border print:border-black">
                                {{ $registration->raceCategory->name }}
                            </span>
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black tracking-tight text-white mb-2 font-heading leading-tight drop-shadow-sm print:text-black">
                            {{ $registration->raceCategory->event->name ?? 'UIGU FUN RUN' }}
                        </h1>
                        <p class="text-teal-50 font-bold tracking-widest uppercase text-sm print:text-gray-500">Official E-Ticket</p>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-8 sm:p-10 bg-white print:p-0 print:mt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-10">
                    <!-- Event Details -->
                    <div class="md:col-span-2 space-y-8">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-3 mb-6">Detail Acara</h3>

                        <div class="space-y-6">
                            <!-- Date & Time Row -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div class="group">
                                    <div class="flex items-center text-gray-500 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-green-50 text-[#00d285] flex items-center justify-center mr-3 group-hover:bg-[#00d285] group-hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Tanggal</span>
                                    </div>
                                    <p class="text-xl font-bold text-gray-900 ml-11">
                                        {{ $registration->raceCategory->event->date?->locale('id')->translatedFormat('l, d F Y') ?? 'Untuk Diumumkan' }}
                                    </p>
                                </div>

                                <div class="group">
                                    <div class="flex items-center text-gray-500 mb-2">
                                        <div class="w-8 h-8 rounded-lg bg-teal-50 text-[#009aa6] flex items-center justify-center mr-3 group-hover:bg-[#009aa6] group-hover:text-white transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Waktu Start</span>
                                    </div>
                                    <p class="text-xl font-bold text-gray-900 ml-11">
                                        {{ $registration->raceCategory->event->date?->format('H:i') ?? 'TBA' }} WITA
                                    </p>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="group">
                                <div class="flex items-center text-gray-500 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center mr-3 group-hover:bg-red-500 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wide text-gray-400">Lokasi</span>
                                </div>
                                <p class="text-xl font-bold text-gray-900 leading-snug ml-11">
                                    {{ $registration->raceCategory->event->location ?? 'Universitas Ichsan Gorontalo Utara' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="bg-[#f0fdf9] border-2 border-dashed border-[#00d285] rounded-2xl p-6 flex flex-col items-center justify-center text-center print:border-gray-300 print:bg-white">
                        <span class="text-xs font-black text-[#009aa6] uppercase tracking-widest mb-3">ID Registrasi</span>
                        <span class="font-mono text-2xl font-black text-gray-900 mb-6 tracking-tighter border-b-2 border-[#00d285] border-dashed pb-2 w-full">{{ $registration->registration_number }}</span>

                        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-4 print:shadow-none print:border-none">
                            {!! QrCode::size(150)->color(0, 50, 60)->generate($registration->registration_number) !!}
                        </div>
                        <span class="text-[10px] text-[#009aa6] font-bold uppercase tracking-wide print:text-black">Scan saat Registrasi Ulang</span>
                    </div>
                </div>

                <!-- Divider -->
                <div class="relative my-10 border-t-2 border-dashed border-gray-200 print:hidden">
                    <div class="absolute -left-12 -top-4 w-8 h-8 bg-gray-50 rounded-full"></div>
                    <div class="absolute -right-12 -top-4 w-8 h-8 bg-gray-50 rounded-full"></div>
                </div>

                <!-- Participants List -->
                <div>
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6">Peserta Terdaftar</h3>
                    <div class="border border-gray-100 rounded-2xl overflow-hidden divide-y divide-gray-100 shadow-sm print:border-gray-300">
                        @foreach($registration->participants as $index => $participant)
                            <div class="p-5 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors">
                                <div class="flex items-center space-x-5">
                                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-[#00d285] to-[#009aa6] text-white rounded-full flex items-center justify-center font-bold text-sm shadow-md print:bg-none print:bg-gray-200 print:text-black">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <p class="text-base font-bold text-gray-900">{{ $participant->name }}</p>
                                        <p class="text-xs text-gray-500 font-medium mt-0.5 uppercase tracking-wide">Ukuran Jersey: <span class="font-bold text-gray-800">{{ $participant->jersey_size }}</span></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] uppercase text-gray-400 tracking-wider font-bold mb-1">Nomor BIB</span>
                                    @if($participant->bib_number)
                                        <span class="inline-block bg-[#1a1a1a] text-[#f8c400] text-xl font-mono font-black px-3 py-1 rounded-lg shadow-sm print:border print:border-black print:text-black print:bg-white">
                                            {{ $participant->bib_number }}
                                        </span>
                                    @else
                                        <span class="inline-block bg-gray-100 text-gray-400 text-xs font-bold px-3 py-1.5 rounded-lg italic border border-gray-200">
                                            Belum Rilis
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Footer Message -->
            <div class="bg-gray-50 p-6 text-center border-t border-gray-200 print:hidden">
                <p class="text-xs text-gray-500">Harap membawa E-Ticket ini (digital/cetak) dan identitas diri saat pengambilan Race Pack.</p>
            </div>
        </div>

        <div class="mt-8 text-center text-gray-400 text-xs print:hidden">
            &copy; {{ date('Y') }} Universitas Ichsan Gorontalo Utara. All rights reserved.
        </div>
    </div>
</div>
@endsection
