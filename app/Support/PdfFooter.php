<?php

namespace App\Support;

use Barryvdh\DomPDF\PDF;

class PdfFooter
{
    /** Render PDF lalu gambar footer (garis, teks kiri, "Halaman x dari y") di setiap halaman. */
    public static function tambah(PDF $pdf, string $teksKiri): void
    {
        $pdf->render();

        $dompdf  = $pdf->getDomPDF();
        $canvas  = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->getFont('Helvetica');
        $size    = 7.5;
        $hitam   = [0, 0, 0];

        $kiri  = 45;
        $kanan = $canvas->get_width() - 45;
        $y     = $canvas->get_height() - 50;

        $canvas->page_line($kiri, $y, $kanan, $y, $hitam, 0.75);
        $canvas->page_text($kiri, $y + 4, $teksKiri, $font, $size, $hitam);

        $lebar = $metrics->getTextWidth('Halaman 99 dari 99', $font, $size);
        $canvas->page_text($kanan - $lebar, $y + 4, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, $size, $hitam);
    }
}
