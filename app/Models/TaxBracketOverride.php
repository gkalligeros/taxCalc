<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxBracketOverride extends Model
{
    protected $fillable = [
        'tax_bracket_id',
        'min_children',
        'max_children',
        'min_age',
        'max_age',
        'rate',
    ];

    protected $casts = [
        'min_children' => 'integer',
        'max_children' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
        'rate' => 'decimal:4',
    ];

    public function taxBracket(): BelongsTo
    {
        return $this->belongsTo(TaxBracket::class);
    }
}
