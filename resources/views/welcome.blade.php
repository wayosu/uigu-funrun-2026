@extends('layouts.app')

@section('content')
    @if ($event)
        {{-- Hero Section --}}
        <div class="relative min-h-screen bg-fun-dark flex items-center justify-center overflow-hidden">
            {{-- Background Gradient --}}
            <div class="absolute inset-0 bg-gradient-to-br from-fun-teal via-fun-green to-fun-yellow opacity-10"></div>

            {{-- Abstract Lines Pattern --}}
            <div class="absolute inset-0 opacity-20 pointer-events-none overflow-hidden"
                style="background-image: radial-gradient(#00d285 1px, transparent 1px); background-size: 40px 40px;">
            </div>

            {{-- Organic Shapes --}}
            <div
                class="absolute top-[-20%] left-[-10%] w-[50%] h-[50%] bg-gradient-to-br from-fun-green to-fun-teal rounded-full blur-[100px] opacity-20 animate-pulse">
            </div>
            <div class="absolute bottom-[-10%] right-[-5%] w-[40%] h-[40%] bg-gradient-to-tr from-fun-yellow to-fun-green rounded-full blur-[80px] opacity-20 animate-pulse"
                style="animation-delay: 2s;"></div>

            {{-- Hero Content --}}
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center flex flex-col items-center">
                {{-- Logo with Glow --}}
                <div class="mb-12 relative group">
                    <div
                        class="absolute inset-0 bg-fun-yellow blur-3xl opacity-20 group-hover:opacity-40 transition-opacity duration-500 rounded-full">
                    </div>
                    <img src="{{ asset('assets/logo-event-1.png') }}" alt="{{ $event->name }}"
                        class="relative h-32 md:h-48 w-auto drop-shadow-2xl transform hover:scale-105 transition duration-500">
                </div>

                {{-- Main Date & Location --}}
                <div
                    class="flex flex-wrap justify-center gap-6 mb-8 text-sm md:text-base font-bold tracking-widest uppercase text-white/80">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-fun-yellow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>{{ $event->date->locale('id')->isoFormat('D MMMM Y') }}</span>
                    </div>
                    <div class="w-1.5 h-1.5 rounded-full bg-fun-green hidden sm:block"></div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-fun-yellow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="text-5xl md:text-8xl font-black text-white tracking-tighter mb-8 leading-tight drop-shadow-sm">
                    Steps for a <br class="md:hidden" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-fun-yellow via-fun-green to-fun-teal">
                        Sustainable Future
                    </span>
                </h1>

                @if($event->description)
                    <div class="mt-4 max-w-2xl mx-auto text-lg text-gray-300 leading-relaxed font-light mb-12">
                        {!! $event->description !!}
                    </div>
                @endif

                {{-- CTA Button --}}
                <a href="#categories"
                    class="group relative inline-flex items-center justify-center px-10 py-5 text-lg font-bold text-white transition-all duration-200 bg-transparent font-heading rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fun-green">
                    <span
                        class="absolute inset-0 w-full h-full -mt-1 rounded-full opacity-30 bg-gradient-to-r from-fun-green via-fun-teal to-fun-green"></span>
                    <span
                        class="absolute inset-0 w-full h-full mt-1 rounded-full opacity-30 bg-gradient-to-r from-fun-green via-fun-teal to-fun-green"></span>
                    <span
                        class="relative w-full h-full flex items-center bg-gradient-to-r from-fun-green to-fun-teal rounded-full inset-0 px-10 py-5 group-hover:scale-105 transition-transform duration-300 shadow-[0_0_20px_rgba(0,210,133,0.3)] hover:shadow-[0_0_40px_rgba(0,210,133,0.5)]">
                        DAFTAR SEKARANG
                        <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3">
                            </path>
                        </svg>
                    </span>
                </a>
            </div>

            {{-- Scroll Helper --}}
            <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce text-fun-green">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </div>

        {{-- Race Categories --}}
        <div id="categories" class="relative py-32 bg-gray-50 overflow-hidden">
            {{-- Fancy Background --}}
            <div
                class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMSIgY3k9IjEiIHI9IjEiIGZpbGw9InJnYmEoMCwgMCwgMCwgMC4wNSkiLz48L3N2Zz4=')] bg-repeat opacity-50">
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <span class="text-fun-teal font-bold tracking-widest uppercase text-sm mb-4 block">Pilih Tantanganmu</span>
                    <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-6 font-heading">Kategori Lomba</h2>
                    <div class="w-24 h-1.5 bg-gradient-to-r from-fun-yellow to-fun-green rounded-full mx-auto"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($event->raceCategories as $category)
                        <div
                            class="group relative bg-white rounded-3xl p-1 transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                            {{-- Gradient Border Effect --}}
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl -z-10 group-hover:from-fun-green group-hover:to-fun-teal transition-all duration-500">
                            </div>

                            <div class="h-full bg-white rounded-[22px] overflow-hidden flex flex-col p-8 relative z-0">
                                {{-- Icon / Category Badge --}}
                                <div class="mb-6 flex items-center justify-between">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-fun-green/10 text-fun-green flex items-center justify-center group-hover:bg-fun-green group-hover:text-white transition-colors duration-300">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <span
                                        class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 text-gray-600 group-hover:bg-fun-yellow group-hover:text-fun-dark transition-colors">{{ $category->distance }}</span>
                                </div>

                                <h3 class="text-3xl font-bold text-gray-900 mb-2 font-heading">{{ $category->name }}</h3>
                                {{-- <div class="text-sm text-gray-500 font-medium mb-6 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-fun-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Start: {{ \Carbon\Carbon::parse($category->start_time)->format('H:i') }} WIB
                                </div> --}}

                                <div class="space-y-4 mb-8 flex-grow">
                                    @if($category->description)
                                        <p class="text-gray-600 text-sm leading-relaxed border-l-2 border-fun-green/30 pl-3">
                                            {{ $category->description }}
                                        </p>
                                    @endif

                                    {{-- Price --}}
                                    <div class="pt-4 border-t border-gray-100">
                                        <div class="flex items-baseline text-gray-900">
                                            <span class="text-3xl font-black tracking-tight">Rp
                                                {{ number_format($category->price_individual, 0, ',', '.') }}</span>
                                        </div>
                                        {{-- <p class="text-xs text-gray-400 mt-1">sudah termasuk medali & jersey</p> --}}
                                    </div>
                                </div>

                                @php
                                    $state = $categoryStates[$category->id] ?? ['state' => 'open', 'label' => 'Daftar Kategori Ini', 'disabled' => false];
                                @endphp

                                @if($state['disabled'])
                                    <button disabled class="block w-full py-4 rounded-xl font-bold text-center text-sm uppercase tracking-widest transition-all duration-300
                                                                bg-gray-100 text-gray-400 cursor-not-allowed border border-gray-200">
                                        {{ $state['label'] }}
                                    </button>
                                @else
                                    <a href="{{ route('registration.form', ['category' => $category->id]) }}"
                                        class="block w-full py-4 rounded-xl font-bold text-center text-sm uppercase tracking-widest transition-all duration-300
                                                            bg-gray-900 text-white hover:bg-gradient-to-r hover:from-fun-green hover:to-fun-teal hover:shadow-lg">
                                        {{ $state['label'] }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section: Why Join? (Static content for better impact) --}}
        <div id="about" class="py-24 bg-white relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="relative">
                        <div
                            class="absolute -top-10 -left-10 w-40 h-40 bg-fun-yellow rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-pulse">
                        </div>
                        <div
                            class="absolute -bottom-10 -right-10 w-40 h-40 bg-fun-teal rounded-full mix-blend-multiply filter blur-2xl opacity-30 animate-pulse">
                        </div>
                        <img src="{{ asset('assets/bg-hero.jpeg') }}" alt="Runners"
                            class="relative rounded-[2rem] shadow-2xl skew-y-3 transform hover:skew-y-0 transition-transform duration-700">
                    </div>
                    <div>
                        <h2 class="text-4xl font-black text-gray-900 mb-6 font-heading leading-tight">LEBIH DARI SEKADAR
                            <br><span class="text-fun-green">LARI MINGGU PAGI</span></h2>
                        <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                            Bergabunglah dengan ribuan pelari lainnya dalam event lari paling ditunggu tahun ini.
                            Rasakan atmosfer yang penuh semangat, rute yang menantang namun aman, dan perayaan gaya hidup sehat
                            bersama komunitas.
                        </p>

                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-full bg-fun-green/10 flex items-center justify-center text-fun-green mt-1">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xl font-bold text-gray-900">Rute Steril & Aman</h4>
                                    <p class="text-gray-500">Keamanan pelari adalah prioritas utama kami dengan pengawalan
                                        medis.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div
                                    class="flex-shrink-0 w-12 h-12 rounded-full bg-fun-teal/10 flex items-center justify-center text-fun-teal mt-1">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-xl font-bold text-gray-900">Medali Finisher Eksklusif</h4>
                                    <p class="text-gray-500">Desain medali unik dan jersey berkualitas tinggi untuk semua
                                        peserta.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        <div class="flex-grow flex flex-col items-center justify-center min-h-[60vh] py-20 bg-gray-50 text-center px-4">
            <div class="mb-8 relative group">
                <div
                    class="absolute inset-0 bg-fun-teal blur-2xl opacity-20 group-hover:opacity-30 transition-opacity duration-500 rounded-full">
                </div>
                <img src="{{ asset('assets/logo-event-1.png') }}" alt="UIGU Fun Run"
                    class="relative h-24 md:h-32 w-auto grayscale opacity-80 mb-6 mx-auto">
            </div>

            <div class="w-20 h-20 bg-gray-200/50 rounded-full flex items-center justify-center mb-6 animate-pulse mx-auto">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-800 mb-4 font-heading tracking-tight">Event Belum Tersedia</h1>
            <p class="text-gray-500 max-w-lg mx-auto text-lg leading-relaxed">
                Saat ini belum ada event lari yang aktif. <br>
                Ikuti media sosial kami untuk update informasi terbaru.
            </p>

            <div class="mt-8 flex gap-4 justify-center">
                <a href="https://www.instagram.com/uigu_funrun2026/" target="_blank"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full shadow-sm text-white bg-gradient-to-r from-fun-green to-fun-teal hover:from-green-500 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fun-green transition-all duration-300 transform hover:-translate-y-1">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </path>
                    </svg>
                    @uigu_funrun2026
                </a>
            </div>
        </div>
    @endif
@endsection