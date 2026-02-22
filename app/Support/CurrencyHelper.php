<?php

namespace App\Support;

class CurrencyHelper
{
    const COUNTRY_CURRENCIES = [
        'GR' => 'EUR', 'DE' => 'EUR', 'FR' => 'EUR', 'IT' => 'EUR',
        'ES' => 'EUR', 'PT' => 'EUR', 'CY' => 'EUR', 'NL' => 'EUR',
        'BE' => 'EUR', 'AT' => 'EUR', 'IE' => 'EUR', 'FI' => 'EUR',
        'HR' => 'EUR', 'US' => 'USD', 'GB' => 'GBP', 'SE' => 'SEK',
        'DK' => 'DKK', 'NO' => 'NOK', 'CH' => 'CHF', 'PL' => 'PLN',
        'CZ' => 'CZK', 'RO' => 'RON', 'BG' => 'BGN', 'HU' => 'HUF',
    ];

    public static function forCountry(string $countryCode): string
    {
        return self::COUNTRY_CURRENCIES[$countryCode] ?? 'EUR';
    }
}
