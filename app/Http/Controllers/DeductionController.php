<?php

namespace App\Http\Controllers;

use App\Models\Deduction;
use App\Models\TaxScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function store(Request $request, TaxScale $scale): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $scale->deductions()->create($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Deduction added.');
    }

    public function update(Request $request, TaxScale $scale, Deduction $deduction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:1',
        ]);

        $deduction->update($validated);

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Deduction updated.');
    }

    public function destroy(TaxScale $scale, Deduction $deduction): RedirectResponse
    {
        $deduction->delete();

        return redirect()->route('admin.scales.show', $scale)->with('success', 'Deduction deleted.');
    }
}
