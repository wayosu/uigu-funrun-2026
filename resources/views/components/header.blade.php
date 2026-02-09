<header class="bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center gap-3 group">
                    <img src="{{ asset('assets/logo-event-1.png') }}" class="h-10 w-auto group-hover:scale-105 transition-transform duration-300" alt="UIGU Fun Run">
                    {{-- <span class="font-heading font-black text-xl italic tracking-tighter text-gray-900">
                        UIGU <span class="text-transparent bg-clip-text bg-gradient-to-r from-fun-green to-fun-teal">FUN RUN</span>
                    </span> --}}
                </a>
            </div>

            <nav class="hidden md:flex items-center space-x-8">
                <a href="{{ url('/') }}"
                    class="text-gray-700 hover:text-fun-green px-3 py-2 rounded-lg text-sm font-bold uppercase tracking-wide transition-all hover:bg-gray-50">
                    Beranda
                </a>
                <a href="{{ url('/#categories') }}"
                    class="text-gray-700 hover:text-fun-green px-3 py-2 rounded-lg text-sm font-bold uppercase tracking-wide transition-all hover:bg-gray-50">
                    Kategori
                </a>
                <a href="{{ url('/#about') }}"
                    class="text-gray-700 hover:text-fun-green px-3 py-2 rounded-lg text-sm font-bold uppercase tracking-wide transition-all hover:bg-gray-50">
                    Tentang
                </a>
            </nav>

            <div class="flex items-center">
                <a href="{{ url('/#categories') }}"
                    class="bg-gradient-to-r from-fun-green to-fun-teal hover:from-green-500 hover:to-teal-500 text-white px-6 py-2.5 rounded-full text-sm font-black uppercase tracking-wider transition-all shadow-lg hover:shadow-fun-green/30 transform hover:-translate-y-0.5 active:scale-95">
                    Daftar Sekarang
                </a>
            </div>
        </div>
    </div>
</header>
