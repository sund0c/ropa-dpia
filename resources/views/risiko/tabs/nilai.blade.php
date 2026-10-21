@php
    use App\Http\Controllers\RisikoController as RC;
    use App\Support\MetodologiRisiko as MR;
    use App\Support\PetaRisiko as PR;

    $peta         = PR::nilai($risiko['peta'] ?? []);
    $daftarRisiko = RC::daftarRisiko($risiko);

    $levelDampak      = collect(range(1, 5))->mapWithKeys(fn ($i) => [$i => $i . ' – ' . $peta['dampak'][$i]['nama']])->all();
    $levelKemungkinan = collect(range(1, 5))->mapWithKeys(fn ($i) => [$i => $i . ' – ' . $peta['kemungkinan'][$i]['nama']])->all();

    // Isian per risiko (kunci = ID risiko Tab III): input terakhir bila validasi gagal, lalu data tersimpan
    $sumber = session()->hasOldInput() ? (array) old('set', []) : (array) ($d['set'] ?? []);
    $isian  = [];
    foreach ($sumber as $s) {
        if (is_array($s) && isset($s['risiko_id'])) $isian[$s['risiko_id']] = $s;
    }

    $belumDinilai = isset($risiko['nilai']) ? RC::risikoBelumDinilai($risiko) : [];

    $levelJs = [
        'batas' => array_values($peta['kategori_batas']),
        'warna' => collect(MR::KATEGORI)->map(fn ($k) => $k['warna'])->all(),
        'nama'  => collect(MR::KATEGORI)->map(fn ($k) => $k['nama'])->all(),
        'putih' => MR::TEKS_PUTIH,
    ];
@endphp

<style>
    .nr-list { display: flex; flex-direction: column; gap: 1rem; }
    .nr { border: 1px solid #e5e7eb; border-radius: 10px; padding: .9rem 1rem .4rem; background: #fff; }
    .nr-head { display: flex; justify-content: space-between; align-items: center; margin: -.9rem -1rem .8rem; padding: .55rem 1rem; background: #eff6ff; border-bottom: 1px solid #dbeafe; border-radius: 10px 10px 0 0; }
    .nr-kode { font-weight: bold; color: #1e3a8a; letter-spacing: .5px; }
    .nr .field select, .nr .field textarea, .nr .field input { width: 100%; box-sizing: border-box; }
    .nr-ringkas { margin: 0 0 .9rem; padding: .55rem .75rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .88rem; display: flex; flex-direction: column; gap: .3rem; }
    .nr-ringkas div { display: flex; gap: .5rem; }
    .nr-ringkas span { flex: none; width: 6.5rem; color: #6b7280; }
    .nr-ringkas b { font-weight: normal; color: #111827; }
    .nr-grid { display: grid; grid-template-columns: 1.3fr 1.3fr .6fr 1fr; gap: 0 1rem; align-items: start; }
    .nr-grid2 { display: grid; grid-template-columns: 1.3fr 1fr; gap: 0 1rem; max-width: 520px; }
    .nr-badge { display: inline-block; min-width: 2.5rem; text-align: center; padding: .45rem .7rem; border-radius: 6px; font-weight: bold; background: #e5e7eb; color: #4b5563; }
    @media (max-width: 760px) { .nr-grid, .nr-grid2 { grid-template-columns: 1fr 1fr; } }
</style>

@if ($daftarRisiko === [])
    <div class="alert warn">
        Belum ada risiko di <strong>Tab III Identifikasi Risiko</strong>. Isi dan simpan Tab III terlebih dahulu.
    </div>
@else
    <p class="hint" style="margin-top:0">
        Nilai setiap risiko dari Tab III berdasarkan kontrol yang sudah berjalan saat ini.
        <strong>IR</strong> (nilai risiko) = Nilai Dampak × Nilai Kemungkinan; level risiko mengikuti kategori di Tab II.
        Nilai dampak awal diambil dari Tab III dan dapat disesuaikan. Prioritas diisi angka (1 = paling diprioritaskan).
    </p>

    @if ($belumDinilai)
        <div class="alert warn">
            Ada risiko baru di Tab III yang belum dinilai: <strong>{{ implode(', ', $belumDinilai) }}</strong>.
            Lengkapi lalu simpan tab ini.
        </div>
    @endif

    @error('set') <div class="alert err">{{ $message }}</div> @enderror

    <div id="nilai" data-level="{{ json_encode($levelJs) }}">
        <div class="nr-list">
            @foreach (array_values($daftarRisiko) as $i => $r)
                @include('risiko.tabs._nilai-row', [
                    'i'   => $i,
                    'r'   => $r,
                    'row' => $isian[$r['id']] ?? ['dampak' => $r['dampak'] ?? null],
                ])
            @endforeach
        </div>
    </div>

    <script>
    (function () {
        const root = document.getElementById('nilai');
        const L    = JSON.parse(root.dataset.level);

        const level = (skor) => {
            for (let i = 0; i < 4; i++) if (skor <= L.batas[i]) return i + 1;
            return 5;
        };

        function warnai(el, lv) {
            el.style.background = L.warna[lv];
            el.style.color = L.putih.includes(lv) ? '#fff' : '#111';
        }

        function hitung(card) {
            const d  = parseInt(card.querySelector('[data-nr-d]').value, 10);
            const k  = parseInt(card.querySelector('[data-nr-k]').value, 10);
            const ir = card.querySelector('[data-nr-ir]');
            const lv = card.querySelector('[data-nr-level]');
            if (isNaN(d) || isNaN(k)) {
                ir.textContent = '–'; lv.textContent = '–';
                ir.style.background = lv.style.background = '';
                ir.style.color = lv.style.color = '';
                return;
            }
            const skor = d * k, l = level(skor);
            ir.textContent = skor;
            lv.textContent = L.nama[l];
            warnai(ir, l); warnai(lv, l);
        }

        root.addEventListener('change', (e) => {
            if (e.target.matches('[data-nr-d], [data-nr-k]')) hitung(e.target.closest('[data-nr]'));
        });

        root.querySelectorAll('[data-nr]').forEach(hitung);
    })();
    </script>
@endif
