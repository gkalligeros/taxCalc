<?php

namespace App\Http\Controllers;

use App\Models\TaxScale;
use App\Services\SalaryCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            'gross' => 'required|numeric|min:0',
            'country_code' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
            'age' => 'integer|min:16|max:100',
            'children' => 'integer|min:0|max:20',
        ]);

        $result = $calculator->calculate(
            gross: (float) $validated['gross'],
            countryCode: $validated['country_code'],
            state: $validated['state'] ?? null,
            age: $validated['age'] ?? 31,
            children: $validated['children'] ?? 0,
        );

        return response()->json($result);
    }
}
