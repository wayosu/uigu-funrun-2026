@extends('layouts.app')

@section('content')
    <div class="flex-grow flex flex-col items-center justify-center min-h-[70vh] text-center px-4 py-16 relative overflow-hidden">
        {{-- Background Elements --}}
        <div class="absolute inset-0 pointer-events-none opacity-50">
             <div class="absolute top-[10%] left-[10%] w-64 h-64 bg-fun-green/10 rounded-full blur-3xl animate-blob"></div>
             <div class="absolute bottom-[10%] right-[10%] w-64 h-64 bg-fun-teal/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
             <div class="absolute bottom-[20%] left-[20%] w-64 h-64 bg-fun-yellow/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative z-10">
            <h1 class="text-[10rem] md:text-[14rem] font-black text-gray-100 leading-none select-none tracking-tighter">
                @yield('code')
            </h1>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-3xl md:text-5xl font-extrabold text-gray-800 bg-white/80 px-6 py-2 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50">
                     @yield('message')
                </span>
            </div>
        </div>

        <p class="text-gray-500 mt-8 max-w-md mx-auto text-lg">
            @yield('description', 'Maaf, sepertinya ada kesalahan atau halaman yang Anda cari tidak dapat ditemukan.')
        </p>

        <div class="mt-12">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 bg-fun-green text-white px-8 py-4 rounded-full font-bold shadow-lg shadow-fun-green/30 hover:bg-fun-green/90 hover:scale-105 transition-all duration-300 transform active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
@endsection
