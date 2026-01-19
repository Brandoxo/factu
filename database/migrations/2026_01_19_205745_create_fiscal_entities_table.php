<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {   
        if (!Schema::hasTable('fiscal_entities')) {
        Schema::create('fiscal_entities', function (Blueprint $table) {
            $table->id()->bigInteger();
            $table->string('rfc')->unique();
            $table->string('legal_name');
            $table->string('tax_regime');
            $table->string('zip_code');
            $table->string('email');
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiscal_entities');
    }
};
