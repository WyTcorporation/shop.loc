<x-filament::page>
    <div class="space-y-6">
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700">
            <button
                type="button"
                wire:click="$set('activeTab', 'form')"
                @class([
                    'px-3 py-2 text-sm font-medium border-b-2 transition',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'form',
                    'border-transparent text-gray-600 dark:text-gray-300 hover:text-gray-900' => $activeTab !== 'form',
                ])
            >
                {{ __('shop.admin.resources.products.imports.tabs.form') }}
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'history')"
                @class([
                    'px-3 py-2 text-sm font-medium border-b-2 transition',
                    'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === 'history',
                    'border-transparent text-gray-600 dark:text-gray-300 hover:text-gray-900' => $activeTab !== 'history',
                ])
            >
                {{ __('shop.admin.resources.products.imports.tabs.history') }}
            </button>
        </div>

        @if ($activeTab === 'form')
            <x-filament::section>
                <x-slot name="heading">{{ __('shop.admin.resources.products.imports.form.heading') }}</x-slot>

                <form wire:submit.prevent="submit" class="space-y-4">
                    {{ $this->form }}

                    <x-filament::button type="submit">
                        {{ __('shop.admin.resources.products.imports.form.actions.queue') }}
                    </x-filament::button>
                </form>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">{{ __('shop.admin.resources.products.imports.table.recent_imports') }}</x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.file') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.status') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.progress') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.results') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.completed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($recentImports as $import)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $import->original_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $import->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $color = match ($import->status) {
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'processing' => 'warning',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <x-filament::badge :color="$color">
                                            {{ ucfirst($import->status) }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        {{ $import->processed_rows }} / {{ $import->total_rows }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        <div>{{ __('shop.admin.resources.products.imports.table.results_created') }}: {{ $import->created_rows }}</div>
                                        <div>{{ __('shop.admin.resources.products.imports.table.results_updated') }}: {{ $import->updated_rows }}</div>
                                        <div>{{ __('shop.admin.resources.products.imports.table.results_failed') }}: {{ $import->failed_rows }}</div>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        {{ $import->completed_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('shop.admin.resources.products.imports.table.empty_imports') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">{{ __('shop.admin.resources.products.imports.table.recent_exports') }}</x-slot>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.format') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.status') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.rows') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600 dark:text-gray-300">{{ __('shop.admin.resources.products.imports.table.columns.completed_at') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($recentExports as $export)
                                <tr>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ strtoupper($export->format) }}</div>
                                        <div class="text-xs text-gray-500">{{ $export->created_at?->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-3 py-2">
                                        @php
                                            $color = match ($export->status) {
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                'processing' => 'warning',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <x-filament::badge :color="$color">
                                            {{ ucfirst($export->status) }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        {{ $export->processed_rows }} / {{ $export->total_rows }}
                                    </td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">
                                        {{ $export->completed_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">
                                        {{ __('shop.admin.resources.products.imports.table.empty_exports') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament::page>
