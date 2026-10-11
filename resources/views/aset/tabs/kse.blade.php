@php
    use App\Support\Kse;

    $nilai = [];
    foreach (Kse::PERTANYAAN as $n => $p) {
        $nilai[$n] = (string) old("kse_$n", $d["kse_$n"] ?? '');
    }
    $hasil = Kse::hitung(collect($nilai)->mapWithKeys(fn ($v, $n) => ["kse_$n" => $v])->all());
@endphp

<style>
.kse-ketentuan { border-left: 4px solid #1e40af; background: #eff6ff; padding: .75rem 1rem; border-radius: 0 8px 8px 0; margin-bottom: 1rem; line-height: 1.6; }
.kse-list { display: flex; flex-direction: column; gap: 1rem; }
.kse-kol { border: 1px solid #e5e7eb; border-radius: 10px; padding: 1rem; }
.kse-head { display: flex; gap: .75rem; align-items: flex-start; padding-bottom: .75rem; margin-bottom: .85rem; border-bottom: 1px solid #e5e7eb; }
.kse-no { flex: none; width: 1.9rem; height: 1.9rem; border-radius: 50%; display: grid; place-items: center;
          background: #e0e7ff; color: #1e3a8a; font-weight: bold; font-size: .85rem; }
.kse-head strong { line-height: 1.45; padding-top: .2rem; }

/* 3 pilihan menyamping: sama lebar, sama tinggi */
.kse-opsi-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
.kse-opsi { display: flex; gap: .6rem; align-items: flex-start; height: 100%; box-sizing: border-box;
            border: 1px solid #e5e7eb; border-radius: 10px; padding: .8rem; cursor: pointer; background: #fff;
            transition: background .15s, border-color .15s; }
.kse-opsi:hover { border-color: #9ca3af; }
.kse-opsi input { margin-top: .2rem; flex: none; width: 1rem; height: 1rem; accent-color: #2563eb; }
.kse-isi { display: flex; flex-direction: column; gap: .45rem; font-size: .88rem; color: #374151; line-height: 1.5; min-width: 0; }
.kse-ket { margin-top: .85rem; margin-bottom: 0; }
.kse-ket textarea { font-size: .9rem; }
.kse-kol .error { display: block; margin-top: .5rem; }

.pil { align-self: flex-start; display: inline-block; padding: .1rem .6rem; border-radius: 999px; font-size: .75rem; font-weight: bold; letter-spacing: .3px; }
.pil-a { background: #fee2e2; color: #991b1b; }
.pil-b { background: #fef3c7; color: #92400e; }
.pil-c { background: #dcfce7; color: #166534; }
.pil-strategis { background: #fee2e2; color: #991b1b; }
.pil-tinggi    { background: #fef3c7; color: #92400e; }
.pil-rendah    { background: #dcfce7; color: #166534; }
.pil-kosong    { background: #e5e7eb; color: #4b5563; }
.pil-besar { font-size: .95rem; padding: .3rem 1rem; }

/* Kotak terpilih diwarnai sesuai nilai */
.kse-opsi:has(input[value=a]:checked) { border-color: #f87171; background: #fef2f2; }
.kse-opsi:has(input[value=b]:checked) { border-color: #f59e0b; background: #fffbeb; }
.kse-opsi:has(input[value=c]:checked) { border-color: #22c55e; background: #f0fdf4; }

/* Hasil: skor + kategori di kiri, catatan di kanan */
.kse-hasil { display: grid; grid-template-columns: auto 1fr; gap: 1rem 1.5rem; align-items: center;
             margin-top: 1rem; padding: 1rem 1.1rem; background: #f3f4f6; border-radius: 10px; }
.kse-angka { display: flex; flex-direction: column; gap: .5rem; }
.kse-baris { display: flex; align-items: center; gap: .6rem; }
.kse-label { font-size: .8rem; font-weight: bold; letter-spacing: .5px; color: #374151; min-width: 5.6rem; }
.kse-skor { font-size: 1.35rem; font-weight: bold; color: #111827; }
.kse-skor small { font-size: .85rem; color: #6b7280; font-weight: normal; }
.kse-catatan { border-left: 4px solid #9ca3af; padding: .55rem .9rem; background: #fff; border-radius: 0 8px 8px 0; line-height: 1.5; font-size: .92rem; }
.kse-catatan.k-strategis, .kse-catatan.k-tinggi { border-left-color: #dc2626; }
.kse-catatan.k-rendah { border-left-color: #16a34a; }

@media (max-width: 700px) {
    .kse-opsi-grid { grid-template-columns: 1fr; }
    .kse-hasil { grid-template-columns: 1fr; }
}
</style>

<div class="kse-ketentuan">
    <strong>Ketentuan penilaian:</strong> setiap jawaban bernilai <strong>5</strong>, <strong>2</strong>, atau <strong>1</strong>.
    Total skor menentukan kategori Sistem Elektronik:
    <strong>10–15 Rendah</strong> · <strong>16–34 Tinggi</strong> · <strong>35–50 Strategis</strong>.
</div>

<div class="kse-list">
    @foreach (Kse::PERTANYAAN as $n => $p)
        <section class="kse-kol">
            <header class="kse-head">
                <span class="kse-no">{{ $n }}</span>
                <strong>{{ $p['tanya'] }} <span class="req">*</span></strong>
            </header>

            <div class="kse-opsi-grid">
                @foreach ($p['opsi'] as $kunci => $teks)
                    <label class="kse-opsi">
                        <input type="radio" name="kse_{{ $n }}" value="{{ $kunci }}" data-kse @checked($nilai[$n] === $kunci)>
                        <span class="kse-isi">
                            <span class="pil pil-{{ $kunci }}">NILAI {{ Kse::NILAI[$kunci] }}</span>
                            <span>{{ $teks }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error("kse_$n") <small class="error">{{ $message }}</small> @enderror

            <div class="field kse-ket">
                <label for="ket_{{ $n }}">Keterangan <small class="hint">(opsional)</small></label>
                <textarea id="ket_{{ $n }}" name="ket_{{ $n }}" rows="2" maxlength="1000"
                    placeholder="Dasar atau alasan pemilihan jawaban">{{ old("ket_$n", $d["ket_$n"] ?? '') }}</textarea>
                @error("ket_$n") <small class="error">{{ $message }}</small> @enderror
            </div>
        </section>
    @endforeach
</div>

<div class="kse-hasil">
    <div class="kse-angka">
        <div class="kse-baris">
            <span class="kse-label">TOTAL SKOR</span>
            <span class="kse-skor" data-skor>{{ $hasil['skor'] ?? '–' }}<small> / 50</small></span>
        </div>
        <div class="kse-baris">
            <span class="kse-label">KATEGORI</span>
            <span class="pil pil-besar pil-{{ $hasil['kategori'] ?? 'kosong' }}" data-kategori>
                {{ $hasil ? strtoupper(Kse::KATEGORI[$hasil['kategori']]) : 'Belum lengkap' }}
            </span>
        </div>
    </div>

    <div class="kse-catatan {{ $hasil ? 'k-' . $hasil['kategori'] : '' }}" data-catatan>
        @if ($hasil)
            <strong>Catatan:</strong> {{ Kse::CATATAN[$hasil['kategori']] }}
        @else
            <span class="hint" data-sisa>Jawab seluruh pertanyaan untuk melihat kategori.</span>
        @endif
    </div>
</div>

<script>
(function () {
    const nilai    = @json(Kse::NILAI);
    const kategori = @json(Kse::KATEGORI);
    const catatan  = @json(Kse::CATATAN);
    const nomor    = @json(array_keys(Kse::PERTANYAAN));

    const kat = (s) => s >= 35 ? 'strategis' : (s >= 16 ? 'tinggi' : 'rendah');

    function sync() {
        let skor = 0, terjawab = 0;
        for (const n of nomor) {
            const pilih = document.querySelector(`input[name="kse_${n}"]:checked`);
            if (pilih) { skor += nilai[pilih.value]; terjawab++; }
        }
        const lengkap = terjawab === nomor.length;
        const k = lengkap ? kat(skor) : null;

        document.querySelector('[data-skor]').innerHTML = (lengkap ? skor : '–') + '<small> / 50</small>';

        const badge = document.querySelector('[data-kategori]');
        badge.className   = 'pil pil-besar pil-' + (k ?? 'kosong');
        badge.textContent = k ? kategori[k].toUpperCase() : 'Belum lengkap';

        const box = document.querySelector('[data-catatan]');
        box.className = 'kse-catatan' + (k ? ' k-' + k : '');
        if (k) {
            box.innerHTML = '<strong>Catatan:</strong> ';
            box.append(catatan[k]);
        } else {
            box.innerHTML = '<span class="hint"></span>';
            box.firstChild.textContent =
                `Jawab seluruh pertanyaan untuk melihat kategori (${terjawab}/${nomor.length} terjawab).`;
        }
    }

    document.querySelectorAll('[data-kse]').forEach(r => r.addEventListener('change', sync));
})();
</script>
