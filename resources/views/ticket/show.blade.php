@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen py-10 print:bg-white print:py-0">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-6 print:hidden">
                <a href="{{ route('ticket.download', $registration->registration_number) }}" class="inline-flex items-center px-6 py-3 border border-transparent shadow-lg text-sm font-bold rounded-full text-white bg-fun-teal hover:bg-teal-600 transition-all transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fun-teal">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Unduh PDF (Cetak)
                </a>
                <a href="{{ url('/') }}" class="ml-4 text-gray-500 hover:text-fun-green text-sm font-medium">Kembali</a>
            </div>

            <div class="bg-white rounded-3xl shadow-xl overflow-hidden print:shadow-none print:border print:border-gray-200">
                {{-- Ticket Header --}}
                <div class="bg-gradient-to-r from-fun-green to-fun-teal px-8 py-8 flex flex-col sm:flex-row justify-between items-center print:bg-white print:border-b print:border-gray-300 relative overflow-hidden">
                     {{-- Decorative Shapes --}}
                    <div class="absolute top-0 right-0 -mr-16 -mt-16 w-48 h-48 bg-white/10 rounded-full blur-3xl print:hidden"></div>
                    <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-32 h-32 bg-fun-yellow/20 rounded-full blur-2xl print:hidden"></div>

                    <div class="relative z-10 flex items-center gap-4 mb-4 sm:mb-0">
                        {{-- Use asset logo if available --}}
                         <img src="{{ asset('assets/logo-event-1.png') }}" class="h-16 w-auto bg-white rounded-lg p-2 object-contain shadow-sm" alt="Logo">
                         <div class="text-white print:text-black">
                            <h1 class="text-2xl font-black tracking-widest leading-none">E-TICKET</h1>
                            <span class="text-fun-yellow font-bold print:text-black block text-sm tracking-wide">{{ $registration->raceCategory->event->name ?? 'UIGU FUN RUN' }}</span>
                         </div>
                    </div>
                    <div class="relative z-10 text-center sm:text-right">
                         <span class="text-white/80 text-xs font-bold uppercase tracking-wider block mb-1">Kategori Tiket</span>
                         <span class="bg-fun-yellow text-fun-dark px-4 py-1 rounded-full font-heading font-black text-lg uppercase shadow-sm print:bg-transparent print:border print:border-black">{{ $registration->raceCategory->name }}</span>
                    </div>
                </div>

                {{-- Ticket Body --}}
                <div class="p-8 relative overflow-hidden min-h-[400px]">
                    {{-- Watermark --}}
                    <div class="absolute -right-20 -bottom-20 opacity-5 pointer-events-none print:opacity-10">
                        <svg class="w-96 h-96 text-fun-green" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14h2v2h-2zm0-10h2v8h-2z"/></svg>
                    </div>

                    {{-- Event Details --}}
                    <div class="mb-8 border-b border-gray-100 pb-8 relative z-10">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-8">
                            <div class="flex-1">
                                <h2 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Detail Acara</h2>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="flex items-start group">
                                        <div class="w-8 flex-shrink-0 text-fun-green">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase">Tanggal</p>
                                            <p class="font-bold text-gray-800 text-lg">{{ $registration->raceCategory->event->date?->format('l, d F Y') ?? 'TBA' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start group">
                                        <div class="w-8 flex-shrink-0 text-fun-teal">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase">Waktu</p>
                                            <p class="font-bold text-gray-800 text-lg">{{ $registration->raceCategory->event->date?->format('H:i') ?? 'TBA' }} WITA</p>
                                        </div>
                                    </div>

                                    <div class="flex items-start group">
                                         <div class="w-8 flex-shrink-0 text-red-500">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400 font-bold uppercase">Lokasi</p>
                                            <p class="font-bold text-gray-800 text-lg">{{ $registration->raceCategory->event->location ?? 'Universitas Ichsan Gorontalo Utara' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full md:w-auto text-left md:text-center p-6 rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 print:bg-transparent print:border-none print:p-0">
                                <p class="text-xs text-gray-500 uppercase tracking-widest font-bold mb-2">ID Registrasi</p>
                                <p class="text-2xl font-mono font-black text-fun-teal mb-4 tracking-tighter">
                                    {{ $registration->registration_number }}
                                </p>
                                <div class="flex justify-center bg-white p-2 rounded-lg shadow-sm w-fit mx-auto print:hidden">
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->color(0, 154, 166)->generate($registration->registration_number) !!}
                                </div>
                                <div class="hidden print:block flex justify-center mt-2">
                                     {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate($registration->registration_number) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Participant List --}}
                    <div>
                        <h3 class="text-sm text-gray-600 uppercase tracking-widest font-bold mb-6 border-l-4 border-fun-green pl-3">Daftar Peserta</h3>
                        <div class="bg-gray-50 rounded-2xl p-2 print:bg-transparent print:p-0">
                            @foreach($registration->participants as $index => $participant)
                                <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm mb-2 border border-gray-100 last:mb-0 print:border-b print:shadow-none print:rounded-none">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-fun-green text-white flex items-center justify-center font-bold text-sm shadow-inner print:bg-black print:text-white">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 text-lg">{{ $participant->name }}</p>
                                            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Size: <span class="text-fun-teal">{{ $participant->jersey_size }}</span></p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-block px-3 py-1 bg-gray-100 text-gray-700 text-sm rounded-lg font-mono font-bold border border-gray-200 print:border-black">
                                            BIB: {{ $participant->bib_number ?? 'PENDING' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Footer Note --}}
                    <div class="mt-8 text-center text-xs text-gray-400 print:mt-12">
                        <p>Simpan E-Ticket ini. Tunjukkan QR Code saat pengambilan Race Pack.</p>
                        <p class="mt-1">&copy; 2024 UIGU Fun Run.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
