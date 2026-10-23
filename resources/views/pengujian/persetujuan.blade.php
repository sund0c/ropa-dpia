@extends('layouts.pengujian')

@section('title', 'Persetujuan Penggunaan')

@section('content')
    <h1>Pengecekan Keamanan Aplikasi Web</h1>
    <p class="hint">Alat bantu pengujian keamanan aplikasi web secara manual berdasarkan OWASP Top 10:2025.</p>

    <noscript>
        <div class="alert err">Aplikasi ini memerlukan JavaScript. Aktifkan JavaScript di browser Anda.</div>
    </noscript>

    <dialog id="dialog-persetujuan" class="dialog dialog-lebar">
        <h2 style="margin-top:0">Pernyataan Penggunaan</h2>

        <ol class="persetujuan">
            <li>
                Aplikasi ini adalah alat bantu untuk mempermudah pengecekan keamanan aplikasi web secara manual
                mengacu kepada <strong>OWASP Top 10:2025</strong> dan Runbook Pengujian Manual Bidang Persandian
                Diskominfos Provinsi Bali. Setiap tab memuat pedoman langkah uji dan isian hasilnya.
                Satu sesi digunakan untuk satu aplikasi.
            </li>
            <li>
                Pengujian <strong>hanya boleh dilakukan terhadap aplikasi yang Anda miliki atau yang memiliki
                otorisasi tertulis</strong> untuk diuji. Pengujian tanpa izin dapat melanggar hukum. Hasil pengujian manual
                bersifat sampel dan tidak membuktikan aplikasi bebas kerentanan.
            </li>
            <li>
                Laporan yang dihasilkan berklasifikasi <strong>TERBATAS</strong> karena memuat informasi kerentanan.
                <strong>Jangan memasukkan token sesi, kata sandi, atau data pribadi yang sebenarnya</strong> ke dalam isian bukti.
            </li>
            <li>
                Dengan menggunakan aplikasi ini, pengguna memahami dan menyetujui bahwa aplikasi ini adalah alat bantu
                saja dan tidak melakukan penyimpanan data (sekali pakai).
            </li>
            <li>
                Seluruh data dan informasi yang dimasukkan oleh pengguna saat menggunakan aplikasi ini adalah
                tanggung jawab mutlak pengguna, termasuk kerahasiaan, integritas, dan ketersediaan data dan informasinya.
            </li>
        </ol>

        <p class="hint">
            Data sesi terhapus otomatis saat browser ditutup, saat memulai sesi baru, atau paling lambat 24 jam
            sejak sesi dimulai. Ekspor data sebelum menutup browser bila masih diperlukan.
        </p>

        <form method="POST" action="{{ route('pengujian.persetujuan.setuju') }}">
            @csrf
            <label class="check" style="margin:1rem 0">
                <input type="checkbox" name="setuju" value="1" data-setuju>
                <strong>Saya telah membaca, memahami, dan menyetujui pernyataan di atas.</strong>
            </label>
            @error('setuju') <small class="error">{{ $message }}</small> @enderror

            <p class="alert err" data-tolak-info hidden>
                Anda tidak dapat menggunakan aplikasi ini tanpa menyetujui pernyataan penggunaan.
                Silakan tutup halaman ini bila tidak setuju.
            </p>

            <div class="actions">
                <button type="button" class="secondary" data-tolak>Tidak Setuju</button>
                <button type="submit" data-mulai disabled>Setuju dan Mulai</button>
            </div>
        </form>
    </dialog>

    <script>
    (function () {
        const dlg   = document.getElementById('dialog-persetujuan');
        const cek   = dlg.querySelector('[data-setuju]');
        const mulai = dlg.querySelector('[data-mulai]');

        dlg.showModal();
        dlg.addEventListener('cancel', (e) => e.preventDefault());   // Esc tidak menutup popup

        cek.addEventListener('change', () => {
            mulai.disabled = !cek.checked;
            dlg.querySelector('[data-tolak-info]').hidden = true;
        });

        dlg.querySelector('[data-tolak]').addEventListener('click', () => {
            cek.checked = false;
            mulai.disabled = true;
            dlg.querySelector('[data-tolak-info]').hidden = false;
        });
    })();
    </script>
@endsection
