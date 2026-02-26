<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcbresInvoice extends Model
{
    protected $fillable = [
        'pos_order_id',
        'facturama_id',
        'cfdi_uuid',
        'status',
        'subtotal',
        'total',
        'stamped_fiscal_data',
        'pdf_path',
        'xml_path',
        'error_log'
    ];

    protected $casts = [
        'stamped_fiscal_data' => 'array',
    ];
}
