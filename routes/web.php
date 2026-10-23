<?php

use App\Http\Controllers\AsetController;
use App\Http\Controllers\AsetDataController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\DpiaController;
use App\Http\Controllers\RopaController;
use App\Http\Middleware\EnsurePersetujuan;
use App\Http\Middleware\EnsurePersetujuanAset;
use App\Http\Middleware\EnsurePersetujuanRisiko;
use App\Http\Controllers\RisikoController;
use App\Http\Controllers\RisikoDataController;
use App\Http\Controllers\InsidenController;
use App\Http\Controllers\InsidenDataController;
use App\Http\Middleware\EnsurePersetujuanInsiden;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =====================================================================
//  Halaman pembuka
// =====================================================================
Route::get('/', fn() => view('beranda'))->name('beranda');

// =====================================================================
//  MODUL B — RoPA & DPIA
// =====================================================================

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

    $request->session()->regenerate();
    session(['persetujuan' => now()->toIso8601String()]);

    return redirect()->route('ropa.form');
})->name('persetujuan.setuju');

Route::middleware([EnsurePersetujuan::class, 'cache.headers:no_store;private'])->group(function () {

    // RoPA
    Route::get('/ropa',        [RopaController::class, 'form'])->name('ropa.form');
    Route::post('/ropa/pdf',    [RopaController::class, 'pdf'])->name('ropa.pdf');
    Route::get('/ropa/kebijakan-privasi', [\App\Http\Controllers\KebijakanPrivasiController::class, 'pdf'])->name('ropa.kebijakan');
    Route::post('/ropa/{tab}', [RopaController::class, 'save'])->name('ropa.save');

    // DPIA
    Route::get('/dpia',                     [DpiaController::class, 'form'])->name('dpia.form');
    Route::post('/dpia/pdf',                 [DpiaController::class, 'pdf'])->name('dpia.pdf');
    Route::post('/dpia/metodologi/default', [DpiaController::class, 'resetMetodologi'])->name('dpia.metodologi.default');
    Route::post('/dpia/inheren/default',    [DpiaController::class, 'resetInheren'])->name('dpia.inheren.default');
    Route::post('/dpia/{tab}',              [DpiaController::class, 'save'])->name('dpia.save');

    // Ekspor / impor data RoPA & DPIA (JSON)
    Route::get('/data/ekspor', [DataController::class, 'ekspor'])->name('data.ekspor');
    Route::post('/data/impor', [DataController::class, 'impor'])->name('data.impor');

    // Sesi baru: SELURUH sesi diakhiri (Aset, RoPA, DPIA, persetujuan) → semua tab lain kedaluwarsa
    Route::post('/reset', function () {
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('persetujuan')
            ->with('saved', 'Sesi sebelumnya telah diakhiri dan seluruh data (Aset TIK, RoPA, DPIA) dihapus. Setujui pernyataan penggunaan untuk memulai sesi baru.');
    })->name('session.reset');
});

// =====================================================================
//  MODUL A — Identifikasi dan Penilaian Aset TIK
//  Route statis didaftarkan sebelum /aset/{tab}
// =====================================================================

Route::get('/aset/persetujuan', function () {
    return session()->has('persetujuan_aset')
        ? redirect()->route('aset.form')
        : view('aset.persetujuan');
})->name('aset.persetujuan');

Route::post('/aset/persetujuan', function (Request $request) {
    $request->validate(
        ['setuju' => ['accepted']],
        ['setuju.accepted' => 'Centang pernyataan persetujuan untuk melanjutkan.']
    );

    $request->session()->regenerate();
    session(['persetujuan_aset' => now()->toIso8601String()]);

    return redirect()->route('aset.form');
})->name('aset.persetujuan.setuju');

