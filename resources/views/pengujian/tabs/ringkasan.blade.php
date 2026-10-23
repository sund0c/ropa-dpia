@php
    use App\Http\Controllers\PengujianController as PC;
    use App\Support\RunbookOwasp;

    $rekap   = PC::rekap($uji);
    $temuan  = PC::daftarTemuan($uji);
    $belum   = []; // kategori yang belum disimpan
    foreach (array_keys(RunbookOwasp::KATEGORI) as $k) {
        if (! isset($uji[$k])) $belum[] = strtoupper($k);
    }
    $warnaR = ['kritis' => '#b91c1c', 'tinggi' => '#c2410c', 'sedang' => '#b45309', 'rendah' => '#047857'];

    // Contoh kesimpulan, selaras dengan contoh hasil di tab A01–A10 (aplikasi perizinan fiktif)
    $contohKesimpulan = "Pengujian manual terhadap Aplikasi Layanan Perizinan Dinas XYZ di lingkungan staging pada 14–16 Oktober 2026 mencakup 61 butir uji OWASP Top 10:2025. Ditemukan 3 kerentanan: 1 berisiko Tinggi (objek pengajuan milik pengguna lain dapat diakses melalui API, A01.2), 1 Sedang (belum ada pembatasan percobaan login, A07.4), dan 1 Rendah (header keamanan belum lengkap, A02.1). Sejumlah butir masih berstatus Perlu tinjauan karena memerlukan tinjauan kode atau konfirmasi developer, terutama pada pencatatan log dan integrasi SOC (A09).\n\nPrioritas perbaikan:\n1. Segera terapkan pengecekan kepemilikan objek di server pada seluruh endpoint pengajuan; aplikasi sebaiknya tidak naik ke produksi sebelum temuan Tinggi ini ditutup.\n2. Terapkan pembatasan laju dan penguncian sementara pada login.\n3. Lengkapi header keamanan dan tindak lanjuti butir berstatus Perlu tinjauan bersama developer.\n\nDisarankan uji ulang (retest) terhadap seluruh temuan setelah perbaikan selesai.";
@endphp

<style>
    .rk-tabel { width: 100%; border-collapse: collapse; font-size: .85rem; margin-bottom: 1.25rem; }
    .rk-tabel th, .rk-tabel td { border: 1px solid #e5e7eb; padding: .4rem .5rem; text-align: center; }
    .rk-tabel th { background: #f3f4f6; }
    .rk-tabel td.kat { text-align: left; }
    .rk-tabel td.rentan { font-weight: bold; color: #b91c1c; }
    .rk-temuan { display: flex; flex-direction: column; gap: .5rem; margin-bottom: 1.25rem; }
    .rk-t { border: 1px solid #e5e7eb; border-left: 4px solid #9ca3af; border-radius: 6px; padding: .5rem .75rem; font-size: .88rem; }
    .rk-badge { display: inline-block; padding: .05rem .5rem; border-radius: 999px; color: #fff; font-size: .75rem; font-weight: bold; }
    .rk-kosong { padding: .6rem .9rem; background: #dcfce7; border-radius: 6px; color: #166534; margin-bottom: 1.25rem; }
</style>

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
</style>

<div class="contoh-bar">
    <span>Rekap dan daftar temuan dihitung otomatis dari tab A01–A10. Muat contoh <strong>kesimpulan</strong> untuk melihat cara penulisannya.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

@if ($belum)
    <div class="alert warn">
        Kategori berikut belum disimpan: <strong>{{ implode(', ', $belum) }}</strong>.
        Simpan seluruh tab A01–A10 agar rekap dan daftar temuan lengkap.
    </div>
@endif

<section class="panel">
    <h2 class="panel-head">A. Rekap Hasil per Kategori</h2>
    <table class="rk-tabel">
        <tr>
            <th style="width:26%">Kategori</th>
            <th>Aman</th><th>Kerentanan</th><th>Perlu tinjauan</th><th>Tidak diuji</th><th>Tidak berlaku</th><th>Total</th>
        </tr>
        @foreach (RunbookOwasp::KATEGORI as $k => $kat)
            @php $r = $rekap[$k]; @endphp
            <tr>
                <td class="kat">{{ $kat['kode'] }} — {{ $kat['nama'] }}</td>
                <td>{{ $r['aman'] }}</td>
                <td class="{{ $r['rentan'] ? 'rentan' : '' }}">{{ $r['rentan'] }}</td>
                <td>{{ $r['tinjau'] }}</td>
                <td>{{ $r['tidak_diuji'] }}</td>
                <td>{{ $r['tidak_berlaku'] }}</td>
                <td>{{ $r['total'] }}</td>
            </tr>
        @endforeach
    </table>
</section>

<section class="panel">
    <h2 class="panel-head">B. Daftar Temuan <small>{{ count($temuan) }} kerentanan</small></h2>
    @if ($temuan === [])
        <div class="rk-kosong">Belum ada butir yang ditandai sebagai Kerentanan. Pastikan seluruh kategori sudah diisi dan disimpan.</div>
    @else
        <div class="rk-temuan">
            @foreach ($temuan as $t)
                <div class="rk-t" style="border-left-color:{{ $warnaR[$t['risiko']] ?? '#9ca3af' }}">
                    <span class="rk-badge" style="background:{{ $warnaR[$t['risiko']] ?? '#9ca3af' }}">{{ PC::RISIKO[$t['risiko']] ?? $t['risiko'] }}</span>
                    <strong>{{ $t['kode'] }} {{ $t['judul'] }}</strong> — {{ $t['kat'] }}<br>
                    <small>{{ $t['objek'] }}</small>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="panel">
    <h2 class="panel-head">C. Kesimpulan &amp; Pernyataan Ruang Lingkup</h2>
    <x-aset-field name="kesimpulan" label="Kesimpulan" type="textarea" rows="9" :value="$d['kesimpulan'] ?? null"
        required maxlength="3000"
        hint="Ringkasan kondisi keamanan aplikasi dan prioritas perbaikan untuk pimpinan." />

    <x-aset-field name="pernyataan" label="Pernyataan Ruang Lingkup" type="textarea" rows="4"
        :value="$d['pernyataan'] ?? \App\Http\Controllers\PengujianController::PERNYATAAN_BAKU" required maxlength="3000"
        hint="Teks baku dari runbook; sesuaikan bila perlu. Menegaskan bahwa hasil bersifat sampel." />
</section>

<script>
(function () {
    const form = document.querySelector('[name="kesimpulan"]').form;
    const contoh = {
        kesimpulan: @json($contohKesimpulan),
        pernyataan: @json(\App\Http\Controllers\PengujianController::PERNYATAAN_BAKU),
    };
    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        if (form.elements.kesimpulan.value.trim() !== '' && !confirm('Kesimpulan yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        form.elements.kesimpulan.value = contoh.kesimpulan;
        if (form.elements.pernyataan.value.trim() === '') form.elements.pernyataan.value = contoh.pernyataan;
    });
})();
</script>
