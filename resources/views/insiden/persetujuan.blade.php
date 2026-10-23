@extends('layouts.insiden')

@section('title', 'Persetujuan Penggunaan')

@section('content')
    <h1>Laporan Penanganan Insiden</h1>
    <p class="hint">Alat bantu penyusunan laporan penanganan insiden siber pada aset Teknologi Informasi dan Komunikasi (TIK).</p>

    <noscript>
        <div class="alert err">Aplikasi ini memerlukan JavaScript. Aktifkan JavaScript di browser Anda.</div>
    </noscript>

    <dialog id="dialog-persetujuan" class="dialog dialog-lebar">
        <h2 style="margin-top:0">Pernyataan Penggunaan</h2>

        <ol class="persetujuan">
            <li>
                Aplikasi ini adalah alat bantu untuk mempermudah penyusunan laporan penanganan insiden siber mengacu
                kepada ISO/IEC 27035, NIST SP 800-61, Peraturan BSSN Nomor 1 Tahun 2024, dan <em>best practice</em> di
                Diskominfos Provinsi Bali. Dipersilakan menyesuaikan jika memiliki format dan kebijakan terkait
                penanganan insiden. Satu sesi digunakan untuk satu insiden.
            </li>
            <li>
                Laporan yang dihasilkan berklasifikasi <strong>TERBATAS</strong> karena memuat informasi kerentanan dan
                jejak serangan. <strong>Jangan memasukkan kata sandi, kunci, token, atau data pribadi yang sebenarnya.</strong>
                Samarkan informasi sensitif (mis. <code>admin:****</code>, <code>10.x.x.x</code>) seperlunya.
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

        <form method="POST" action="{{ route('insiden.persetujuan.setuju') }}">
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
