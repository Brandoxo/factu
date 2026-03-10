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
        Schema::create('pcbres_fiscal_entities', function (Blueprint $table) {
$table->id();
            $table->string('rfc', 13)->unique(); 
            
            $table->string('legal_name');
            $table->string('email');
            
            $table->string('zip_code', 5);
            $table->string('tax_regime', 3);
            $table->string('cfdi_use', 3);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pcbres_fiscal_entities');
    }
};
