<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ScaleController;
use App\Http\Controllers\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalculatorController::class, 'index'])->name('calculator');
Route::get('/sitemap.xml', function () {
    $url = config('app.url');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
        . '  <url><loc>' . $url . '</loc></url>' . "\n"
        . '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::get('/robots.txt', function () {
    $sitemap = config('app.url') . '/sitemap.xml';

    return response("User-agent: *\nDisallow:\nSitemap: {$sitemap}\n", 200, ['Content-Type' => 'text/plain']);
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $supportedLocales = ['en', 'el', 'it'];

    if (! in_array($locale, $supportedLocales, true)) {
        $locale = config('app.fallback_locale', 'en');
    }

    $request->session()->put('locale', $locale);
    App::setLocale($locale);

    return redirect()->back();
})->name('locale.switch');

// Admin auth (unprotected)
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Admin protected routes
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [StatsController::class, 'index'])->name('stats');
    Route::resource('scales', ScaleController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('scales/{scale}/activate', [ScaleController::class, 'activate'])->name('scales.activate');
});
