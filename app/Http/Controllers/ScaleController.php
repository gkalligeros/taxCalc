<?php

namespace App\Http\Controllers;

use App\Models\TaxScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScaleController extends Controller
{
    private const COUNTRY_MAP = [
        'GR' => 'Greece',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'PT' => 'Portugal',
        'CY' => 'Cyprus',
        'NL' => 'Netherlands',
        'BE' => 'Belgium',
        'AT' => 'Austria',
        'IE' => 'Ireland',
        'SE' => 'Sweden',
        'DK' => 'Denmark',
        'FI' => 'Finland',
        'NO' => 'Norway',
        'CH' => 'Switzerland',
        'PL' => 'Poland',
        'CZ' => 'Czech Republic',
        'RO' => 'Romania',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'HU' => 'Hungary',
    ];

    public function index(): Response
    {
        return Inertia::render('Admin/Scales/Index', [
            'scales' => TaxScale::withCount(['brackets', 'deductions'])
                ->orderBy('country_code')
                ->orderBy('state')
                ->orderByDesc('is_active')
                ->orderByDesc('updated_at')
                ->get(),
            'availableCountries' => self::COUNTRY_MAP,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
        ]);

        TaxScale::create($validated);

        return redirect()->route('admin.scales.index')->with('success', 'Scale created.');
    }

    public function show(TaxScale $scale): Response
    {
        $scale->load(['brackets.overrides', 'deductions']);

        return Inertia::render('Admin/Scales/Edit', [
            'scale' => $scale,
            'availableCountries' => self::COUNTRY_MAP,
        ]);
    }

    public function update(Request $request, TaxScale $scale): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'required|string|size:2',
            'state' => 'nullable|string|max:100',
        ]);

        $scale->update($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Scale updated.');
    }

    public function destroy(TaxScale $scale): RedirectResponse
    {
        $scale->delete();

        return redirect()->route('admin.scales.index')->with('success', 'Scale deleted.');
    }

    public function activate(TaxScale $scale): RedirectResponse
    {
        TaxScale::activateOnly($scale->id);

        return redirect()->route('admin.scales.index')->with('success', "'{$scale->name}' is now the active scale.");
    }
}
