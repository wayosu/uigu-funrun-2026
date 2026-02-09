<x-filament::section>
    <x-slot name="heading">
        Notification Details
    </x-slot>

    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Channel</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                <span class="inline-flex items-center gap-1">
                    @if($record->channel === 'whatsapp')
                        <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-green-500" />
                        WhatsApp
                    @elseif($record->channel === 'email')
                        <x-heroicon-o-envelope class="w-4 h-4 text-blue-500" />
                        Email
                    @else
                        <x-heroicon-o-bell class="w-4 h-4" />
                        {{ ucfirst($record->channel) }}
                    @endif
                </span>
            </dd>
        </div>

        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
            <dd class="mt-1">
                <span @class([
                    'inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full',
                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' => $record->status === 'sent',
                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' => $record->status === 'failed',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' => $record->status === 'pending',
                ])>
                    @if($record->status === 'sent')
                        <x-heroicon-o-check-circle class="w-3 h-3" />
                        Sent
                    @elseif($record->status === 'failed')
                        <x-heroicon-o-x-circle class="w-3 h-3" />
                        Failed
                    @elseif($record->status === 'pending')
                        <x-heroicon-o-clock class="w-3 h-3" />
                        Pending
                    @else
                        {{ ucfirst($record->status) }}
                    @endif
                </span>
            </dd>
        </div>

        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Recipient</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $record->recipient }}</dd>
        </div>

        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sent At</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->sent_at ? $record->sent_at->format('d M Y H:i:s') : 'Not sent yet' }}
            </dd>
        </div>

        @if($record->registration)
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Registration</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->registration->registration_number }}
            </dd>
        </div>
        @endif

        @if($record->participant)
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Participant</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->participant->full_name }}
            </dd>
        </div>
        @endif

        @if($record->failed_reason)
        <div class="sm:col-span-2">
            <dt class="text-sm font-medium text-red-500 dark:text-red-400">Error Reason</dt>
            <dd class="mt-1 text-sm text-red-900 dark:text-red-100 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                {{ $record->failed_reason }}
            </dd>
        </div>
        @endif

        <div class="sm:col-span-2">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Message</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg whitespace-pre-wrap">{{ $record->message }}</dd>
        </div>

        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->created_at->format('d M Y H:i:s') }}
            </dd>
        </div>

        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Updated At</dt>
            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->updated_at->format('d M Y H:i:s') }}
            </dd>
        </div>
    </dl>
</x-filament::section>
