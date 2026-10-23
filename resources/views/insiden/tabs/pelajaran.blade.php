@php
    use App\Http\Controllers\InsidenController as IC;

    $lama = session()->hasOldInput();
    $ev   = fn (string $a, string $f) => (string) ($lama ? old("evaluasi.$a.$f") : ($d['evaluasi'][$a][$f] ?? ''));

    $tl = $lama ? array_values(array_filter((array) old('tindak_lanjut', []), 'is_array')) : ($d['tindak_lanjut'] ?? []);
    if ($tl === []) $tl = [[]];

    // ---------------- Contoh kasus deface WordPress (Laporan Akhir) ----------------
    $t = fn ($a, $k, $p, $g, $s) => ['tindakan' => $a, 'kontrol' => $k, 'pj' => $p, 'target' => $g, 'status' => $s];
    $contoh = [
        'evaluasi' => [
            'deteksi'    => ['Operator cepat meneruskan laporan masyarakat (13 menit)', 'Deteksi datang dari publik, bukan dari pemantauan; log belum masuk SIEM'],
            'tanggapan'  => ['Bukti diamankan sebelum pembersihan; situs pulih dalam ± 3,5 jam', 'Belum ada playbook defacement tertulis, sehingga langkah disusun saat kejadian'],
            'pemulihan'  => ['Cadangan harian tersedia dan dapat dipulihkan', 'Cadangan berada di VM yang sama; belum ada salinan offline'],
            'komunikasi' => ['Pimpinan diberi tahu dalam 1 jam', 'Belum ada templat pernyataan publik untuk insiden'],
            'pencegahan' => ['', 'Tidak ada standar hardening CMS dan inventaris akun admin'],
        ],
        'tindak_lanjut' => [
            $t('Wajibkan 2FA dan kata sandi minimal 12 karakter untuk seluruh akun admin CMS Pemprov Bali', 'A.5.17, A.8.5', 'Bidang Persandian', '2026-11-30', 'berjalan'),
            $t('Susun dan tetapkan standar hardening WordPress (larang akun admin, matikan editor berkas, batasi /wp-admin ke VPN/IP internal)', 'A.8.9', 'Bidang Persandian & Bidang Aplikasi', '2026-12-31', 'belum'),
            $t('Integrasikan log seluruh situs ke SIEM/SOC dengan aturan peringatan brute force login', 'A.8.15, A.8.16', 'Tim SOC', '2026-12-31', 'belum'),
            $t('Pasang pemantauan perubahan halaman (defacement monitoring) dengan notifikasi otomatis', 'A.8.16', 'Tim SOC', '2026-11-30', 'berjalan'),
            $t('Inventarisasi dan audit akun admin di semua situs OPD; jadwalkan penilaian kerentanan berkala', 'A.5.9, A.8.8', 'Bidang Persandian', '2027-01-31', 'belum'),
            $t('Pindahkan cadangan ke penyimpanan terpisah dan uji pemulihan tiap triwulan', 'A.8.13', 'Pengelola Pusat Data', '2026-12-31', 'belum'),
            $t('Susun playbook penanganan defacement dan templat pernyataan publik', 'A.5.24, A.5.26', 'Tim Penanganan Insiden', '2026-11-30', 'berjalan'),
        ],
        'rekomendasi' => 'Wajibkan kata sandi kuat dan autentikasi dua faktor untuk seluruh akun administrator CMS di lingkungan Pemprov Bali, hapus nama pengguna bawaan admin, dan batasi akses halaman administrator hanya dari jaringan internal/VPN.',
        'tembusan'    => "1. Kepala Dinas Kominfos Provinsi Bali\n2. Arsip Tim Penanganan Insiden",
    ];
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .ev { display: grid; grid-template-columns: 7rem 1fr 1fr; gap: 0 .75rem; align-items: start; margin-bottom: .5rem; }
    .ev-head { font-size: .8rem; color: #6b7280; font-weight: bold; }
    .ev-nama { font-weight: bold; color: #1e3a8a; padding-top: .5rem; }
    .ev textarea { width: 100%; box-sizing: border-box; padding: .45rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
    .tl-list { display: flex; flex-direction: column; gap: .6rem; }
    .tl { border: 1px solid #e5e7eb; border-left: 4px solid #fcd34d; border-radius: 8px; padding: .4rem .8rem 0; }
    .tl .field { margin-bottom: .6rem; }
    .tl .field input, .tl .field select { width: 100%; box-sizing: border-box; }
    .tl-head { display: flex; justify-content: space-between; align-items: center; }
    .tl-no { font-weight: bold; color: #92400e; }
    button.tl-hapus { background: transparent; color: #b91c1c; padding: .2rem .5rem; font-size: 1rem; }
    .tl-grid { display: grid; grid-template-columns: 1fr 1.2fr .9fr .9fr; gap: 0 .75rem; }
    .tl-tambah { margin: .75rem 0 1rem; }
    @media (max-width: 700px) { .ev { grid-template-columns: 1fr; } .ev-head-row { display: none; } .tl-grid { grid-template-columns: 1fr 1fr; } }
</style>

<div class="contoh-bar">
    <span>Contoh evaluasi dan tindak lanjut kasus <em>defacement</em> WordPress.</span>
    <button type="button" data-isi-contoh>Isi dengan contoh</button>
</div>

<datalist id="daftar-kontrol">
    @foreach (IC::KONTROL as $k) <option value="{{ $k }}"></option> @endforeach
</datalist>

<div id="pelajaran">
    {{-- ================= A ================= --}}
    <section class="panel">
        <h2 class="panel-head">A. Pelajaran yang Dipetik <small>Lessons learned</small></h2>
        <p class="hint" style="margin-top:0">
            Evaluasi jujur tanpa mencari kesalahan perorangan. Isi minimal satu aspek; kosongkan aspek yang tidak relevan.
        </p>
        @error('evaluasi') <div class="alert err">{{ $message }}</div> @enderror

        <div class="ev ev-head-row">
            <span></span><span class="ev-head">Berjalan baik</span><span class="ev-head">Perlu perbaikan</span>
        </div>
        @foreach (IC::ASPEK as $a => $nama)
            <div class="ev">
                <span class="ev-nama">{{ $nama }}</span>
                <div>
                    <textarea name="evaluasi[{{ $a }}][baik]" rows="2" maxlength="1000" aria-label="{{ $nama }}: berjalan baik">{{ $ev($a, 'baik') }}</textarea>
                    @error("evaluasi.$a.baik") <small class="error">{{ $message }}</small> @enderror
                </div>
                <div>
                    <textarea name="evaluasi[{{ $a }}][perbaikan]" rows="2" maxlength="1000" aria-label="{{ $nama }}: perlu perbaikan">{{ $ev($a, 'perbaikan') }}</textarea>
                    @error("evaluasi.$a.perbaikan") <small class="error">{{ $message }}</small> @enderror
                </div>
            </div>
        @endforeach
    </section>

    {{-- ================= B ================= --}}
    <section class="panel">
        <h2 class="panel-head">B. Rencana Tindak Lanjut</h2>
        <p class="hint" style="margin-top:0">
            Tindakan perbaikan jangka menengah/panjang agar insiden serupa tidak terulang. Kontrol dapat dipilih dari saran Annex A.
        </p>
        @error('tindak_lanjut') <div class="alert err">{{ $message }}</div> @enderror
        <div class="tl-list" data-tl-list>
            @foreach ($tl as $i => $row)
                @include('insiden.tabs._tindak-lanjut-row', ['i' => $i, 'row' => $row])
            @endforeach
        </div>
        <template data-tl-template>
            @include('insiden.tabs._tindak-lanjut-row', ['i' => '__i__', 'row' => []])
        </template>
        <button type="button" class="secondary tl-tambah" data-tl-tambah>+ Tambah tindak lanjut</button>
    </section>

    {{-- ================= C ================= --}}
    <section class="panel">
        <h2 class="panel-head">C. Rekomendasi Utama &amp; Tembusan</h2>
        <x-aset-field name="rekomendasi" label="Rekomendasi Utama" type="textarea" rows="3"
            :value="$d['rekomendasi'] ?? null" required maxlength="2000"
            hint="1–2 kalimat untuk pimpinan, dicetak di Ringkasan Eksekutif. Contoh: Wajibkan kata sandi kuat dan 2FA untuk seluruh akun administrator CMS, hapus nama pengguna bawaan admin, dan batasi akses halaman administrator hanya dari jaringan internal/VPN." />
        <x-aset-field name="tembusan" label="Tembusan" type="textarea" rows="2"
            :value="$d['tembusan'] ?? null" maxlength="1000"
            hint="Opsional, satu per baris. Contoh: 1. Kepala Dinas Kominfos Provinsi Bali  2. Arsip Tim Penanganan Insiden" />
    </section>
</div>

<script>
(function () {
    const root   = document.getElementById('pelajaran');
    const form   = root.closest('form');
    const list   = root.querySelector('[data-tl-list]');
    const tpl    = root.querySelector('[data-tl-template]');
    const contoh = @json($contoh);
    const MAKS   = {{ IC::MAKS_BARIS }};
    let seq = Date.now();

    function tambah(isi) {
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', String(seq++)));
        const baris = list.lastElementChild;
        if (isi) for (const [k, v] of Object.entries(isi)) {
            const el = baris.querySelector(`[name$="[${k}]"]`);
            if (el) el.value = v ?? '';
        }
        return baris;
    }

    function segarkan() {
        list.querySelectorAll('[data-tl-no]').forEach((el, n) => el.textContent = 'Tindak lanjut ' + (n + 1));
        root.querySelector('[data-tl-tambah]').disabled = list.children.length >= MAKS;
    }

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-tl-tambah]')) { tambah().querySelector('input').focus(); segarkan(); return; }
        const h = e.target.closest('[data-tl-hapus]');
        if (h) {
            const baris = h.closest('[data-tl]');
            if (list.children.length > 1) baris.remove();
            else baris.querySelectorAll('input, select').forEach(x => x.value = '');
            segarkan();
        }
    });

    document.querySelector('[data-isi-contoh]').addEventListener('click', () => {
        const ada = [...root.querySelectorAll('input, select, textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Isian Tab VI yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        for (const [a, [baik, perbaikan]] of Object.entries(contoh.evaluasi)) {
            form.elements[`evaluasi[${a}][baik]`].value = baik;
            form.elements[`evaluasi[${a}][perbaikan]`].value = perbaikan;
        }
        list.innerHTML = '';
        contoh.tindak_lanjut.forEach(r => tambah(r));
        form.elements.rekomendasi.value = contoh.rekomendasi;
        form.elements.tembusan.value = contoh.tembusan;
        segarkan();
    });

    segarkan();
})();
</script>
