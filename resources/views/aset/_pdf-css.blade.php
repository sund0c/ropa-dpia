{{-- Gaya PDF aset (dipakai PDF satu aset & Aset Register) --}}
        @page {
            margin: 1.8cm 1.6cm 4cm 1.6cm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.35;
        }

        header {
            position: fixed;
            top: -1.15cm;
            left: 0;
            right: 0;
            text-align: right;
        }

        header span {
            font-weight: bold;
            font-size: 9pt;
            letter-spacing: 1.5pt;
            border: 0.75pt solid #000;
            padding: 2pt 8pt;
        }

        h1 {
            font-size: 13pt;
            text-align: center;
            margin: 0 0 12pt;
            letter-spacing: .3pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td,
        .kv td,
        .grid th,
        .grid td {
            border: 0.75pt solid #000;
            padding: 4pt 6pt;
            vertical-align: top;
            text-align: left;
        }

        .meta td.k,
        .kv td.k {
            width: 32%;
            font-weight: bold;
        }

        h2 {
            font-size: 10.5pt;
            background: #e5e5e5;
            border: 0.75pt solid #000;
            border-bottom: 0;
            padding: 4pt 6pt;
            margin: 14pt 0 0;
        }

        h2.halaman-baru {
            page-break-before: always;
            margin-top: 0;
        }

        h3 {
            font-size: 9.5pt;
            margin: 8pt 0 3pt;
        }

        .grid th {
            background: #f2f2f2;
        }

        .grid td.no,
        .grid td.tengah {
            text-align: center;
        }

        .kesimpulan {
            margin-top: 6pt;
            border: 0.75pt solid #000;
            padding: 5pt 6pt;
            font-weight: bold;
        }

        .catatan {
            margin-top: 4pt;
            border: 0.75pt solid #000;
            padding: 5pt 6pt;
        }

        .tebal {
            font-weight: bold;
        }

        /* Tanda tangan */
        .blok-pengesahan {
            margin-top: 26pt;
            page-break-inside: avoid;
        }

        .pengesahan {
            text-align: center;
        }

        .ttd {
            margin-top: 16pt;
        }

        .ttd td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10pt;
            border: 0;
        }

        .ttd td.ruang {
            height: 65pt;
            vertical-align: middle;
        }

        .ttd .nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .ttd .tte {
            font-size: 8pt;
            font-style: italic;
            color: #333;
        }

        .ttd .ruang-ttd {
            height: 60pt;
        }
