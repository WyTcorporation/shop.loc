<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('Conversation') }}</x-slot>
            <div class="space-y-4">
                @forelse($this->messages as $message)
                    <div class="rounded-xl border p-4 @class([
                        'bg-primary-50 border-primary-200' => $message['is_author'],
                        'bg-white border-gray-200' => ! $message['is_author'],
                    ])">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $message['user']->name ?? __('System') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($message['created_at'])->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-3 text-sm text-gray-700 whitespace-pre-line">
                            {{ $message['body'] }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('No messages yet.') }}</p>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('New message') }}</x-slot>
            <form wire:submit.prevent="send" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit">
                    {{ __('Send') }}
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>
</x-filament::page>
