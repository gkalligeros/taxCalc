<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    protected $fillable = [
        'country_code',
        'currency',
        'state',
        'mode',
        'gross',
        'net',
        'tax',
        'total_deductions',
        'age',
        'children',
        'tax_exemption_rate',
    ];

    protected $casts = [
        'gross' => 'float',
        'net' => 'float',
        'tax' => 'float',
        'total_deductions' => 'float',
        'age' => 'integer',
        'children' => 'integer',
        'tax_exemption_rate' => 'float',
    ];
}
