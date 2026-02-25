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
        Schema::create('pcbres_invoices', function (Blueprint $table) {
            $table->id();
            // 1. El enlace sagrado al sistema C#/PHP del restaurante
            $table->unsignedBigInteger('pos_order_id')->unique(); 
            
            // 2. IDs de los proveedores externos
            $table->string('facturama_id')->nullable();
            $table->string('cfdi_uuid')->nullable()->unique();
            
            // 3. La máquina de estados para la Cola (Queue)
            $table->enum('status', ['pending', 'processing', 'stamped', 'failed', 'cancelled'])->default('pending');
            
            // 4. Datos transaccionales rápidos (Para reportes del dueño)
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            
            // 5. El Snapshot Inmutable (Todo el XML resumido, datos del cliente, etc.)
            $table->json('stamped_fiscal_data');
            
            // 6. Los archivos que importan
            $table->string('pdf_path')->nullable();
            $table->string('xml_path')->nullable();
            
            // 7. La bitácora forense (obligatoria si usas Queues)
            $table->text('error_log')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
