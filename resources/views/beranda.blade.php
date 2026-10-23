<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tools Manajemen Aset Persandian</title>
    <style>
        :root {
            --latar: #1e293b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, sans-serif;
            color: #1f2937;
            background: var(--latar);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .kartu {
            width: 100%;
            max-width: 760px;
            background: #fff;
            border-radius: 14px;
            padding: 2.5rem 2.25rem;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .35);
        }

        .kepala {
            text-align: center;
            margin-bottom: 2rem;
        }

        .kepala h1 {
            margin: 0 0 .6rem;
            font-size: 1.85rem;
            letter-spacing: .5px;
            color: #0f172a;
        }

        .kepala p {
            margin: 0;
            color: #6b7280;
            line-height: 1.6;
        }

        .pilihan {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .tombol {
            display: flex;
            align-items: center;
            gap: 1.1rem;
            padding: 1.25rem 1.4rem;
            border-radius: 12px;
            border: 2px solid #c7d2fe;
            background: #f8faff;
            text-decoration: none;
            color: inherit;
            transition: border-color .15s, background .15s, transform .15s;
        }

        a.tombol:hover,
        a.tombol:focus-visible {
            border-color: #1e40af;
            background: #eef2ff;
            transform: translateY(-1px);
            outline: none;
        }

        .huruf {
            flex: none;
            width: 3.2rem;
            height: 3.2rem;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: #1e40af;
            color: #fff;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .teks strong {
            display: block;
            font-size: 1.25rem;
            letter-spacing: .3px;
            color: #0f172a;
        }

        .teks small {
            display: block;
            margin-top: .25rem;
            color: #6b7280;
            font-size: .9rem;
            line-height: 1.45;
        }

        .panah {
            margin-left: auto;
            font-size: 1.5rem;
            color: #1e40af;
        }

        .tombol.nonaktif {
            border-color: #e5e7eb;
            background: #f9fafb;
            cursor: not-allowed;
        }

        .tombol.nonaktif .huruf {
            background: #9ca3af;
        }

        .tombol.nonaktif strong {
            color: #6b7280;
        }

        .lencana {
            margin-left: auto;
            padding: .25rem .75rem;
            border-radius: 999px;
            background: #e5e7eb;
            color: #4b5563;
            font-size: .8rem;
            font-weight: bold;
            letter-spacing: .4px;
        }

        .info {
            margin: 0 0 1.5rem;
            padding: .8rem 1rem;
            border-radius: 8px;
            background: #fef3c7;
            color: #78350f;
            line-height: 1.5;
            font-size: .95rem;
        }

        @media (max-width: 560px) {
            .kartu {
                padding: 1.75rem 1.1rem;
            }

            .kepala h1 {
                font-size: 1.45rem;
            }

            .teks strong {
                font-size: 1.05rem;
            }

            .panah {
                display: none;
            }
        }
    </style>
</head>

<body>
    <main class="kartu">
        <div class="kepala">
            <h1>TOOLS MANAJEMEN ASET PERSANDIAN</h1>
            <p>
                Berdasarkan praktik baik di Dinas Kominfos Provinsi Bali<br>
                &copy; 2026 Bidang Persandian
            </p>
        </div>

        @if (session('sesi_berakhir') || session('expired'))
            <div class="info">
                <strong>Sesi telah berakhir.</strong> Sesi baru telah dimulai di tab lain, atau sesi sudah melewati 24
                jam.
                Seluruh data sebelumnya telah dihapus. Silakan pilih modul untuk memulai kembali.
            </div>
        @endif


        <nav class="pilihan">
            <a class="tombol" href="{{ route('aset.form') }}">
                <span class="huruf">A</span>
                <span class="teks">
                    <strong>IDENTIFIKASI DAN PENILAIAN ASET TIK</strong>
                    <small>Informasi aset, kritikalitas (CIA), Infrastruktur Informasi Vital, dan kategorisasi Sistem
                        Elektronik</small>
                </span>
                <span class="panah">→</span>
            </a>

            <a class="tombol" href="{{ route('ropa.form') }}">
                <span class="huruf">B</span>
                <span class="teks">
                    <strong>PENYUSUNAN ROPA &amp; DPIA</strong>
                    <small>Record of Processing Activities dan Data Protection Impact Assessment sesuai UU PDP</small>
                </span>
                <span class="panah">→</span>
            </a>

            <a class="tombol" href="{{ route('risiko.form') }}">
                <span class="huruf">C</span>
                <span class="teks">
                    <strong>MANAJEMEN RISIKO</strong>
                    <small>Penyusunan dokumen manajemen risiko aset</small>
                </span>
                <span class="panah">→</span>
            </a>

            <a class="tombol" href="{{ route('insiden.form') }}">
                <span class="huruf">D</span>
                <span class="teks">
                    <strong>LAPORAN PENANGANAN INSIDEN</strong>
                    <small>Penyusunan laporan penanganan insiden siber (ISO/IEC 27035, NIST SP 800-61, Peraturan BSSN 1/2024)</small>
                </span>
                <span class="panah">→</span>
            </a>
        </nav>
    </main>
</body>

</html>
