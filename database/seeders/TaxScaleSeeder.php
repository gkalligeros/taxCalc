<?php

namespace Database\Seeders;

use App\Models\TaxScale;
use Illuminate\Database\Seeder;

class TaxScaleSeeder extends Seeder
{
    public function run(): void
    {
        $scale = TaxScale::create([
            'name' => 'Greece 2026 Tax Scale',
            'country_code' => 'GR',
            'is_active' => true,
        ]);

        // Progressive tax brackets (Greece 2026 — Law 5246/2025)
        $brackets = [
            ['min_amount' => 0,     'max_amount' => 10000,  'rate' => 0.0900],
            ['min_amount' => 10000, 'max_amount' => 20000,  'rate' => 0.2000],
            ['min_amount' => 20000, 'max_amount' => 30000,  'rate' => 0.2600],
            ['min_amount' => 30000, 'max_amount' => 40000,  'rate' => 0.3400],
            ['min_amount' => 40000, 'max_amount' => 60000,  'rate' => 0.3900],
            ['min_amount' => 60000, 'max_amount' => null,   'rate' => 0.4400],
        ];

        $createdBrackets = [];
        foreach ($brackets as $bracket) {
            $createdBrackets[] = $scale->brackets()->create($bracket);
        }

        // Overrides for bracket 0-10k
        $createdBrackets[0]->overrides()->create([
            'min_children' => 4, 'max_children' => null,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.0000,
        ]);

        // Overrides for bracket 10k-20k
        $bracket10k = $createdBrackets[1];

        // Under 25, 0 children: 0%
        $bracket10k->overrides()->create([
            'min_children' => 0, 'max_children' => null,
            'min_age' => null, 'max_age' => 25,
            'rate' => 0.0000,
        ]);

        // Age 26-30, 0 children: 9%
        $bracket10k->overrides()->create([
            'min_children' => 0, 'max_children' => 0,
            'min_age' => 26, 'max_age' => 30,
            'rate' => 0.0900,
        ]);

        // 1 child, any age: 18%
        $bracket10k->overrides()->create([
            'min_children' => 1, 'max_children' => 1,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.1800,
        ]);

        // 2 children, any age: 16%
        $bracket10k->overrides()->create([
            'min_children' => 2, 'max_children' => 2,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.1600,
        ]);

        // 3 children, any age: 9%
        $bracket10k->overrides()->create([
            'min_children' => 3, 'max_children' => 3,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.0900,
        ]);

        // 4+ children, any age: 0%
        $bracket10k->overrides()->create([
            'min_children' => 4, 'max_children' => null,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.0000,
        ]);

        // Overrides for bracket 20k-30k
        $bracket20k = $createdBrackets[2];

        // 1 child: 24%
        $bracket20k->overrides()->create([
            'min_children' => 1, 'max_children' => 1,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.2400,
        ]);

        // 2 children: 22%
        $bracket20k->overrides()->create([
            'min_children' => 2, 'max_children' => 2,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.2200,
        ]);

        // 3 children: 20%
        $bracket20k->overrides()->create([
            'min_children' => 3, 'max_children' => 3,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.2000,
        ]);

        // 4+ children: 18%
        $bracket20k->overrides()->create([
            'min_children' => 4, 'max_children' => null,
            'min_age' => null, 'max_age' => null,
            'rate' => 0.1800,
        ]);

        // Deductions
        $scale->deductions()->create([
            'name' => 'Social Security (EFKA)',
            'rate' => 0.1337,
        ]);
    }
}
