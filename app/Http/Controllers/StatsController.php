<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\TaxBracket;
use App\Models\TaxScale;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(): Response
    {
        $totalCalculations = Calculation::count();
        $totalScales = TaxScale::count();
        $activeScales = TaxScale::where('is_active', true)->count();
        $totalCountries = TaxScale::distinct('country_code')->count('country_code');
        $totalBrackets = TaxBracket::count();

        $byCountry = Calculation::select('country_code')
            ->selectRaw('MIN(currency) as currency')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('ROUND(AVG(gross), 2) as avg_gross')
            ->selectRaw('ROUND(AVG(net), 2) as avg_net')
            ->selectRaw('ROUND(AVG(tax), 2) as avg_tax')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->get();

        $recentCalculations = Calculation::orderByDesc('created_at')
            ->limit(100)
            ->get();

        return Inertia::render('Admin/Stats', [
            'stats' => [
                'total_calculations' => $totalCalculations,
                'total_scales' => $totalScales,
                'active_scales' => $activeScales,
                'total_countries' => $totalCountries,
                'total_brackets' => $totalBrackets,
            ],
            'by_country' => $byCountry,
            'recent_calculations' => $recentCalculations,
        ]);
    }
}
