<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalEntity extends Model
{
    protected $fillable = [
        'client_id',
        'legal_name',
        'rfc',
        'tax_regime',
        'zip_code',
    ];
    
}