Route::middleware([EnsurePersetujuanAset::class, 'cache.headers:no_store;private'])
    ->prefix('aset')->name('aset.')->group(function () {

        Route::get('/',            [AsetController::class, 'form'])->name('form');
        Route::post('/pdf',        [AsetController::class, 'pdf'])->name('pdf');
        Route::get('/data/ekspor', [AsetDataController::class, 'ekspor'])->name('data.ekspor');
        Route::post('/data/impor', [AsetDataController::class, 'impor'])->name('data.impor');

        // Sesi baru: SELURUH sesi diakhiri (Aset, RoPA, DPIA, persetujuan) → semua tab lain kedaluwarsa
        Route::post('/reset', function () {
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('aset.persetujuan')
                ->with('saved', 'Sesi sebelumnya telah diakhiri dan seluruh data (Aset TIK, RoPA, DPIA) dihapus. Setujui pernyataan penggunaan untuk memulai sesi baru.');
        })->name('reset');

        Route::post('/{tab}', [AsetController::class, 'save'])->name('save');   // paling akhir
    });

// =====================================================================
//  MODUL C — Manajemen Risiko
//  Route statis didaftarkan sebelum /risiko/{tab}
// =====================================================================

Route::get('/risiko/persetujuan', function () {
    return session()->has('persetujuan_risiko')
        ? redirect()->route('risiko.form')
        : view('risiko.persetujuan');
})->name('risiko.persetujuan');

Route::post('/risiko/persetujuan', function (Request $request) {
    $request->validate(
        ['setuju' => ['accepted']],
        ['setuju.accepted' => 'Centang pernyataan persetujuan untuk melanjutkan.']
    );

    $request->session()->regenerate();
    session(['persetujuan_risiko' => now()->toIso8601String()]);

    return redirect()->route('risiko.form');
})->name('risiko.persetujuan.setuju');

Route::middleware([EnsurePersetujuanRisiko::class, 'cache.headers:no_store;private'])
    ->prefix('risiko')->name('risiko.')->group(function () {

        Route::get('/',            [RisikoController::class, 'form'])->name('form');
        Route::post('/pdf',        [RisikoController::class, 'pdf'])->name('pdf');
        Route::get('/data/ekspor', [RisikoDataController::class, 'ekspor'])->name('data.ekspor');
        Route::post('/data/impor', [RisikoDataController::class, 'impor'])->name('data.impor');

        // Sesi baru: SELURUH sesi diakhiri (semua modul) → semua tab lain kedaluwarsa
        Route::post('/reset', function () {
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('risiko.persetujuan')
                ->with('saved', 'Sesi sebelumnya telah diakhiri dan seluruh data dihapus. Setujui pernyataan penggunaan untuk memulai sesi baru.');
        })->name('reset');

        Route::post('/peta/default', [RisikoController::class, 'resetPeta'])->name('peta.default');

        Route::post('/{tab}', [RisikoController::class, 'save'])->name('save');   // paling akhir
    });

// =====================================================================
//  MODUL D — Laporan Penanganan Insiden
//  Route statis didaftarkan sebelum /insiden/{tab}
// =====================================================================

Route::get('/insiden/persetujuan', function () {
    return session()->has('persetujuan_insiden')
        ? redirect()->route('insiden.form')
        : view('insiden.persetujuan');
})->name('insiden.persetujuan');

Route::post('/insiden/persetujuan', function (Request $request) {
    $request->validate(
        ['setuju' => ['accepted']],
        ['setuju.accepted' => 'Centang pernyataan persetujuan untuk melanjutkan.']
    );

    $request->session()->regenerate();
    session(['persetujuan_insiden' => now()->toIso8601String()]);

    return redirect()->route('insiden.form');
})->name('insiden.persetujuan.setuju');

Route::middleware([EnsurePersetujuanInsiden::class, 'cache.headers:no_store;private'])
    ->prefix('insiden')->name('insiden.')->group(function () {

        Route::get('/',            [InsidenController::class, 'form'])->name('form');
        Route::post('/pdf',        [InsidenController::class, 'pdf'])->name('pdf');
        Route::get('/data/ekspor', [InsidenDataController::class, 'ekspor'])->name('data.ekspor');
        Route::post('/data/impor', [InsidenDataController::class, 'impor'])->name('data.impor');

        // Sesi baru: SELURUH sesi diakhiri (semua modul) → semua tab lain kedaluwarsa
        Route::post('/reset', function () {
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('insiden.persetujuan')
                ->with('saved', 'Sesi sebelumnya telah diakhiri dan seluruh data dihapus. Setujui pernyataan penggunaan untuk memulai sesi baru.');
        })->name('reset');

        Route::post('/{tab}', [InsidenController::class, 'save'])->name('save');   // paling akhir
    });
