<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_scales', function (Blueprint $table) {
            $table->string('country_code', 2)->default('GR')->after('name');
            $table->string('state', 100)->nullable()->after('country_code');
            $table->index(['country_code', 'state', 'is_active']);
        });

        Schema::create('tax_bracket_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_bracket_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('min_children')->unsigned()->default(0);
            $table->tinyInteger('max_children')->unsigned()->nullable();
            $table->tinyInteger('min_age')->unsigned()->nullable();
            $table->tinyInteger('max_age')->unsigned()->nullable();
            $table->decimal('rate', 5, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_bracket_overrides');

        Schema::table('tax_scales', function (Blueprint $table) {
            $table->dropIndex(['country_code', 'state', 'is_active']);
            $table->dropColumn(['country_code', 'state']);
        });
    }
};
