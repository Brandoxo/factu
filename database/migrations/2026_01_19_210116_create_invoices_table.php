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
            $table->id();
            $table->string('folio')->unique();
            $table->foreignId('fiscal_entity_id')->nullable()->constrained('fiscal_entities');
            $table->string('reservation_id');
            $table->decimal('order_id', 15, 2);
            $table->string('facturama_id')->nullable();
            $table->string('cfdi_uuid')->nullable()->unique();
            $table->enum('status', ['draft', 'pending', 'active', 'cancelled']);
            $table->string('payment_form')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('use_cfdi')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('pdf_path')->nullable();
            $table->string('xml_path')->nullable();
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
