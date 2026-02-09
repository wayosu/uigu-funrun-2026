@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        {{-- Logo --}}
        <div class="text-center group mb-8">
            <span class="font-heading font-black text-3xl italic tracking-tighter text-gray-900 inline-block transform group-hover:scale-105 transition-transform duration-300">
                UIGU <span class="text-transparent bg-clip-text bg-gradient-to-r from-fun-green to-fun-teal">FUN RUN</span>
            </span>
        </div>

        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 font-heading">
            Akses Volunteer
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Masukkan Kode PIN untuk mengakses sistem check-in
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100">
            <form class="space-y-6" action="{{ route('checkin.authenticate') }}" method="POST">
                @csrf

                <div>
                    <label for="pin" class="block text-sm font-bold text-gray-700 mb-2">Kode PIN</label>
                    <div class="mt-1">
                        <input id="pin" name="pin" type="password" required autofocus
                            class="appearance-none block w-full px-3 py-4 border border-gray-300 rounded-xl placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-fun-green focus:border-transparent text-center text-3xl tracking-[1em] font-bold text-gray-900 transition-all"
                            placeholder="••••">
                    </div>
                    @error('pin')
                        <p class="mt-2 text-sm text-red-600 flex items-center justify-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-fun-green to-fun-teal hover:from-green-500 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fun-green shadow-lg hover:shadow-fun-green/30 transform transition-all duration-200 hover:-translate-y-1">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-green-100 group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Masuk Sistem
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ url('/') }}" class="font-medium text-fun-teal hover:text-fun-green transition-colors text-sm">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
