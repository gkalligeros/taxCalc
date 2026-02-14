<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxBracket extends Model
{
    protected $fillable = ['tax_scale_id', 'min_amount', 'max_amount', 'rate'];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'rate' => 'decimal:4',
    ];

    public function taxScale(): BelongsTo
    {
        return $this->belongsTo(TaxScale::class);
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(TaxBracketOverride::class);
    }

    public function getRateFor(int $age, int $children): float
    {
        $matching = $this->overrides->filter(function (TaxBracketOverride $override) use ($age, $children) {
            if ($override->min_age !== null && $age < $override->min_age) {
                return false;
            }
            if ($override->max_age !== null && $age > $override->max_age) {
                return false;
            }
            if ($children < $override->min_children) {
                return false;
            }
            if ($override->max_children !== null && $children > $override->max_children) {
                return false;
            }
            return true;
        });

        if ($matching->isEmpty()) {
            return (float) $this->rate;
        }

        return (float) $matching->min('rate');
    }
}
