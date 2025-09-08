export function formatPrice(v: number | string, currency = 'EUR', locale = 'uk-UA') {
    const n = typeof v === 'string' ? Number(v) : v;
    return new Intl.NumberFormat(locale, { style: 'currency', currency, maximumFractionDigits: 2 }).format(n || 0);
}
