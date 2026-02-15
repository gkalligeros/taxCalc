<?php

namespace App\Http\Controllers;

use App\Models\TaxScale;
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
        ]);

        $mode = $validated['mode'] ?? 'gross_to_net';
        $age = $validated['age'] ?? 31;
        $children = $validated['children'] ?? 0;
        $countryCode = $validated['country_code'];
        $state = $validated['state'] ?? null;

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
            );
        }

        return response()->json([
            ...$result,
            'mode' => $mode,
        ]);
    }
}
