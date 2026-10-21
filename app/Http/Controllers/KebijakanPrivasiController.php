<?php

namespace App\Http\Controllers;

use App\Support\PdfFooter;
use App\Support\RopaStatus;
use Barryvdh\DomPDF\Facade\Pdf;

/** Draf Kebijakan Privasi (publik) untuk satu layanan, disusun otomatis dari isian RoPA di sesi. */
class KebijakanPrivasiController extends Controller
{
    public function pdf()
    {
        $ropa = session('ropa', []);

        if (! RopaStatus::lengkap($ropa)) {
            return redirect()->route('ropa.form')
                ->withErrors(['ropa' => 'Lengkapi seluruh tab RoPA terlebih dahulu untuk membuat draf Kebijakan Privasi.']);
        }

        $pdf = Pdf::loadView('kebijakan.pdf', [
            'ropa'         => $ropa,
            'labels'       => RopaController::labelsPdf(),
            'pasal'        => RopaController::PASAL_DASAR,
            'risikoTinggi' => RopaStatus::wajibDpia($ropa),
            'tanggal'      => now()->locale('id')->translatedFormat('d F Y'),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isRemoteEnabled'     => false,
                'isPhpEnabled'        => false,
                'isJavascriptEnabled' => false,
                'defaultFont'         => 'Helvetica',
            ]);

        PdfFooter::tambah($pdf, 'DRAFT Kebijakan Privasi');

        return $pdf->stream('Kebijakan-Privasi-' . $ropa['nomor'] . '.pdf')
            ->header('Cache-Control', 'no-store, private');
    }
}
