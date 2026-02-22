<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\TaxScale;
use App\Services\PercentileService;
use App\Support\CurrencyHelper;
use App\Services\SalaryCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CalculatorController extends Controller
{
    public function index(Request $request): Response
    {
        $countryCode = $request->query('country_code', 'GR');
        $state = $request->query('state');

        $activeScale = TaxScale::getActive($countryCode, $state ?: null);

        return Inertia::render('Calculator', [
            'activeScale' => $activeScale,
            'selectedCountry' => $countryCode,
            'selectedState' => $state,
            'availableCountries' => TaxScale::getAvailableCountries(),
            'availableStates' => TaxScale::getAvailableStates($countryCode),
        ]);
    }

    public function calculate(Request $request, SalaryCalculator $calculator): JsonResponse
    {
        $validated = $request->validate([
            'mode' => 'nullable|in:gross_to_net,net_to_gross',
            'amount' => 'nullable|numeric|min:0',
            'gross' => 'nullable|numeric|min:0',
            'net' => 'nullable|numeric|min:0',
            'country_code' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'age' => 'integer|min:16|max:100',
            'children' => 'integer|min:0|max:20',
            'tax_exemption_rate' => 'nullable|numeric|min:0|max:1',
        ]);

        $mode = $validated['mode'] ?? 'gross_to_net';
        $age = $validated['age'] ?? 31;
        $children = $validated['children'] ?? 0;
        $countryCode = $validated['country_code'];
        $state = $validated['state'] ?? null;
        $taxExemptionRate = (float) ($validated['tax_exemption_rate'] ?? 0.0);

        if ($mode === 'net_to_gross') {
            if (! isset($validated['net']) && ! isset($validated['amount'])) {
                throw ValidationException::withMessages([
                    'net' => ['The net field is required when using net_to_gross mode.'],
                ]);
            }

            $targetNet = (float) ($validated['net'] ?? $validated['amount']);
            $result = $calculator->calculateFromNet(
                targetNet: $targetNet,
                countryCode: $countryCode,
                state: $state,
                age: $age,
                children: $children,
                taxExemptionRate: $taxExemptionRate,
            );
        } else {
            if (! isset($validated['gross']) && ! isset($validated['amount'])) {
                throw ValidationException::withMessages([
                    'gross' => ['The gross field is required when using gross_to_net mode.'],
                ]);
            }

            $gross = (float) ($validated['gross'] ?? $validated['amount']);
            $result = $calculator->calculate(
                gross: $gross,
                countryCode: $countryCode,
                state: $state,
                age: $age,
                children: $children,
                taxExemptionRate: $taxExemptionRate,
            );
        }

        // Collect live stats BEFORE saving so current calc isn't counted.
        $liveStats = $this->computeLiveStats($result['gross'], $countryCode);

        $currency = CurrencyHelper::forCountry($countryCode);

        Calculation::create([
            'country_code' => $countryCode,
            'currency' => $currency,
            'state' => $state,
            'mode' => $mode,
            'gross' => $result['gross'],
            'net' => $result['net'],
            'tax' => $result['tax'],
            'total_deductions' => $result['total_deductions'],
            'age' => $age,
            'children' => $children,
            'tax_exemption_rate' => $taxExemptionRate,
        ]);

        $percentile = PercentileService::compute($result['gross'], $countryCode);

        return response()->json([
            ...$result,
            'mode' => $mode,
            'currency' => $currency,
            ...$percentile,
            ...$liveStats,
        ]);
    }

    private function computeLiveStats(float $gross, string $countryCode): array
    {
        $totalCount   = Calculation::count();
        $countryCount = Calculation::where('country_code', $countryCode)->count();

        $totalPercentile = $totalCount > 0
            ? round(Calculation::where('gross', '<', $gross)->count() / $totalCount * 100, 1)
            : null;

        $countryPercentile = $countryCount > 0
            ? round(Calculation::where('country_code', $countryCode)->where('gross', '<', $gross)->count() / $countryCount * 100, 1)
            : null;

        $countryAvgGross = $countryCount > 0
            ? round((float) Calculation::where('country_code', $countryCode)->avg('gross'), 2)
            : null;

        $countryAvgNet = $countryCount > 0
            ? round((float) Calculation::where('country_code', $countryCode)->avg('net'), 2)
            : null;

        $totalAvgGross = $totalCount > 0
            ? round((float) Calculation::avg('gross'), 2)
            : null;

        $totalAvgNet = $totalCount > 0
            ? round((float) Calculation::avg('net'), 2)
            : null;

        return [
            'live_total_count'        => $totalCount,
            'live_country_count'      => $countryCount,
            'live_total_percentile'   => $totalPercentile,
            'live_country_percentile' => $countryPercentile,
            'live_country_avg_gross'  => $countryAvgGross,
            'live_country_avg_net'    => $countryAvgNet,
            'live_total_avg_gross'    => $totalAvgGross,
            'live_total_avg_net'      => $totalAvgNet,
        ];
    }
}
