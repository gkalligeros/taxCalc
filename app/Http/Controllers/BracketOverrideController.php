<?php

namespace App\Http\Controllers;

use App\Models\TaxBracket;
use App\Models\TaxBracketOverride;
use App\Models\TaxScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BracketOverrideController extends Controller
{
    public function store(Request $request, TaxScale $scale, TaxBracket $bracket): RedirectResponse
    {
        $validated = $request->validate([
            'min_children' => 'integer|min:0|max:20',
            'max_children' => 'nullable|integer|min:0|max:20',
            'min_age' => 'nullable|integer|min:0|max:120',
            'max_age' => 'nullable|integer|min:0|max:120',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $bracket->overrides()->create($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', __('messages.override_added'));
    }

    public function update(Request $request, TaxScale $scale, TaxBracket $bracket, TaxBracketOverride $override): RedirectResponse
    {
        $validated = $request->validate([
            'min_children' => 'integer|min:0|max:20',
            'max_children' => 'nullable|integer|min:0|max:20',
            'min_age' => 'nullable|integer|min:0|max:120',
            'max_age' => 'nullable|integer|min:0|max:120',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $override->update($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', __('messages.override_updated'));
    }

    public function destroy(TaxScale $scale, TaxBracket $bracket, TaxBracketOverride $override): RedirectResponse
    {
        $override->delete();

        return redirect()->route('admin.scales.show', $scale)->with('success', __('messages.override_deleted'));
    }
}
