@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50/50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                
                {{-- Header --}}
                <div class="bg-gradient-to-r from-fun-green to-fun-teal px-8 py-6 flex justify-between items-center relative overflow-hidden">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-heading font-bold text-white">Pemindai Tiket</h2>
                        <p class="text-fun-yellow text-sm font-medium">Volunteer Area</p>
                    </div>
                    
                    <div class="relative z-10">
                        <form action="{{ route('checkin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="flex items-center text-white/90 hover:text-white bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full transition-colors text-sm font-bold backdrop-blur-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Keluar
                            </button>
                        </form>
                    </div>

                    {{-- Decorative Shapes --}}
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-40 h-40 bg-fun-yellow/20 rounded-full blur-2xl"></div>
                </div>

                <div class="p-8">
                    {{-- Scanner Area --}}
                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Area Pindai QR</label>
                        <div class="rounded-2xl overflow-hidden bg-black shadow-inner ring-4 ring-gray-100 relative">
                             <div id="reader" style="width: 100%; min-height: 350px;"></div>
                             {{-- Overlay Guides would depend on the library styling, usually handled by JS --}}
                        </div>
                        <p class="text-center text-sm text-gray-500 mt-2">Arahkan kamera ke QR Code peserta.</p>
                    </div>

                    {{-- Manual Input --}}
                    <div class="mb-8 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Input Manual</label>
                        <div class="flex gap-3">
                            <input type="text" id="manual-input" placeholder="Masukkan Nomor Registrasi (Contoh: REG-123)"
                                class="flex-grow rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring focus:ring-fun-green/20 py-3 px-4 font-mono text-gray-800 uppercase placeholder-gray-400">
                            <button id="manual-btn"
                                class="bg-fun-teal hover:bg-teal-600 text-white px-6 py-3 rounded-xl font-bold shadow-md transition-transform transform active:scale-95">
                                Check-In
                            </button>
                        </div>
                    </div>

                    {{-- Result Area --}}
                    <div id="result-area" class="hidden animate-fade-in-up">
                        <div id="result-box" class="rounded-3xl p-8 text-center shadow-lg border transition-colors duration-300">
                            <div id="result-icon-container" class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 bg-white shadow-sm">
                                <span id="result-icon" class="text-4xl"></span>
                            </div>
                            <h3 id="result-title" class="text-2xl font-heading font-bold mb-2 uppercase tracking-wide"></h3>
                            <p id="result-message" class="text-lg font-medium opacity-90"></p>

                            {{-- Details --}}
                            <div id="result-details" class="mt-8 text-left bg-white/60 p-6 rounded-2xl hidden">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                                        <p class="text-xs text-gray-500 uppercase">Nomor Registrasi</p>
                                        <p class="text-lg font-mono font-bold text-gray-800" id="reg-number"></p>
                                    </div>
                                    <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm">
                                        <p class="text-xs text-gray-500 uppercase">Kategori</p>
                                        <p class="text-lg font-bold text-gray-800" id="reg-category"></p>
                                    </div>
                                </div>
                                
                                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                    <p class="text-xs text-gray-500 uppercase mb-2">Daftar Peserta</p>
                                    <ul id="participant-list" class="space-y-2"></ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- HTML5-QRCode Library --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Config
            const html5QrcodeScanner = new Html5QrcodeScanner(
                "reader",
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                /* verbose= */ false
            );

            function onScanSuccess(decodedText, decodedResult) {
                // Throttle helps prevent double scans
                processCheckin(decodedText);
            }

            function onScanFailure(error) {
                // Ignore errors
            }

            html5QrcodeScanner.render(onScanSuccess, onScanFailure);

            // Manual Input
            document.getElementById('manual-btn').addEventListener('click', function () {
                const code = document.getElementById('manual-input').value;
                if (code) processCheckin(code);
            });
            
            // Allow Enter key
            document.getElementById('manual-input').addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const code = document.getElementById('manual-input').value;
                    if (code) processCheckin(code);
                }
            });

            let isProcessing = false;

            function processCheckin(code) {
                if (isProcessing) return;
                isProcessing = true;
                
                // Visual feedback of loading
                const btn = document.getElementById('manual-btn');
                const originalBtnText = btn.innerText;
                btn.innerText = '...';
                btn.disabled = true;

                fetch('{{ route("checkin.verify") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ qr_content: code })
                })
                    .then(response => response.json())
                    .then(data => {
                        showResult(data.status === 'success', data.message, data);
                        // Reset processing after delay
                        setTimeout(() => { 
                            isProcessing = false; 
                            btn.innerText = originalBtnText;
                            btn.disabled = false;
                        }, 2000); 
                    })
                    .catch(error => {
                        showResult(false, 'Terjadi kesalahan jaringan.', null);
                        isProcessing = false;
                        btn.innerText = originalBtnText;
                        btn.disabled = false;
                    });
            }

            function showResult(isSuccess, message, data) {
                const area = document.getElementById('result-area');
                const box = document.getElementById('result-box');
                const icon = document.getElementById('result-icon');
                const title = document.getElementById('result-title');
                const msg = document.getElementById('result-message');
                const details = document.getElementById('result-details');
                const iconContainer = document.getElementById('result-icon-container');

                area.classList.remove('hidden');

                // Smooth scroll to result
                area.scrollIntoView({ behavior: 'smooth', block: 'center' });

                if (isSuccess) {
                    box.className = 'rounded-3xl p-8 text-center shadow-lg border border-green-200 bg-green-50 text-green-800';
                    iconContainer.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 bg-white text-green-500 shadow-md ring-4 ring-green-100';
                    icon.innerHTML = '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                    title.innerText = 'VERIFIKASI BERHASIL';
                    msg.className = "text-lg font-medium text-green-700";

                    // Show Details
                    details.classList.remove('hidden');
                    if(data.data) {
                        document.getElementById('reg-number').innerText = data.data.registration_number;
                        document.getElementById('reg-category').innerText = data.data.race_category ? data.data.race_category.name : '-';

                        const list = document.getElementById('participant-list');
                        list.innerHTML = '';
                        if(data.participants) {
                            data.participants.forEach(p => {
                                const li = document.createElement('li');
                                li.className = "flex justify-between items-center py-2 border-b border-gray-100 last:border-0";
                                li.innerHTML = `
                                    <span class="font-bold text-gray-800">${p.name}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-1 bg-gray-100 rounded text-xs text-gray-600 font-mono">${p.bib_number || 'NO BIB'}</span>
                                        <span class="w-6 h-6 bg-fun-teal text-white rounded-full flex items-center justify-center text-xs font-bold">${p.jersey_size}</span>
                                    </div>
                                `;
                                list.appendChild(li);
                            });
                        }
                    }

                } else {
                    box.className = 'rounded-3xl p-8 text-center shadow-lg border border-red-200 bg-red-50 text-red-800';
                    iconContainer.className = 'w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 bg-white text-red-500 shadow-md ring-4 ring-red-100';
                    icon.innerHTML = '<svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                    title.innerText = 'VERIFIKASI GAGAL';
                    msg.className = "text-lg font-medium text-red-700";
                    details.classList.add('hidden');
                }

                msg.innerText = message;
            }
        });
    </script>
@endsection
