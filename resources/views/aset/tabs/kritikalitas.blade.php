@php
    $nilai = [];
    foreach ($cia as $k => $x) {
        $nilai[$k] = (string) old($k, $d[$k] ?? '');
    }
    $krit  = \App\Http\Controllers\AsetController::hitungKritikalitas($nilai);
    $wajib = in_array($krit, ['sedang', 'tinggi'], true);
@endphp

<style>
.cia-ketentuan { border-left: 4px solid #b45309; background: #fffbeb; padding: .75rem 1rem; border-radius: 0 8px 8px 0; margin-bottom: 1rem; line-height: 1.5; }

/* Grid utama: 3 kolom sama lebar; baris = header + 3 pilihan + pesan error.
   Ketiga baris pilihan memakai 1fr → semua kotak pilihan setinggi kotak tertinggi. */
.cia-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-template-rows: auto repeat(3, 1fr) auto;
    column-gap: 1rem;
}
.cia-kol {
    grid-row: span 5;
    display: grid;
    grid-template-rows: subgrid;          /* baris kolom mengikuti baris grid utama */
    row-gap: .6rem;
    border: 1px solid #e5e7eb; border-radius: 10px; padding: .9rem; min-width: 0;
}
.cia-head { display: flex; gap: .65rem; align-items: center; padding-bottom: .6rem; border-bottom: 1px solid #e5e7eb; }
.cia-head strong { display: block; }
.cia-head small { color: #6b7280; font-size: .8rem; }
.cia-huruf { width: 1.9rem; height: 1.9rem; border-radius: 6px; display: grid; place-items: center; font-weight: bold; flex: none; }
.cia-opsi { display: flex; gap: .6rem; align-items: flex-start; height: 100%; box-sizing: border-box;
            border: 1px solid #e5e7eb; border-radius: 10px; padding: .75rem; cursor: pointer; background: #fff;
            transition: background .15s, border-color .15s; }
.cia-opsi:hover { border-color: #9ca3af; }
.cia-opsi input { margin-top: .2rem; flex: none; width: 1rem; height: 1rem; }
.cia-isi { display: flex; flex-direction: column; gap: .4rem; font-size: .88rem; color: #374151; line-height: 1.45; }
.cia-err { min-height: 0; }
.pil { align-self: flex-start; display: inline-block; padding: .1rem .6rem; border-radius: 999px; font-size: .8rem; font-weight: bold; }
.pil-rendah { background: #dcfce7; color: #166534; }
.pil-sedang { background: #fef3c7; color: #92400e; }
.pil-tinggi { background: #fee2e2; color: #991b1b; }
.pil-kosong { background: #e5e7eb; color: #4b5563; }
.pil-besar { font-size: 1rem; padding: .3rem 1rem; }
.cia-c .cia-huruf { background: #ede9fe; color: #6d28d9; }
.cia-i .cia-huruf { background: #dbeafe; color: #1d4ed8; }
.cia-a .cia-huruf { background: #dcfce7; color: #15803d; }
.cia-c input { accent-color: #7c3aed; }
.cia-i input { accent-color: #2563eb; }
.cia-a input { accent-color: #16a34a; }
.cia-c .cia-opsi:has(input:checked) { border-color: #a78bfa; background: #faf5ff; }
.cia-i .cia-opsi:has(input:checked) { border-color: #3b82f6; background: #eff6ff; }
.cia-a .cia-opsi:has(input:checked) { border-color: #22c55e; background: #f0fdf4; }
.cia-hasil { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; padding: .85rem 1rem; background: #f3f4f6; border-radius: 10px; }
.cia-hasil-label { font-size: .8rem; font-weight: bold; letter-spacing: .5px; color: #374151; }

/* Layar sempit: kolom ditumpuk; kotak pilihan di dalam satu kolom tetap sama tinggi */
@media (max-width: 760px) {
    .cia-grid { grid-template-columns: 1fr; grid-template-rows: none; row-gap: 1rem; }
    .cia-kol  { grid-row: auto; grid-template-rows: auto repeat(3, 1fr) auto; }
}
</style>

<div class="cia-ketentuan">
    <strong>Ketentuan:</strong> Aset dengan nilai kritikalitas <strong>Tinggi</strong> dan <strong>Sedang</strong>
    <strong>WAJIB</strong> dibuatkan dokumen manajemen risiko yang berkelanjutan.
</div>

<p class="hint" style="margin-top:0">
    Pilih satu nilai untuk setiap aspek berdasarkan dampak terburuk bila aspek tersebut terganggu.
    Kritikalitas diambil dari nilai tertinggi di antara ketiganya.
</p>

<div class="cia-grid">
    @foreach ($cia as $k => $x)
        <section class="cia-kol cia-{{ $k }}">
            <header class="cia-head">
                <span class="cia-huruf">{{ $x['huruf'] }}</span>
                <div>
                    <strong>{{ $x['judul'] }} <span class="req">*</span></strong>
                    <small>{{ $x['sub'] }}</small>
                </div>
            </header>

            @foreach ($x['opsi'] as $lv => $desk)
                <label class="cia-opsi">
                    <input type="radio" name="{{ $k }}" value="{{ $lv }}" data-cia @checked($nilai[$k] === $lv)>
                    <span class="cia-isi">
                        <span class="pil pil-{{ $lv }}">{{ $level[$lv] }}</span>
                        <span>{{ $desk }}</span>
                    </span>
                </label>
            @endforeach

            <div class="cia-err">
                @error($k) <small class="error">{{ $message }}</small> @enderror
            </div>
        </section>
    @endforeach
</div>

<div class="cia-hasil">
    <span class="cia-hasil-label">KRITIKALITAS:</span>
    <span class="pil pil-besar pil-{{ $krit ?? 'kosong' }}" data-krit>
        {{ $krit ? $level[$krit] : 'Belum lengkap' }}
    </span>
    <small class="hint">= max(C, I, A)</small>
</div>

<div class="alert warn" style="margin-top:1rem" data-wajib-mr @unless ($wajib) hidden @endunless>
    Aset ini memiliki kritikalitas <strong data-krit-teks>{{ $krit ? $level[$krit] : '' }}</strong>,
    sehingga <strong>WAJIB</strong> dibuatkan dokumen manajemen risiko yang berkelanjutan.
</div>

<script>
(function () {
    const level = @json($level);
    const bobot = { rendah: 1, sedang: 2, tinggi: 3 };
    const aspek = @json(array_keys($cia));

    function hitung() {
        let maks = 0;
        for (const k of aspek) {
            const pilih = document.querySelector(`input[name="${k}"]:checked`);
            if (!pilih) return null;
            maks = Math.max(maks, bobot[pilih.value]);
        }
        return Object.keys(bobot).find(l => bobot[l] === maks);
    }

    function sync() {
        const krit  = hitung();
        const badge = document.querySelector('[data-krit]');
        badge.className   = 'pil pil-besar pil-' + (krit ?? 'kosong');
        badge.textContent = krit ? level[krit] : 'Belum lengkap';

        const wajib = krit === 'sedang' || krit === 'tinggi';
        document.querySelector('[data-wajib-mr]').hidden = !wajib;
        document.querySelector('[data-krit-teks]').textContent = krit ? level[krit] : '';
    }

    document.querySelectorAll('[data-cia]').forEach(r => r.addEventListener('change', sync));
})();
</script>
