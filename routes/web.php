<?php

use App\Http\Controllers\BracketController;
use App\Http\Controllers\BracketOverrideController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\DeductionController;
use App\Http\Controllers\ScaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CalculatorController::class, 'index'])->name('calculator');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('scales', [ScaleController::class, 'index'])->name('scales.index');
    Route::post('scales', [ScaleController::class, 'store'])->name('scales.store');
    Route::get('scales/{scale}', [ScaleController::class, 'show'])->name('scales.show');
    Route::put('scales/{scale}', [ScaleController::class, 'update'])->name('scales.update');
    Route::delete('scales/{scale}', [ScaleController::class, 'destroy'])->name('scales.destroy');
    Route::post('scales/{scale}/activate', [ScaleController::class, 'activate'])->name('scales.activate');

    Route::post('scales/{scale}/brackets', [BracketController::class, 'store'])->name('brackets.store');
    Route::put('scales/{scale}/brackets/{bracket}', [BracketController::class, 'update'])->name('brackets.update');
    Route::delete('scales/{scale}/brackets/{bracket}', [BracketController::class, 'destroy'])->name('brackets.destroy');

    Route::post('scales/{scale}/brackets/{bracket}/overrides', [BracketOverrideController::class, 'store'])->name('overrides.store');
    Route::put('scales/{scale}/brackets/{bracket}/overrides/{override}', [BracketOverrideController::class, 'update'])->name('overrides.update');
    Route::delete('scales/{scale}/brackets/{bracket}/overrides/{override}', [BracketOverrideController::class, 'destroy'])->name('overrides.destroy');

    Route::post('scales/{scale}/deductions', [DeductionController::class, 'store'])->name('deductions.store');
    Route::put('scales/{scale}/deductions/{deduction}', [DeductionController::class, 'update'])->name('deductions.update');
    Route::delete('scales/{scale}/deductions/{deduction}', [DeductionController::class, 'destroy'])->name('deductions.destroy');
});
