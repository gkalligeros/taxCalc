<?php

namespace App\Http\Controllers;

use App\Models\TaxBracket;
use App\Models\TaxScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BracketController extends Controller
{
    public function store(Request $request, TaxScale $scale): RedirectResponse
    {
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $scale->brackets()->create($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Bracket added.');
    }

    public function update(Request $request, TaxScale $scale, TaxBracket $bracket): RedirectResponse
    {
        $validated = $request->validate([
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $bracket->update($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Bracket updated.');
    }

    public function destroy(TaxScale $scale, TaxBracket $bracket): RedirectResponse
    {
        $bracket->delete();

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Bracket deleted.');
    }
}
