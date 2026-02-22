<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->string('state', 100)->nullable();
            $table->string('mode', 20)->default('gross_to_net');
            $table->decimal('gross', 12, 2)->default(0);
            $table->decimal('net', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->integer('age')->default(31);
            $table->integer('children')->default(0);
            $table->decimal('tax_exemption_rate', 5, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
