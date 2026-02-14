<?php

namespace App\Services;

use App\Models\TaxScale;

class SalaryCalculator
{
    public function calculate(
        float $gross,
        ?TaxScale $scale = null,
        ?string $countryCode = null,
        ?string $state = null,
        int $age = 31,
        int $children = 0,
    ): array {
        if (!$scale) {
            $scale = $countryCode
                ? TaxScale::getActive($countryCode, $state)
                : TaxScale::where('is_active', true)->with(['brackets.overrides', 'deductions'])->first();
        }

        if (!$scale) {
            return [
                'gross' => round($gross, 2),
                'total_deductions' => 0,
                'deductions_breakdown' => [],
                'taxable_income' => round($gross, 2),
                'tax' => 0,
                'tax_breakdown' => [],
                'net' => round($gross, 2),
            ];
        }

        $scale->loadMissing(['brackets.overrides', 'deductions']);

        // Calculate deductions
        $deductionsBreakdown = [];
        $totalDeductions = 0;

        foreach ($scale->deductions as $deduction) {
            $amount = round($gross * (float) $deduction->rate, 2);
            $totalDeductions += $amount;
            $deductionsBreakdown[] = [
                'name' => $deduction->name,
                'rate' => (float) $deduction->rate,
                'amount' => $amount,
            ];
        }

        $taxableIncome = round($gross - $totalDeductions, 2);

        // Calculate progressive tax
        $taxBreakdown = [];
        $totalTax = 0;
        $remaining = $taxableIncome;

        foreach ($scale->brackets as $bracket) {
            if ($remaining <= 0) {
                break;
            }

            $bracketMin = (float) $bracket->min_amount;
            $bracketMax = $bracket->max_amount !== null ? (float) $bracket->max_amount : null;
            $baseRate = (float) $bracket->rate;
            $effectiveRate = $bracket->getRateFor($age, $children);

            $bracketWidth = $bracketMax !== null ? ($bracketMax - $bracketMin) : $remaining;
            $taxableInBracket = min($remaining, $bracketWidth);

            $taxForBracket = round($taxableInBracket * $effectiveRate, 2);
            $totalTax += $taxForBracket;

            $taxBreakdown[] = [
                'min' => $bracketMin,
                'max' => $bracketMax,
                'base_rate' => $baseRate,
                'rate' => $effectiveRate,
                'taxable_amount' => round($taxableInBracket, 2),
                'tax' => $taxForBracket,
            ];

            $remaining -= $taxableInBracket;
        }

        $net = round($taxableIncome - $totalTax, 2);

        return [
            'gross' => round($gross, 2),
            'total_deductions' => round($totalDeductions, 2),
            'deductions_breakdown' => $deductionsBreakdown,
            'taxable_income' => $taxableIncome,
            'tax' => round($totalTax, 2),
            'tax_breakdown' => $taxBreakdown,
            'net' => $net,
        ];
    }
}
