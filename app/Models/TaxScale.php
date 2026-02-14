<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxScale extends Model
{
    protected $fillable = ['name', 'country_code', 'state', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function brackets(): HasMany
    {
        return $this->hasMany(TaxBracket::class)->orderBy('min_amount');
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(Deduction::class);
    }

    public static function activateOnly(int $id): void
    {
        $scale = static::findOrFail($id);

        static::where('country_code', $scale->country_code)
            ->where(function ($q) use ($scale) {
                if ($scale->state === null) {
                    $q->whereNull('state');
                } else {
                    $q->where('state', $scale->state);
                }
            })
            ->where('is_active', true)
            ->update(['is_active' => false]);

        static::where('id', $id)->update(['is_active' => true]);
    }

    public static function getActive(string $countryCode, ?string $state = null): ?self
    {
        return static::where('country_code', $countryCode)
            ->where(function ($q) use ($state) {
                if ($state === null) {
                    $q->whereNull('state');
                } else {
                    $q->where('state', $state);
                }
            })
            ->where('is_active', true)
            ->with(['brackets.overrides', 'deductions'])
            ->first();
    }

    public static function getAvailableCountries(): array
    {
        return static::select('country_code')
            ->distinct()
            ->orderBy('country_code')
            ->pluck('country_code')
            ->toArray();
    }

    public static function getAvailableStates(string $countryCode): array
    {
        return static::where('country_code', $countryCode)
            ->whereNotNull('state')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state')
            ->toArray();
    }
}
