<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NON_EUR_CURRENCIES = [
        'US' => 'USD', 'GB' => 'GBP', 'SE' => 'SEK', 'DK' => 'DKK',
        'NO' => 'NOK', 'CH' => 'CHF', 'PL' => 'PLN', 'CZ' => 'CZK',
        'RO' => 'RON', 'BG' => 'BGN', 'HU' => 'HUF',
    ];

    public function up(): void
    {
        Schema::table('tax_scales', function (Blueprint $table) {
            $table->string('currency', 3)->default('EUR')->after('country_code');
        });

        Schema::table('calculations', function (Blueprint $table) {
            $table->string('currency', 3)->default('EUR')->after('country_code');
        });

        foreach (self::NON_EUR_CURRENCIES as $cc => $currency) {
            DB::table('tax_scales')->where('country_code', $cc)->update(['currency' => $currency]);
            DB::table('calculations')->where('country_code', $cc)->update(['currency' => $currency]);
        }
    }

    public function down(): void
    {
        Schema::table('tax_scales', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('calculations', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
