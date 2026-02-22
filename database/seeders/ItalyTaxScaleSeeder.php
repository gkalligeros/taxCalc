<?php

namespace Database\Seeders;

use App\Models\TaxScale;
use Illuminate\Database\Seeder;

class ItalyTaxScaleSeeder extends Seeder
{
    /**
     * Italy 2024 IRPEF tax scale seeded in three variants:
     *   – 12 salaries (standard monthly, uncommon in Italy)
     *   – 13 salaries / Tredicesima (most common; 13th paid in December)
     *   – 14 salaries / Quattordicesima (commerce, tourism, some services; 14th paid in June/July)
     *
     * IRPEF brackets (Legge di Bilancio 2024 — 3-bracket reform):
     *   €0 – €28,000 : 23%
     *   €28,001 – €50,000 : 35%
     *   > €50,000 : 43%
     *
     * Employee INPS contribution:
     *   9.19% (IVS) + 0.30% (supplementary) = 9.49% — standard private-sector rate
     *
     * Note: Italian detrazioni d'imposta (employment income tax credits) are
     * income-dependent credits that reduce the tax owed, not the taxable base.
     * They cannot be expressed as a flat deduction rate and are not modelled here,
     * so the calculated net will be slightly lower than the actual take-home pay.
     *
     * Tax Exemption: "Impatriati" regime (D.Lgs. 209/2023) — 50% exemption for
     * workers who transfer their tax residence to Italy for at least 5 years.
     *
     */
    public function run(): void
    {
        $brackets = [
            ['min_amount' => 0,      'max_amount' => 28000, 'rate' => 0.2300],
            ['min_amount' => 28000,  'max_amount' => 50000, 'rate' => 0.3500],
            ['min_amount' => 50000,  'max_amount' => null,  'rate' => 0.4300],
        ];

        $deductions = [
            ['name' => 'INPS (Social Security)', 'rate' => 0.0949],
        ];

        $exemptions = [
            [
                'name'        => '50% Impatriati Exemption',
                'description' => '50% income tax exemption for workers who transfer tax residence to Italy for ≥5 years (D.Lgs. 209/2023).',
                'rate'        => 0.5000,
            ],
        ];

        $scales = [
            [
                'name'             => 'Italy 2024 — 12 Salaries',
                'salaries_per_year' => 12,
                'is_active'        => false,
            ],
            [
                'name'             => 'Italy 2024 — 13 Salaries (Tredicesima)',
                'salaries_per_year' => 13,
                'is_active'        => true,
            ],
            [
                'name'             => 'Italy 2024 — 14 Salaries (Quattordicesima)',
                'salaries_per_year' => 14,
                'is_active'        => false,
            ],
        ];

        foreach ($scales as $scaleData) {
            $scale = TaxScale::create([
                'name'             => $scaleData['name'],
                'country_code'     => 'IT',
                'currency'         => 'EUR',
                'salaries_per_year' => $scaleData['salaries_per_year'],
                'is_active'        => $scaleData['is_active'],
            ]);

            foreach ($brackets as $bracket) {
                $scale->brackets()->create($bracket);
            }

            foreach ($deductions as $deduction) {
                $scale->deductions()->create($deduction);
            }

            foreach ($exemptions as $exemption) {
                $scale->taxExemptions()->create($exemption);
            }
        }
    }
}
