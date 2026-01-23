<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxStamp extends Model
{

    protected $fillable = [
        'invoice_id',
        'cfdi_sign',
        'rfc_prov_certif',
        'sat_cert_number',
        'sat_sign',
        'date_time',
    ];

    public $timestamps = false;
}
