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
        if (!Schema::hasTable('invoice_items')) {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id()->bigInteger();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->string('product_code_sat');
            $table->string('unit_code_sat');
            $table->string('description');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->string('cloudbeds_transaction_id');
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
