@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header --}}
            <div class="text-center mb-12">
                <span class="text-fun-teal font-bold tracking-widest uppercase text-xs mb-2 block">Konfirmasi Pembayaran</span>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 font-heading">Selesaikan Pendaftaran Anda</h1>
                
                <div class="mt-8 flex flex-col items-center">
                    <div class="inline-flex flex-col items-center px-8 py-4 bg-white rounded-2xl shadow-sm border border-gray-100">
                        <span class="text-gray-400 text-xs font-bold uppercase tracking-wide mb-1">Nomor Registrasi</span>
                        <span class="font-mono text-3xl font-bold text-gray-800 tracking-wider">{{ $registration->registration_number }}</span>
                    </div>

                    <div class="mt-6">
                        <div class="inline-block relative group">
                            <div class="absolute inset-0 bg-fun-green blur-md opacity-20 group-hover:opacity-40 transition-opacity duration-500 rounded-xl"></div>
                            <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 text-white rounded-xl p-6 shadow-xl border border-gray-700">
                                 <p class="text-gray-400 font-medium text-xs uppercase tracking-wide mb-2">Total Pembayaran</p>
                                 <span class="text-4xl font-black text-fun-yellow tracking-tight">IDR {{ number_format($registration->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8">
                {{-- Payment Instructions --}}
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-fun-teal to-fun-green px-8 py-6 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white flex items-center font-heading">
                            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Metode Transfer
                        </h2>
                        <span class="bg-white/20 backdrop-blur-md text-white text-xs font-bold px-3 py-1 rounded-full border border-white/20">Aman & Terpercaya</span>
                    </div>
                    <div class="p-8 bg-gray-50/50">
                        @if ($paymentSettings->isEmpty())
                            <div class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada metode pembayaran. Silakan hubungi admin.</p>
                            </div>
                        @else
                            <div class="space-y-6">
                                @foreach ($paymentSettings as $setting)
                                    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-fun-green/5 -mr-8 -mt-8 rounded-full z-0 group-hover:bg-fun-green/10 transition-colors duration-500"></div>
                                        
                                        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                                            @if ($setting->qris_path)
                                                <div class="w-40 h-40 shrink-0 bg-white p-2 border border-gray-100 rounded-xl shadow-sm">
                                                    <img src="{{ Storage::url($setting->qris_path) }}" alt="QRIS"
                                                        class="w-full h-full object-contain">
                                                </div>
                                            @endif
                                            <div class="flex-1 text-center sm:text-left">
                                                <h3 class="font-bold text-xl text-gray-900 mb-1 font-heading">{{ $setting->bank_name }}</h3>
                                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">Nomor Rekening</p>
                                                
                                                <div class="flex items-center justify-center sm:justify-start gap-3 mb-4">
                                                    <p class="text-3xl font-mono font-bold text-fun-teal select-all tracking-tight">
                                                        {{ $setting->account_number }}</p>
                                                    <button onclick="navigator.clipboard.writeText('{{ $setting->account_number }}')" class="text-gray-400 hover:text-fun-green transition-colors p-2 rounded-full hover:bg-gray-50" title="Salin">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                            <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                
                                                <div class="inline-flex items-center bg-gray-50 rounded-lg px-4 py-2 border border-gray-100">
                                                    <p class="text-sm text-gray-500">Atas Nama: <span
                                                        class="font-bold text-gray-900">{{ $setting->account_name }}</span></p>
                                                </div>

                                                @if($setting->instructions)
                                                    <p class="mt-4 text-sm text-gray-500 italic border-l-4 border-fun-yellow pl-4 py-1 bg-yellow-50/50 rounded-r-lg">
                                                        {{ $setting->instructions }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Upload Proof --}}
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
                    <div class="bg-gray-900 px-8 py-6 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white flex items-center font-heading">
                            <svg class="w-6 h-6 mr-3 text-fun-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Upload Bukti Transfer
                        </h2>
                    </div>
                    <div class="p-8">
                        <form action="{{ route('payment.store', $registration) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="mb-8">
                                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Pilih Bank Tujuan <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="bank_account" required class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green py-3 px-4 appearance-none bg-gray-50 focus:bg-white transition-colors text-gray-800 font-medium">
                                        <option value="">Pilih rekening bank...</option>
                                        @foreach($paymentSettings as $setting)
                                            <option value="{{ $setting->id }}">
                                                {{ $setting->bank_name }} - {{ $setting->account_number }} ({{ $setting->account_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                    </div>
                                </div>
                                @error('bank_account') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <input type="hidden" name="payment_method" value="bank_transfer">

                            <div class="mb-8">
                                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Bukti Transfer (Gambar) <span class="text-red-500">*</span></label>
                                
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-xl hover:border-fun-green hover:bg-fun-green/5 transition-all duration-300 group cursor-pointer bg-gray-50">
                                    <div class="space-y-1 text-center relative w-full">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-fun-green transition-colors duration-300" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer rounded-md font-bold text-fun-green hover:text-fun-teal focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-fun-green px-1">
                                                <span>Upload file</span>
                                                <input id="file-upload" name="payment_proof" type="file" accept="image/*" required class="sr-only">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 font-medium">JPG, PNG, JPEG maksimal 2MB</p>
                                        <div id="file-name" class="text-sm text-gray-900 mt-3 font-bold bg-white inline-block px-3 py-1 rounded-full shadow-sm" style="display:none"></div>
                                    </div>
                                </div>
                                @error('payment_proof') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <script>
                                document.getElementById('file-upload').addEventListener('change', function(e) {
                                    var fileName = e.target.files[0] ? e.target.files[0].name : '';
                                    var fileNameEl = document.getElementById('file-name');
                                    if(fileName) {
                                        fileNameEl.textContent = 'Terpilih: ' + fileName;
                                        fileNameEl.style.display = 'inline-block';
                                    } else {
                                        fileNameEl.style.display = 'none';
                                    }
                                });
                            </script>

                            <div class="mb-8">
                                <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Catatan <span class="font-normal text-gray-400 text-xs text-transform-none ml-1">(Opsional)</span></label>
                                <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-base py-3 px-4 resize-none bg-gray-50 focus:bg-white" placeholder="Tambahkan catatan jika perlu..."></textarea>
                                @error('notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            @if($errors->has('error'))
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center">
                                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-red-600 text-sm font-bold">{{ $errors->first('error') }}</p>
                                </div>
                            @endif

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-gray-900 to-gray-800 text-white font-bold py-4 px-8 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:from-fun-green hover:to-fun-teal flex justify-center items-center group">
                                <span>Kirim Bukti Pembayaran</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
