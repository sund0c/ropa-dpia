<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RopaController;
use App\Http\Controllers\DpiaController;

Route::get('/', fn() => redirect()->route('ropa.form'));

Route::get('/ropa',        [RopaController::class, 'form'])->name('ropa.form');
// Placeholder, isi PDF dikerjakan nanti
Route::get('/ropa/pdf', function () {
    $ropa = session('ropa', []);
    abort_unless(\App\Support\RopaStatus::lengkap($ropa), 403, 'Lengkapi seluruh tab RoPA terlebih dahulu.');
    return response('Export PDF RoPA ' . e($ropa['nomor']) . ' belum diimplementasikan.', 501);
})->name('ropa.pdf');
Route::get('/ropa/pdf', [RopaController::class, 'pdf'])->name('ropa.pdf');
Route::post('/ropa/{tab}', [RopaController::class, 'save'])->name('ropa.save');

Route::get('/dpia',        [DpiaController::class, 'form'])->name('dpia.form');
Route::post('/dpia/{tab}', [DpiaController::class, 'save'])->name('dpia.save');
Route::post('/dpia/metodologi/default', [DpiaController::class, 'resetMetodologi'])->name('dpia.metodologi.default');
Route::post('/dpia/inheren/default', [DpiaController::class, 'resetInheren'])->name('dpia.inheren.default');

Route::post('/reset', function () {
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('ropa.form');
})->name('session.reset');
