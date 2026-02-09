<div class="space-y-6">
    {{-- Payment Details Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Payment Details Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payment Details</h3>

            <div class="space-y-4">
                {{-- Registration Number --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Registration Number</label>
                    <div class="mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="text-base font-bold text-primary-600 dark:text-primary-400">{{ $record->registration->registration_number }}</span>
                    </div>
                </div>

                {{-- Race Category --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Race Category</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-success-100 text-success-800 dark:bg-success-800 dark:text-success-100">
                            {{ $record->registration->raceCategory->name }}
                        </span>
                    </div>
                </div>

                {{-- Payment Amount --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Amount</label>
                    <div class="mt-1">
                        <span class="text-2xl font-bold text-success-600 dark:text-success-400">
                            Rp {{ number_format($record->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Payment Status --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Status</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                              style="background-color: {{ $record->registration->status->color() }}20; color: {{ $record->registration->status->color() }}">
                            {{ $record->registration->status->label() }}
                        </span>
                    </div>
                </div>

                {{-- Uploaded At --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Uploaded At</label>
                    <div class="mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-base text-gray-900 dark:text-gray-100">
                            {{ $record->created_at->format('d M Y, H:i') }}
                        </span>
                    </div>
                </div>

                {{-- Verified At --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified At</label>
                    <div class="mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-base text-gray-900 dark:text-gray-100">
                            @if($record->verified_at)
                                {{ $record->verified_at->format('d M Y, H:i') }}
                            @else
                                <span class="text-gray-400">Not verified yet</span>
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Verified By --}}
                <div>
                    <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Verified By</label>
                    <div class="mt-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span class="text-base text-gray-900 dark:text-gray-100">
                            {{ $record->verifier?->name ?? '-' }}
                        </span>
                    </div>
                </div>

                {{-- Rejection Reason --}}
                @if($record->rejection_reason)
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <label class="text-sm font-medium text-danger-600 dark:text-danger-400">Rejection Reason</label>
                        <div class="mt-2 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg p-3">
                            <p class="text-sm text-danger-800 dark:text-danger-200">{{ $record->rejection_reason }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payment Proof Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payment Proof</h3>

            <div class="flex items-center justify-center bg-gray-50 dark:bg-gray-900 rounded-lg overflow-hidden" style="height: 400px;">
                @if($record->proof_path && Storage::disk('local')->exists($record->proof_path))
                    @php
                        $proofPath = Storage::disk('local')->path($record->proof_path);
                        $proofMime = mime_content_type($proofPath) ?: 'image/jpeg';
                        $proofBase64 = base64_encode(file_get_contents($proofPath));
                    @endphp
                    <img src="data:{{ $proofMime }};base64,{{ $proofBase64 }}"
                         alt="Payment Proof"
                         class="max-w-full max-h-full object-contain">
                @else
                    <div class="text-center text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <p class="text-sm">No image uploaded</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Participants Section --}}
    @if($record->registration->participants->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Participants</h3>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $record->registration->participants->count() }} participant(s)
                </span>
            </div>

            <div class="space-y-3">
                @foreach($record->registration->participants as $index => $participant)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <div class="shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900 text-primary-800 dark:text-primary-200 rounded-full flex items-center justify-center font-semibold text-sm">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $participant->name }}</span>
                                @if($participant->is_pic)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-800 dark:text-warning-100">
                                        ⭐ PIC
                                    </span>
                                @endif
                            </div>
                            @if($participant->bib_number)
                                <div class="flex items-center gap-1 mt-1">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">BIB:</span>
                                    <span class="text-sm font-mono font-semibold text-primary-600 dark:text-primary-400">{{ $participant->bib_number }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
