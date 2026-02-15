<?php

namespace App\Http\Controllers;

use App\Models\TaxScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScaleController extends Controller
{
    private const COUNTRY_CODES = [
        'GR', 'US', 'GB', 'DE', 'FR', 'IT', 'ES', 'PT', 'CY', 'NL', 'BE', 'AT',
        'IE', 'SE', 'DK', 'FI', 'NO', 'CH', 'PL', 'CZ', 'RO', 'BG', 'HR', 'HU',
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
            'availableCountries' => $this->availableCountries(),
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

        return redirect()->route('admin.scales.index')->with('success', __('messages.scale_created'));
    }

    public function show(TaxScale $scale): Response
    {
        $scale->load(['brackets.overrides', 'deductions']);

        return Inertia::render('Admin/Scales/Edit', [
            'scale' => $scale,
            'availableCountries' => $this->availableCountries(),
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

        return redirect()->route('admin.scales.show', $scale)->with('success', __('messages.scale_updated'));
    }

    public function destroy(TaxScale $scale): RedirectResponse
    {
        $scale->delete();

        return redirect()->route('admin.scales.index')->with('success', __('messages.scale_deleted'));
    }

    public function activate(TaxScale $scale): RedirectResponse
    {
        TaxScale::activateOnly($scale->id);

        return redirect()->route('admin.scales.index')->with(
            'success',
            __('messages.scale_activated', ['name' => $scale->name])
        );
    }

    private function availableCountries(): array
    {
        $countries = [];

        foreach (self::COUNTRY_CODES as $code) {
            $translationKey = "countries.$code";
            $translated = __($translationKey);
            $countries[$code] = $translated === $translationKey ? $code : $translated;
        }

        return $countries;
    }
}
