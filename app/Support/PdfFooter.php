<?php

namespace App\Support;

use Barryvdh\DomPDF\PDF;

class PdfFooter
{
    /**
     * Jarak teks nomor halaman dari tepi bawah (pt; 1 cm ≈ 28,35 pt). Tata naskah dinas menyisakan 3 cm
     * di bagian bawah untuk footer TTE BSrE (QR code & keterangan tanda tangan elektronik), jadi nomor halaman
     * ditulis sekitar 3,5 cm dari tepi bawah. Margin bawah @page di setiap template PDF dibuat 4cm
     * agar isi tidak menabrak nomor halaman.
     */
    private const JARAK_BAWAH = 100;

    /** Render PDF lalu tulis "Halaman x dari y" di kanan bawah setiap halaman, di atas area footer TTE. */
    public static function tambah(PDF $pdf): void
    {
        $pdf->render();

        $dompdf  = $pdf->getDomPDF();
        $canvas  = $dompdf->getCanvas();
        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->getFont('Helvetica');
        $size    = 7.5;

        $kanan = $canvas->get_width() - 45;
        $y     = $canvas->get_height() - self::JARAK_BAWAH;
        $lebar = $metrics->getTextWidth('Halaman 99 dari 99', $font, $size);

        $canvas->page_text($kanan - $lebar, $y, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, $size, [0, 0, 0]);
    }
}
