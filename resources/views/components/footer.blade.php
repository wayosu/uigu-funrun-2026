<footer class="bg-white border-t border-gray-100 mt-auto">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="md:flex md:justify-between items-start">
            <div class="mb-8 md:mb-0 text-center md:text-start space-y-4">
                <div class="flex justify-center md:justify-start items-center gap-4">
                    <img src="{{ asset('assets/logo-event-1.png') }}" class="h-12 w-auto filter hover:brightness-110 transition duration-300" alt="UIGU Fun Run">
                    <div class="h-8 w-px bg-gray-200"></div>
                    <img src="{{ asset('assets/logo-penyelenggara.png') }}" class="h-12 w-auto filter hover:brightness-110 transition duration-300" alt="UIGU Fun Run">
                    {{-- <span class="font-heading font-black text-xl italic tracking-tighter text-gray-900">
                        UIGU <span class="text-transparent bg-clip-text bg-gradient-to-r from-fun-green to-fun-teal">FUN RUN</span>
                    </span> --}}
                </div>
                <p class="text-gray-500 text-sm max-w-xs leading-relaxed font-medium mx-auto md:mx-0">
                    Langkah nyata untuk masa depan yang lebih sehat dan berkelanjutan.
                </p>
            </div>

            <div class="flex space-x-6 md:order-2 justify-center md:justify-start">
                <a href="https://www.instagram.com/uigu_funrun2026/" class="text-gray-400 hover:text-fun-green transition-colors">
                    <span class="sr-only">Instagram</span>
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465 1.067-.047 1.409-.06 3.809-.06h.63zm1.418 1.998l-3.562.003c-2.446 0-2.753.01-3.61.049-.887.04-1.362.195-1.68.32a2.91 2.91 0 00-1.077.7 2.91 2.91 0 00-.7 1.076c-.125.318-.28.794-.32 1.68-.039.857-.05 1.164-.05 3.61v.632c0 2.447.01 2.754.05 3.61.039.887.194 1.362.319 1.68a2.91 2.91 0 00.7 1.077 2.91 2.91 0 001.076.7c.318.125.794.28 1.68.32.857.039 1.164.05 3.61.05h.632c2.447 0 2.754-.01 3.61-.05.887-.039 1.362-.194 1.68-.319a2.91 2.91 0 001.077-.7 2.91 2.91 0 00.7-1.076c.125-.318.28-.794.32-1.68.039-.857.05-1.164.05-3.61v-.632c0-2.447-.01-2.754-.05-3.61-.039-.887-.194-1.362-.319-1.68a2.91 2.91 0 00-.7-1.077 2.91 2.91 0 00-1.076-.7c-.318-.125-.794-.28-1.68-.32-.857-.039-1.164-.05-3.61-.05h-.632zM12 7.051c2.733 0 4.949 2.216 4.949 4.949s-2.216 4.949-4.949 4.949S7.051 14.733 7.051 12 9.267 7.051 12 7.051zm0 2c-1.628 0-2.949 1.321-2.949 2.949S10.372 14.949 12 14.949s2.949-1.321 2.949-2.949S13.628 9.051 12 9.051zm5.284-2.836a1.06 1.06 0 110 2.12 1.06 1.06 0 010-2.12z" clip-rule="evenodd" /></svg>
                </a>
            </div>

        </div>
        <div class="mt-12 border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center text-sm text-gray-500 text-center md:text-start space-y-4 md:space-y-0">
            <p>
                &copy; {{ date('Y') }} Universitas Ichsan Gorontalo Utara. Hak cipta dilindungi undang-undang.
            </p>
            <p class="mt-2 md:mt-0 flex items-center">
                Powered by <span class="text-fun-green font-bold ml-1">Universitas Ichsan Gorontalo Utara</span>
            </p>
        </div>
    </div>
</footer>
