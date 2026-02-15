<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_scales', function (Blueprint $table) {
            $table->unsignedTinyInteger('salaries_per_year')->default(12)->after('state');
        });

        DB::table('tax_scales')
            ->where('country_code', 'GR')
            ->update(['salaries_per_year' => 14]);
    }

    public function down(): void
    {
        Schema::table('tax_scales', function (Blueprint $table) {
            $table->dropColumn('salaries_per_year');
        });
    }
};
