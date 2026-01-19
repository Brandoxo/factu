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
    {   if (!Schema::hasTable('invoice_taxes')) {
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id()->bigInteger();
            $table->foreignId('invoice_item_id')->constrained('invoice_items');
            $table->enum('tax_type', ['IVA', 'ISH', 'ISR']);
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->boolean('retention');
            $table->timestamps();
        });
       }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
    }
};
