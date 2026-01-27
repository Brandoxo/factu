<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'product_code_sat',
        'unit_code_sat',
        'description',
        'quantity',
        'unit_price',
        'sub_reservation_id',
    ];
}
