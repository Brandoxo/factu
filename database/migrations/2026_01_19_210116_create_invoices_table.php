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
        if (!Schema::hasTable('invoices')) {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->bigInteger();
            $table->foreignId('fiscal_entity_id')->constrained('fiscal_entities');
            $table->string('reservation_id')->unique();
            $table->decimal('order_id', 15, 2)->unique();
            $table->string('facturama_id')->unique();
            $table->string('cfdi_uuid')->unique();
            $table->enum('status', ['draft', 'pending', 'stamped', 'cancelled']);
            $table->string('payment_form');
            $table->string('payment_method');
            $table->string('use_cfdi');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('pdf_path');
            $table->string('xml_path');
            $table->timestamps();
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
