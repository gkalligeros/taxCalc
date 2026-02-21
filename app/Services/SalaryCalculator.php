<?php

namespace App\Services;

use App\Models\TaxScale;

class SalaryCalculator
{
    public function calculateFromNet(
        float $targetNet,
        ?TaxScale $scale = null,
        ?string $countryCode = null,
        ?string $state = null,
        int $age = 31,
        int $children = 0,
        float $taxExemptionRate = 0.0,
    ): array {
        $resolvedScale = $this->resolveScale($scale, $countryCode, $state);

        if (! $resolvedScale) {
            return $this->calculate(
                gross: $targetNet,
                scale: null,
                age: $age,
                children: $children,
                taxExemptionRate: $taxExemptionRate,
            );
        }

        $low = 0.0;
        $high = max($targetNet, 1.0);
        $best = $this->calculate(
            gross: $high,
            scale: $resolvedScale,
            age: $age,
            children: $children,
            taxExemptionRate: $taxExemptionRate,
        );

        // Expand search bounds until the high-end net reaches (or exceeds) target.
        for ($i = 0; $i < 60 && $best['net'] < $targetNet; $i++) {
            $high *= 2;
            $best = $this->calculate(
                gross: $high,
                scale: $resolvedScale,
                age: $age,
                children: $children,
                taxExemptionRate: $taxExemptionRate,
            );
        }

        for ($i = 0; $i < 80; $i++) {
            $mid = ($low + $high) / 2;
            $candidate = $this->calculate(
                gross: $mid,
                scale: $resolvedScale,
                age: $age,
                children: $children,
                taxExemptionRate: $taxExemptionRate,
            );

            if (abs($candidate['net'] - $targetNet) <= 0.01) {
                return $candidate;
            }

            if ($candidate['net'] < $targetNet) {
                $low = $mid;
            } else {
                $high = $mid;
                $best = $candidate;
            }
        }

        return $best;
    }

    public function calculate(
        float $gross,
        ?TaxScale $scale = null,
        ?string $countryCode = null,
        ?string $state = null,
        int $age = 31,
        int $children = 0,
        float $taxExemptionRate = 0.0,
    ): array {
        $scale = $this->resolveScale($scale, $countryCode, $state);

        if (!$scale) {
            return [
                'gross' => round($gross, 2),
                'total_deductions' => 0,
                'deductions_breakdown' => [],
                'taxable_income' => round($gross, 2),
                'tax_exemption_amount' => 0,
                'tax' => 0,
                'tax_breakdown' => [],
                'net' => round($gross, 2),
                'salaries_per_year' => 12,
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

        // Apply tax exemption (e.g. Article 5C — 50% exemption)
        $taxExemptionAmount = round($taxableIncome * $taxExemptionRate, 2);
        $taxableIncomeAfterExemption = round($taxableIncome - $taxExemptionAmount, 2);

        // Calculate progressive tax on the (possibly reduced) taxable income
        $taxBreakdown = [];
        $totalTax = 0;
        $remaining = $taxableIncomeAfterExemption;

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
            'tax_exemption_amount' => $taxExemptionAmount,
            'tax' => round($totalTax, 2),
            'tax_breakdown' => $taxBreakdown,
            'net' => $net,
            'salaries_per_year' => (int) ($scale->salaries_per_year ?? 12),
        ];
    }

    private function resolveScale(?TaxScale $scale, ?string $countryCode, ?string $state): ?TaxScale
    {
        if ($scale) {
            return $scale;
        }

        return $countryCode
            ? TaxScale::getActive($countryCode, $state)
            : TaxScale::where('is_active', true)->with(['brackets.overrides', 'deductions'])->first();
    }
}
