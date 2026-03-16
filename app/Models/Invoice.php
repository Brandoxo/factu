<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
   protected $table = 'invoices';

    protected $fillable = [
        'folio',
        'fiscal_entity_id',
        'reservation_id',
        'requested_sub_reservation_ids',
        'order_id',
        'facturama_id',
        'facturama_response',
        'cfdi_uuid',
        'status',
        'payment_form',
        'payment_method',
        'use_cfdi',
        'subtotal',
        'total',
        'pdf_path',
        'xml_path',
        'request_payload',
        'last_error',
        'stamped_at',
    ];

    protected $casts = [
        'requested_sub_reservation_ids' => 'array',
        'facturama_response' => 'array',
        'request_payload' => 'array',
        'stamped_at' => 'datetime',
    ];

    public function items()
    {
        return $this->HasMany(InvoiceItem::class, 'invoice_id', 'id');
    }

    public function taxes()
    {
        return $this->HasMany(InvoiceTax::class, 'invoice_id', 'id');
    }
}
