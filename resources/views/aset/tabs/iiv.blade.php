@php
    $nilai = [];
    foreach ($iiv as $k => $x) {
        $nilai[$k] = (string) old($k, $d[$k] ?? '');
    }
    $status = \App\Http\Controllers\AsetController::hitungIiv($nilai);
    $jumlah = array_count_values(array_filter($nilai));
@endphp

<style>
.iiv-ketentuan { border-left: 4px solid #1e40af; background: #eff6ff; padding: .75rem 1rem; border-radius: 0 8px 8px 0; margin-bottom: 1rem; line-height: 1.5; }

/* 5 card ke bawah, lebar penuh */
.iiv-list { display: flex; flex-direction: column; gap: 1rem; }
.iiv-kol { border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem; }
.iiv-head { display: flex; gap: .75rem; align-items: center; padding-bottom: .75rem; margin-bottom: .85rem; border-bottom: 1px solid #e5e7eb; }
.iiv-head strong { display: block; }
.iiv-head small { color: #6b7280; font-size: .82rem; }

/* 3 pilihan menyamping: sama lebar, sama tinggi (grid baris tunggal → stretch) */
.iiv-opsi-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
.iiv-opsi { display: flex; gap: .6rem; align-items: flex-start; height: 100%; box-sizing: border-box;
            border: 1px solid #e5e7eb; border-radius: 10px; padding: .8rem; cursor: pointer; background: #fff;
            transition: background .15s, border-color .15s; }
.iiv-opsi:hover { border-color: #9ca3af; }
.iiv-opsi input { margin-top: .2rem; flex: none; width: 1rem; height: 1rem; accent-color: #2563eb; }
.iiv-isi { display: flex; flex-direction: column; gap: .45rem; font-size: .88rem; color: #374151; line-height: 1.5; min-width: 0; }
.iiv-kol .error { display: block; margin-top: .5rem; }

.pil { align-self: flex-start; display: inline-block; padding: .1rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: bold; letter-spacing: .3px; }
.pil-kritis   { background: #fee2e2; color: #991b1b; }
.pil-terbatas { background: #fef3c7; color: #92400e; }
.pil-minor    { background: #dcfce7; color: #166534; }
.pil-vital    { background: #fee2e2; color: #991b1b; }
.pil-bukan    { background: #dcfce7; color: #166534; }
.pil-kosong   { background: #e5e7eb; color: #4b5563; }
.pil-besar { font-size: .95rem; padding: .3rem 1rem; }

/* Kotak terpilih diwarnai sesuai level */
.iiv-opsi:has(input[value=kritis]:checked)   { border-color: #f87171; background: #fef2f2; }
.iiv-opsi:has(input[value=terbatas]:checked) { border-color: #f59e0b; background: #fffbeb; }
.iiv-opsi:has(input[value=minor]:checked)    { border-color: #22c55e; background: #f0fdf4; }

/* Warna kode per dimensi */

.iiv-hasil { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; padding: .85rem 1rem; background: #f3f4f6; border-radius: 10px; }
.iiv-hasil-label { font-size: .8rem; font-weight: bold; letter-spacing: .5px; color: #374151; }
.iiv-bssn { margin-top: 1rem; border: 2px solid #dc2626; background: #fef2f2; color: #7f1d1d; padding: .9rem 1.1rem; border-radius: 10px; line-height: 1.55; }

/* Layar sempit: pilihan ditumpuk */
@media (max-width: 700px) {
    .iiv-opsi-grid { grid-template-columns: 1fr; }
}
</style>

<div class="iiv-ketentuan">
    <strong>Ketentuan:</strong> Aset <strong>termasuk Infrastruktur Informasi Vital (IIV)</strong> apabila minimal
    <strong>1 dimensi bernilai Kritis</strong>, atau minimal <strong>3 dimensi bernilai Terbatas</strong>.
</div>

<p class="hint" style="margin-top:0">
    Pilih satu nilai untuk setiap dimensi berdasarkan dampak terburuk bila aset ini terganggu atau gagal berfungsi.
</p>

<div class="iiv-list">
    @foreach ($iiv as $k => $x)
        <section class="iiv-kol iiv-{{ $k }}">
            <header class="iiv-head">
                <div>
                    <strong>{{ $x['judul'] }} <span class="req">*</span></strong>
                    <small>{{ $x['sub'] }}</small>
                </div>
            </header>

            <div class="iiv-opsi-grid">
                @foreach ($x['opsi'] as $lv => $desk)
                    <label class="iiv-opsi">
                        <input type="radio" name="{{ $k }}" value="{{ $lv }}" data-iiv @checked($nilai[$k] === $lv)>
                        <span class="iiv-isi">
                            <span class="pil pil-{{ $lv }}">{{ strtoupper($levelIiv[$lv]) }}</span>
                            <span>{{ $desk }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            @error($k) <small class="error">{{ $message }}</small> @enderror
        </section>
    @endforeach
</div>

<div class="iiv-hasil">
    <span class="iiv-hasil-label">STATUS IIV:</span>
    <span class="pil pil-besar pil-{{ $status ?? 'kosong' }}" data-status>
        {{ $status ? $statusIiv[$status] : 'Belum lengkap' }}
    </span>
    <small class="hint" data-jumlah>
        Kritis: {{ $jumlah['kritis'] ?? 0 }} · Terbatas: {{ $jumlah['terbatas'] ?? 0 }} · Minor: {{ $jumlah['minor'] ?? 0 }}
    </small>
</div>

<div class="iiv-bssn" data-bssn @unless ($status === 'vital') hidden @endunless>
    <strong>Catatan:</strong> Aset ini harus <strong>segera dilaporkan ke BSSN</strong> untuk dilakukan peninjauan dan
    penetapan secara resmi oleh BSSN. BSSN akan ikut melakukan upaya penanganan terhadap seluruh aset yang termasuk
    dalam Infrastruktur Informasi Vital.
</div>

<script>
(function () {
    const status  = @json($statusIiv);
    const dimensi = @json(array_keys($iiv));

    function sync() {
        const jumlah = { kritis: 0, terbatas: 0, minor: 0 };
        let lengkap = true;

        for (const k of dimensi) {
            const pilih = document.querySelector(`input[name="${k}"]:checked`);
            if (pilih) jumlah[pilih.value]++;
            else lengkap = false;
        }

        const hasil = !lengkap ? null
            : (jumlah.kritis >= 1 || jumlah.terbatas >= 3) ? 'vital' : 'bukan';

        const badge = document.querySelector('[data-status]');
        badge.className   = 'pil pil-besar pil-' + (hasil ?? 'kosong');
        badge.textContent = hasil ? status[hasil] : 'Belum lengkap';

        document.querySelector('[data-jumlah]').textContent =
            `Kritis: ${jumlah.kritis} · Terbatas: ${jumlah.terbatas} · Minor: ${jumlah.minor}`;
        document.querySelector('[data-bssn]').hidden = hasil !== 'vital';
    }

    document.querySelectorAll('[data-iiv]').forEach(r => r.addEventListener('change', sync));
})();
</script>
