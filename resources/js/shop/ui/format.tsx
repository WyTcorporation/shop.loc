export function formatPrice(v: number | string, currency = 'EUR', locale = 'uk-UA') {
    const n = typeof v === 'string' ? Number(v) : v;
    return new Intl.NumberFormat(locale, { style: 'currency', currency, maximumFractionDigits: 2 }).format(n || 0);
}

type CurrencyFormatOptions = {
    currency?: string;
    locale?: string;
};

export function formatCurrency(value: number | string, { currency, locale }: CurrencyFormatOptions = {}) {
    return formatPrice(value, currency ?? 'EUR', locale ?? 'uk-UA');
}

type DateInput = Date | string | number | null | undefined;

type BaseDateFormatOptions = {
    locale?: string;
    fallback?: string;
    invalidFallback?: string;
};

type DateFormatOptions = BaseDateFormatOptions & Intl.DateTimeFormatOptions;

function normalizeDateInput(input: DateInput): Date | null {
    if (!input && input !== 0) {
        return null;
    }

    if (input instanceof Date) {
        return Number.isNaN(input.getTime()) ? null : input;
    }

    const date = new Date(input);
    return Number.isNaN(date.getTime()) ? null : date;
}

export function formatDate(value: DateInput, options: DateFormatOptions = {}) {
    const { locale = 'uk-UA', fallback = '—', invalidFallback, ...formatOptions } = options;
    const intlOptions = { year: 'numeric', month: 'long', day: 'numeric', ...formatOptions } satisfies Intl.DateTimeFormatOptions;
    const date = normalizeDateInput(value);

    if (!date) {
        if (value == null || value === '') {
            return fallback;
        }

        return invalidFallback ?? String(value);
    }

    return new Intl.DateTimeFormat(locale, intlOptions).format(date);
}

export function formatDateTime(value: DateInput, options: DateFormatOptions = {}) {
    return formatDate(value, {
        hour: '2-digit',
        minute: '2-digit',
        ...options,
    });
}
