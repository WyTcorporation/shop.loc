<x-filament::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">{{ __('shop.admin.resources.saft_exports.actions.export') }}</x-slot>
            <form wire:submit.prevent="submit" class="space-y-4">
                {{ $this->form }}

                <x-filament::button type="submit">
                    {{ __('shop.admin.resources.saft_exports.actions.run') }}
                </x-filament::button>
            </form>
        </x-filament::section>

        @if($this->latestLog)
            <x-filament::section>
                <x-slot name="heading">{{ __('shop.admin.resources.saft_exports.messages.latest_title') }}</x-slot>

                <div class="space-y-2 text-sm text-gray-700">
                    <div>
                        <span class="font-semibold">{{ __('shop.common.status') }}:</span>
                        <span>{{ $this->latestLog->status }}</span>
                    </div>
                    <div>
                        <span class="font-semibold">{{ __('shop.admin.resources.saft_exports.fields.format') }}:</span>
                        <span>{{ strtoupper($this->latestLog->format) }}</span>
                    </div>
                    @if($this->latestLog->message)
                        <div>
                            <span
                                class="font-semibold">{{ __('shop.admin.resources.saft_exports.fields.message') }}:</span>
                            <span>{{ $this->latestLog->message }}</span>
                        </div>
                    @endif
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <x-filament::button tag="a" color="gray"
                                        href="{{ \App\Filament\Mine\Resources\SoftExportLogs\SoftExportLogResource::getUrl('index') }}">
                        {{ __('shop.admin.resources.saft_exports.actions.view_logs') }}
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament::page>
