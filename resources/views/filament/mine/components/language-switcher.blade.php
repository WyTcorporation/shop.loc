@php
    $supportedLocales = collect(config('app.supported_locales', []))
        ->map(fn ($locale) => \Illuminate\Support\Str::of((string) $locale)->lower()->replace('_', '-')->value())
        ->filter()
        ->unique()
        ->values();

    $currentLocale = \Illuminate\Support\Str::of(app()->getLocale())
        ->lower()
        ->replace('_', '-')
        ->value();

    $redirectTarget = request()->fullUrl();
@endphp

@if ($supportedLocales->count() > 1)
    <div class="fi-dropdown-list-item fi-dropdown-user-menu-item">
        <form
            method="POST"
            action="{{ route('mine.language.switch') }}"
            class="flex flex-col gap-1 px-3 py-2"
        >
            @csrf
            <input type="hidden" name="redirect" value="{{ $redirectTarget }}">

            <label for="fi-user-menu-language" class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ __('shop.admin.language_switcher.label') }}
            </label>

            <select
                id="fi-user-menu-language"
                name="locale"
                class="block w-full rounded-lg border border-gray-200 bg-white py-1.5 text-sm text-gray-900 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()"
            >
                @foreach ($supportedLocales as $locale)
                    @php($translationKey = 'shop.languages.' . $locale)
                    @php($label = __($translationKey))
                    <option value="{{ $locale }}" @selected($locale === $currentLocale)>
                        {{ $label === $translationKey ? strtoupper($locale) : $label }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
@endif
