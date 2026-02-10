@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $hasCollective5 = ! is_null($category->price_collective_5);
                $hasCollective10 = ! is_null($category->price_collective_10);
            @endphp

            {{-- Header --}}
            <div class="mb-12 text-center">
                <span class="text-fun-teal font-bold tracking-widest uppercase text-sm">Daftar Sekarang</span>
                <h1 class="text-3xl md:text-5xl font-black text-gray-900 mt-2 font-heading">{{ $category->event->name }}</h1>

                <div class="mt-6 flex flex-col items-center justify-center space-y-4">
                    <div class="inline-flex items-center px-6 py-3 rounded-full bg-white shadow-md border border-gray-100 text-gray-800 font-medium">
                        <div class="w-10 h-10 rounded-full bg-fun-green/10 flex items-center justify-center mr-3 text-fun-green">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="text-xs text-gray-400 uppercase tracking-wide font-bold">Kategori</div>
                            <div class="font-bold text-lg leading-none">
                                {{ $category->name }}
                                @if ($category->distance)
                                    <span class="text-gray-500 font-normal text-base">({{ $category->distance }})</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex flex-wrap justify-center gap-4 text-sm">
                    <div class="px-5 py-3 bg-white rounded-xl shadow-sm border border-gray-100 min-w-[140px]">
                        <span class="block text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Individu</span>
                        <span class="font-black text-xl text-gray-900">IDR {{ number_format($category->price_individual, 0, ',', '.') }}</span>
                    </div>
                    @if ($hasCollective5)
                        <div class="px-5 py-3 bg-white rounded-xl shadow-sm border border-gray-100 min-w-[140px]">
                            <span class="block text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Kolektif 5</span>
                            <span class="font-black text-xl text-gray-900">IDR {{ number_format($category->price_collective_5, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if ($hasCollective10)
                        <div class="px-5 py-3 bg-white rounded-xl shadow-sm border border-gray-100 min-w-[140px]">
                            <span class="block text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Kolektif 10</span>
                            <span class="font-black text-xl text-gray-900">IDR {{ number_format($category->price_collective_10, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-r-xl shadow-sm mb-8">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <strong class="font-bold">Mohon periksa input Anda</strong>
                    </div>
                    <ul class="list-disc list-inside text-sm ml-8 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-fun-green text-green-700 px-6 py-4 rounded-r-xl shadow-sm mb-8">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 mr-2 text-fun-green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <form action="{{ route('registration.store', $category) }}" method="POST"
                x-data="{
                    registrationType: @js(old('registration_type', 'individual')),
                    membersCount() {
                        if (this.registrationType === 'collective_10') {
                            return 9;
                        }

                        if (this.registrationType === 'collective_5') {
                            return 4;
                        }

                        return 0;
                    },
                }">
                @csrf
                <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hidden" />

                {{-- Registration Type --}}
                <div class="bg-white shadow-xl rounded-3xl mb-8 overflow-hidden border border-gray-100">
                    <div class="bg-gray-50 px-8 py-6 border-b border-gray-100 flex items-center">
                        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-fun-dark text-white font-bold text-lg mr-4 shadow-md">1</div>
                        <h2 class="text-xl font-bold text-gray-900 font-heading">Tipe Pendaftaran</h2>
                    </div>
                    <div class="p-8">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Pilih Tipe</label>
                        <select name="registration_type" x-model="registrationType" required
                            class="block w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green sm:text-base py-4 px-4 transition duration-200 bg-gray-50 focus:bg-white text-gray-800 font-medium">
                            <option value="individual">Individu - IDR {{ number_format($category->price_individual, 0, ',', '.') }}</option>
                            @if ($hasCollective5)
                                <option value="collective_5">Kolektif 5 - IDR {{ number_format($category->price_collective_5, 0, ',', '.') }}</option>
                            @endif
                            @if ($hasCollective10)
                                <option value="collective_10">Kolektif 10 - IDR {{ number_format($category->price_collective_10, 0, ',', '.') }}</option>
                            @endif
                        </select>
                        <p class="text-gray-400 text-xs mt-3 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Pilih jenis pendaftaran yang Anda inginkan.
                        </p>
                        @error('registration_type')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- PIC Information --}}
                <div class="bg-white shadow-xl rounded-3xl mb-8 overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-8 py-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                        <div class="flex items-center">
                            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-white/20 backdrop-blur-sm text-white font-bold text-lg mr-4 border border-white/20">2</div>
                            <h2 class="text-xl font-bold text-white font-heading">Informasi PIC</h2>
                        </div>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-white border border-white/20 backdrop-blur-xl">
                            Penanggung Jawab
                        </span>
                    </div>

                    <div class="bg-blue-50/50 px-8 py-4 border-b border-blue-100/50 flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-3 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-blue-700 text-sm font-medium">Instruksi pembayaran dan E-Ticket akan dikirimkan ke kontak PIC ini.</p>
                    </div>

                    <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nama Lengkap</label>
                            <input type="text" name="pic[full_name]" value="{{ old('pic.full_name') }}" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white placeholder-gray-400" placeholder="Sesuai KTP/Identitas">
                            @error('pic.full_name')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nama BIB</label>
                            <input type="text" name="pic[bib_name]" value="{{ old('pic.bib_name') }}" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white placeholder-gray-400" placeholder="Nama yang akan tertera di BIB">
                            <p class="text-gray-400 text-xs mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Nama yang akan dicetak pada nomor BIB
                            </p>
                            @error('pic.bib_name')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Alamat Email</label>
                            <input type="email" name="pic[email]" value="{{ old('pic.email') }}" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white placeholder-gray-400" placeholder="contoh@email.com">
                            @error('pic.email')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nomor WhatsApp</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                </div>
                                <input type="text" name="pic[whatsapp]" value="{{ old('pic.whatsapp') }}" required
                                    placeholder="08123456789"
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 pl-10 pr-4 transition duration-200 bg-gray-50 focus:bg-white placeholder-gray-400">
                            </div>
                            @error('pic.whatsapp')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Jenis Kelamin</label>
                            <select name="pic[gender]" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="male" @selected(old('pic.gender') === 'male')>Laki-laki</option>
                                <option value="female" @selected(old('pic.gender') === 'female')>Perempuan</option>
                            </select>
                            @error('pic.gender')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Tanggal Lahir</label>
                            <input type="date" name="pic[date_of_birth]" value="{{ old('pic.date_of_birth') }}" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white">
                            @error('pic.date_of_birth')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ukuran Jersey</label>
                            <div class="relative">
                                <select name="pic[jersey_size]" required
                                    class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white appearance-none">
                                    <option value="">Pilih Ukuran</option>
                                    @foreach ($jerseySizes as $size)
                                        <option value="{{ $size->code }}" @selected(old('pic.jersey_size') === $size->code)>
                                            {{ $size->name }} ({{ strtoupper($size->code) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-700">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                                </div>
                            </div>
                            @error('pic.jersey_size')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                             <div class="border-t border-gray-100 my-4"></div>
                             <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nomor Identitas / NIK</label>
                            <input type="text" name="pic[identity_number]" value="{{ old('pic.identity_number') }}" required
                                class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-green focus:ring-fun-green text-gray-800 font-medium py-3 px-4 transition duration-200 bg-gray-50 focus:bg-white placeholder-gray-400">
                            @error('pic.identity_number')
                                <span class="text-red-500 text-xs mt-1 block pl-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="px-8 pb-8 pt-2">
                         <div class="bg-red-50/50 rounded-2xl p-6 border border-red-100">
                            <label class="block text-sm font-bold text-red-700 mb-4 flex items-center uppercase tracking-wide">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Kontak Darurat
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                     <label class="block text-xs font-bold text-gray-500 mb-1">Nama Kontak</label>
                                    <input type="text" name="pic[emergency_contact_name]" value="{{ old('pic.emergency_contact_name') }}"
                                        required placeholder="Nama Kontak"
                                        class="w-full rounded-lg border-red-200 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm py-2.5 px-3 bg-white">
                                    @error('pic.emergency_contact_name')
                                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Nomor Telepon</label>
                                    <input type="text" name="pic[emergency_contact_phone]" value="{{ old('pic.emergency_contact_phone') }}"
                                        required placeholder="08..."
                                        class="w-full rounded-lg border-red-200 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm py-2.5 px-3 bg-white">
                                    @error('pic.emergency_contact_phone')
                                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 mb-1">Hubungan</label>
                                    <input type="text" name="pic[emergency_relation]" value="{{ old('pic.emergency_relation') }}"
                                        required placeholder="ex. Orang Tua, Saudara"
                                        class="w-full rounded-lg border-red-200 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm py-2.5 px-3 bg-white">
                                    @error('pic.emergency_relation')
                                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Members Section (Collective Only) --}}
                <div x-show="membersCount() > 0" x-cloak class="space-y-6">
                    <div class="flex items-center my-10 relative">
                         <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center w-full">
                            <span class="px-6 py-2 bg-gray-50 text-gray-500 font-bold text-sm uppercase tracking-wide border border-gray-200 rounded-full">Informasi Anggota Tim</span>
                        </div>
                    </div>

                    @for ($i = 0; $i < 9; $i++)
                        <div x-show="membersCount() > {{ $i }}" class="bg-white shadow-lg rounded-3xl overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl relative">
                            {{-- Number Badge --}}
                            <div class="absolute top-0 right-0 bg-fun-teal text-white text-xs font-bold px-3 py-1.5 rounded-bl-xl z-10">
                                Anggota {{ $i + 1 }}
                            </div>

                            <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-6 pt-10">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nama Lengkap</label>
                                    <input type="text" name="members[{{ $i }}][full_name]"
                                        value="{{ old("members.$i.full_name") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white" placeholder="Sesuai Identitas">
                                    @error("members.$i.full_name")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nama BIB</label>
                                    <input type="text" name="members[{{ $i }}][bib_name]"
                                        value="{{ old("members.$i.bib_name") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white" placeholder="Nama yang akan tertera di BIB">
                                    @error("members.$i.bib_name")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Email</label>
                                    <input type="email" name="members[{{ $i }}][email]"
                                        value="{{ old("members.$i.email") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white">
                                    @error("members.$i.email")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">WhatsApp</label>
                                    <input type="text" name="members[{{ $i }}][whatsapp]"
                                        value="{{ old("members.$i.whatsapp") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white">
                                    @error("members.$i.whatsapp")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Nomor Identitas / NIK</label>
                                    <input type="text" name="members[{{ $i }}][identity_number]"
                                        value="{{ old("members.$i.identity_number") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white" placeholder="Sesuai Identitas">
                                    @error("members.$i.identity_number")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Jenis Kelamin</label>
                                    <select name="members[{{ $i }}][gender]" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white">
                                        <option value="">Pilih...</option>
                                        <option value="male" @selected(old("members.$i.gender") === 'male')>Laki-laki</option>
                                        <option value="female" @selected(old("members.$i.gender") === 'female')>Perempuan</option>
                                    </select>
                                    @error("members.$i.gender")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Tanggal Lahir</label>
                                    <input type="date" name="members[{{ $i }}][date_of_birth]"
                                        value="{{ old("members.$i.date_of_birth") }}" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white">
                                    @error("members.$i.date_of_birth")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ukuran Jersey</label>
                                    <select name="members[{{ $i }}][jersey_size]" x-bind:required="membersCount() > {{ $i }}"
                                        x-bind:disabled="membersCount() <= {{ $i }}"
                                        class="w-full rounded-xl border-gray-200 shadow-sm focus:border-fun-teal focus:ring-fun-teal text-gray-800 py-2.5 px-4 bg-gray-50 focus:bg-white">
                                        <option value="">Pilih Ukuran</option>
                                        @foreach ($jerseySizes as $size)
                                            <option value="{{ $size->code }}" @selected(old("members.$i.jersey_size") === $size->code)>
                                                {{ $size->name }} ({{ strtoupper($size->code) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("members.$i.jersey_size")
                                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="md:col-span-2 border-t border-gray-100 pt-4 mt-2">
                                     <h4 class="text-xs font-bold text-red-700 mb-3 uppercase flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>
                                        Kontak Darurat
                                     </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <input type="text" name="members[{{ $i }}][emergency_contact_name]"
                                                value="{{ old("members.$i.emergency_contact_name") }}"
                                                x-bind:required="membersCount() > {{ $i }}" x-bind:disabled="membersCount() <= {{ $i }}"
                                                placeholder="Nama"
                                                class="w-full rounded-lg bg-red-50 border-red-100 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs py-2 px-3">
                                        </div>
                                        <div>
                                            <input type="text" name="members[{{ $i }}][emergency_contact_phone]"
                                                value="{{ old("members.$i.emergency_contact_phone") }}"
                                                x-bind:required="membersCount() > {{ $i }}" x-bind:disabled="membersCount() <= {{ $i }}"
                                                placeholder="No. Telp"
                                                class="w-full rounded-lg bg-red-50 border-red-100 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs py-2 px-3">
                                        </div>
                                        <div>
                                            <input type="text" name="members[{{ $i }}][emergency_relation]"
                                                value="{{ old("members.$i.emergency_relation") }}"
                                                x-bind:required="membersCount() > {{ $i }}" x-bind:disabled="membersCount() <= {{ $i }}"
                                                placeholder="Hubungan"
                                                class="w-full rounded-lg bg-red-50 border-red-100 shadow-sm focus:border-red-500 focus:ring-red-500 text-xs py-2 px-3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                <div class="flex justify-center mt-12 mb-20">
                    <button type="submit"
                        class="group relative inline-flex items-center justify-center px-10 py-4 text-lg font-bold text-white transition-all duration-200 bg-transparent font-heading rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-fun-green">
                        <span class="absolute inset-0 w-full h-full -mt-1 rounded-full opacity-30 bg-gradient-to-r from-fun-green to-fun-teal"></span>
                        <span class="absolute inset-0 w-full h-full mt-1 rounded-full opacity-30 bg-gradient-to-r from-fun-green to-fun-teal"></span>
                        <span class="relative w-full h-full flex items-center bg-gradient-to-r from-fun-green to-fun-teal rounded-full inset-0 px-10 py-4 group-hover:scale-105 transition-transform duration-300 shadow-lg">
                            <span>Lanjut Pembayaran</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
