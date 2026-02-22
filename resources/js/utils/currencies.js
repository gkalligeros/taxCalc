export const COUNTRY_CURRENCIES = {
    GR: 'EUR', DE: 'EUR', FR: 'EUR', IT: 'EUR', ES: 'EUR', PT: 'EUR',
    CY: 'EUR', NL: 'EUR', BE: 'EUR', AT: 'EUR', IE: 'EUR', FI: 'EUR', HR: 'EUR',
    US: 'USD', GB: 'GBP', SE: 'SEK', DK: 'DKK', NO: 'NOK',
    CH: 'CHF', PL: 'PLN', CZ: 'CZK', RO: 'RON', BG: 'BGN', HU: 'HUF',
};

export function currencyForCountry(countryCode) {
    return COUNTRY_CURRENCIES[countryCode] ?? 'EUR';
}

export function getCurrencySymbol(currency = 'EUR', locale = 'en') {
    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        })
            .formatToParts(0)
            .find(p => p.type === 'currency')?.value ?? currency;
    } catch {
        return currency;
    }
}

export function formatCurrencyValue(value, currency = 'EUR', locale = 'en') {
    try {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency,
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(Number(value));
    } catch {
        return Number(value).toLocaleString(locale, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}
