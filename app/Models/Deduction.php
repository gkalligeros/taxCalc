<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deduction extends Model
{
    protected $fillable = ['tax_scale_id', 'name', 'rate'];

    protected $casts = [
        'rate' => 'decimal:4',
    ];

    public function taxScale(): BelongsTo
    {
        return $this->belongsTo(TaxScale::class);
    }
}
