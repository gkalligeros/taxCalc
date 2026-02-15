<?php

use App\Http\Controllers\CalculatorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalculatorController::class, 'index'])->name('calculator');
Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $supportedLocales = ['en', 'el'];

    if (! in_array($locale, $supportedLocales, true)) {
        $locale = config('app.fallback_locale', 'en');
    }

    $request->session()->put('locale', $locale);
    App::setLocale($locale);

    return redirect()->back();
})->name('locale.switch');
