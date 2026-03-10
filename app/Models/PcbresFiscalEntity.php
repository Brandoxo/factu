<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PcbresFiscalEntity extends Model
{
    protected $table = 'pcbres_fiscal_entities';

    protected $fillable = [
        'rfc',
        'legal_name',
        'email',
        'zip_code',
        'tax_regime',
        'cfdi_use',
    ];
}
