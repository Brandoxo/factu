<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
   protected $table = 'invoices';

    protected $fillable = [
        'reservation_id',
        'order_id',
        'status',
        'cfdi_id',
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
