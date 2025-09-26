<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('shop.conversation.heading') }}</x-slot>
            <div class="space-y-4">
                @forelse($this->messages as $message)
                    <div class="rounded-xl border p-4 @class([
                        'bg-primary-50 border-primary-200' => $message['is_author'],
                        'bg-white border-gray-200' => ! $message['is_author'],
                    ])">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $message['user']->name ?? __('shop.conversation.system') }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($message['created_at'])->diffForHumans() }}
                                </p>
                            </div>
                            @if ($message['is_author'])
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium uppercase tracking-wide',
                                    'bg-emerald-100 text-emerald-700' => $message['is_read'],
                                    'bg-amber-100 text-amber-800' => ! $message['is_read'],
                                ])>
                                    @if ($message['is_read'])
                                        {{ __('shop.conversation.indicators.read', ['time' => optional($message['read_at'])->diffForHumans()]) }}
                                    @else
                                        {{ __('shop.conversation.indicators.awaiting_customer') }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div class="mt-3 text-sm text-gray-700 whitespace-pre-line">
                            {{ $message['body'] }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">{{ __('shop.conversation.empty') }}</p>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">{{ __('shop.conversation.new') }}</x-slot>
            <form wire:submit.prevent="send" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit">
                    {{ __('shop.conversation.send') }}
                </x-filament::button>
            </form>
        </x-filament::section>
    </div>
</x-filament::page>
