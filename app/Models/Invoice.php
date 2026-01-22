<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
   protected $table = 'invoices';

    protected $fillable = [
        'fiscal_entity_id',
        'reservation_id',
        'order_id',
        'facturama_id',
        'cfdi_uuid',
        'status',
        'payment_form',
        'payment_method',
        'use_cfdi',
        'subtotal',
        'total',
        'pdf_path',
        'xml_path',
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
