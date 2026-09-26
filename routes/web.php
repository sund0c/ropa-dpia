<?php

use App\Http\Controllers\DpiaController;
use App\Http\Controllers\RopaController;
use App\Http\Middleware\EnsurePersetujuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------
//  Persetujuan penggunaan (di luar kelompok yang dijaga)
// ---------------------------------------------------------------------
Route::get('/persetujuan', function () {
    return session()->has('persetujuan')
        ? redirect()->route('ropa.form')
        : view('persetujuan');
})->name('persetujuan');

Route::post('/persetujuan', function (Request $request) {
    $request->validate(
        ['setuju' => ['accepted']],
        ['setuju.accepted' => 'Centang pernyataan persetujuan untuk melanjutkan.']
    );

    $request->session()->regenerate();   // status session berubah: perbarui session ID
    session(['persetujuan' => now()->toIso8601String()]);

    return redirect()->route('ropa.form');
})->name('persetujuan.setuju');

// ---------------------------------------------------------------------
//  Aplikasi (wajib sudah menyetujui)
// ---------------------------------------------------------------------
Route::middleware(EnsurePersetujuan::class)->group(function () {

    Route::get('/', fn() => redirect()->route('ropa.form'));

    // RoPA
    Route::get('/ropa',        [RopaController::class, 'form'])->name('ropa.form');
    Route::get('/ropa/pdf',    [RopaController::class, 'pdf'])->name('ropa.pdf');
    Route::post('/ropa/{tab}', [RopaController::class, 'save'])->name('ropa.save');

    // DPIA
    Route::get('/dpia',                    [DpiaController::class, 'form'])->name('dpia.form');
    Route::get('/dpia/pdf',                [DpiaController::class, 'pdf'])->name('dpia.pdf');
    Route::post('/dpia/metodologi/default', [DpiaController::class, 'resetMetodologi'])->name('dpia.metodologi.default');
    Route::post('/dpia/inheren/default',    [DpiaController::class, 'resetInheren'])->name('dpia.inheren.default');
    Route::post('/dpia/{tab}',             [DpiaController::class, 'save'])->name('dpia.save');

    // Ekspor / impor data (JSON)
    Route::get('/data/ekspor', [\App\Http\Controllers\DataController::class, 'ekspor'])->name('data.ekspor');
    Route::post('/data/impor', [\App\Http\Controllers\DataController::class, 'impor'])->name('data.impor');

    // Sesi baru: semua data dihapus, persetujuan dibawa ke sesi baru
    // Sesi baru: semua data dihapus, termasuk persetujuan → popup persetujuan muncul lagi
    Route::post('/reset', function () {
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('persetujuan')
            ->with('saved', 'Data sesi sebelumnya telah dihapus. Setujui pernyataan penggunaan untuk memulai sesi baru.');
    })->name('session.reset');
});
