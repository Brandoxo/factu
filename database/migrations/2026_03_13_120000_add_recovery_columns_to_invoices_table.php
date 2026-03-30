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
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('requested_sub_reservation_ids')->nullable()->after('reservation_id');
            $table->json('facturama_response')->nullable()->after('facturama_id');
            $table->json('request_payload')->nullable()->after('xml_path');
            $table->text('last_error')->nullable()->after('request_payload');
            $table->timestamp('stamped_at')->nullable()->after('last_error');
            $table->index(['reservation_id', 'status'], 'invoices_reservation_status_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_reservation_status_index');
            $table->dropColumn([
                'requested_sub_reservation_ids',
                'facturama_response',
                'request_payload',
                'last_error',
                'stamped_at',
            ]);
        });
    }
};
