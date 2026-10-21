@php
    use App\Http\Controllers\RisikoController as RC;
    use App\Support\MetodologiRisiko as MR;
    use App\Support\PetaRisiko as PR;

    $peta      = PR::nilai($risiko['peta'] ?? []);
    $ditangani = RC::daftarDitangani($risiko);

    $levelDampak      = collect(range(1, 5))->mapWithKeys(fn ($i) => [$i => $i . ' – ' . $peta['dampak'][$i]['nama']])->all();
    $levelKemungkinan = collect(range(1, 5))->mapWithKeys(fn ($i) => [$i => $i . ' – ' . $peta['kemungkinan'][$i]['nama']])->all();
    $warna     = collect(MR::KATEGORI)->map(fn ($k) => $k['warna'])->all();
    $namaLevel = collect(MR::KATEGORI)->map(fn ($k) => $k['nama'])->all();
    $putih     = MR::TEKS_PUTIH;

    // Isian per risiko (kunci = ID risiko): input terakhir bila validasi gagal, lalu data tersimpan
    $sumber = session()->hasOldInput() ? (array) old('set', []) : (array) ($d['set'] ?? []);
    $isian  = [];
    foreach ($sumber as $s) {
        if (is_array($s) && isset($s['risiko_id'])) $isian[$s['risiko_id']] = $s;
    }

    $belum = isset($risiko['penanganan']) ? RC::belumDitangani($risiko) : [];

    $levelJs = ['batas' => array_values($peta['kategori_batas']), 'warna' => $warna, 'nama' => $namaLevel, 'putih' => $putih];
@endphp

<style>
    .pn-list { display: flex; flex-direction: column; gap: 1rem; }
    .pn { border: 1px solid #e5e7eb; border-radius: 10px; padding: .9rem 1rem .4rem; background: #fff; }
    .pn-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; flex-wrap: wrap; margin: -.9rem -1rem .8rem; padding: .55rem 1rem; background: #eff6ff; border-bottom: 1px solid #dbeafe; border-radius: 10px 10px 0 0; }
    .pn-kode { font-weight: bold; color: #1e3a8a; letter-spacing: .5px; }
    .pn-meta { display: flex; align-items: center; gap: .6rem; font-size: .85rem; color: #374151; }
    .pn-badge { padding: .2rem .55rem; border-radius: 999px; font-weight: bold; font-size: .78rem; }
    .pn .field select, .pn .field textarea, .pn .field input[type=date] { width: 100%; box-sizing: border-box; }
    .pn-ringkas { margin: 0 0 .9rem; padding: .55rem .75rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .88rem; display: flex; flex-direction: column; gap: .3rem; }
    .pn-ringkas div { display: flex; gap: .5rem; }
    .pn-ringkas span { flex: none; width: 6.5rem; color: #6b7280; }
    .pn-ringkas b { font-weight: normal; color: #111827; }
    .pn-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1rem; }
    .pn-grid4 { display: grid; grid-template-columns: 1.3fr 1.3fr .6fr 1fr; gap: 0 1rem; align-items: start; }
    .pn-radio { display: flex; gap: 1.5rem; margin-top: .25rem; }
    .pn-residual { border-left: 4px solid #c7d2fe; background: #fafbff; padding: .75rem .9rem .1rem; margin: 0 0 .9rem; border-radius: 0 8px 8px 0; }
    .nr-badge { display: inline-block; min-width: 2.5rem; text-align: center; padding: .45rem .7rem; border-radius: 6px; font-weight: bold; background: #e5e7eb; color: #4b5563; }
    @media (max-width: 760px) { .pn-grid2 { grid-template-columns: 1fr; } .pn-grid4 { grid-template-columns: 1fr 1fr; } }
</style>

@if (! isset($risiko['nilai']))
    <div class="alert warn">
        Isi dan simpan <strong>Tab IV Nilai Risiko Saat Ini</strong> terlebih dahulu.
    </div>
@elseif ($ditangani === [])
    <div class="alert ok">
        Tidak ada risiko yang diputuskan untuk ditangani di Tab IV (seluruh keputusan penanganan = Tidak).
        Tab ini tidak perlu diisi.
    </div>
@else
    <p class="hint" style="margin-top:0">
        Rencana penanganan untuk setiap risiko yang di Tab IV diputuskan untuk ditangani, urut menurut prioritas.
        Bila setelah penanganan masih terdapat risiko residual, nilai kembali dampak dan kemungkinannya
        (<strong>RR</strong> = Dampak × Kemungkinan residual).
    </p>

    @if ($belum)
        <div class="alert warn">
            Risiko berikut belum memiliki rencana penanganan: <strong>{{ implode(', ', $belum) }}</strong>.
            Lengkapi lalu simpan tab ini.
        </div>
    @endif

    @error('set') <div class="alert err">{{ $message }}</div> @enderror

    <div id="penanganan" data-level="{{ json_encode($levelJs) }}">
        <div class="pn-list">
            @foreach (array_values($ditangani) as $i => $r)
                @include('risiko.tabs._penanganan-row', ['i' => $i, 'r' => $r, 'row' => $isian[$r['id']] ?? []])
            @endforeach
        </div>
    </div>

    <script>
    (function () {
        const root = document.getElementById('penanganan');
        const L    = JSON.parse(root.dataset.level);

        const level = (skor) => {
            for (let i = 0; i < 4; i++) if (skor <= L.batas[i]) return i + 1;
            return 5;
        };

        function hitung(card) {
            const d  = parseInt(card.querySelector('[data-pn-d]').value, 10);
            const k  = parseInt(card.querySelector('[data-pn-k]').value, 10);
            const rr = card.querySelector('[data-pn-rr]');
            const lv = card.querySelector('[data-pn-level]');
            if (isNaN(d) || isNaN(k)) {
                rr.textContent = '–'; lv.textContent = '–';
                rr.style.background = lv.style.background = '';
                rr.style.color = lv.style.color = '';
                return;
            }
            const skor = d * k, l = level(skor);
            rr.textContent = skor;
            lv.textContent = L.nama[l];
            [rr, lv].forEach(el => {
                el.style.background = L.warna[l];
                el.style.color = L.putih.includes(l) ? '#fff' : '#111';
            });
        }

        function tampil(card) {
            const res = card.querySelector('[data-pn-residual]:checked');
            card.querySelector('[data-pn-blok-residual]').hidden = !(res && res.value === 'ya');
            card.querySelector('[data-pn-blok-kontrol]').hidden = card.querySelector('[data-pn-status]').value !== 'mitigasi';
        }

        root.addEventListener('change', (e) => {
            const card = e.target.closest('[data-pn]');
            if (!card) return;
            if (e.target.matches('[data-pn-residual], [data-pn-status]')) tampil(card);
            if (e.target.matches('[data-pn-d], [data-pn-k]')) hitung(card);
        });

        root.querySelectorAll('[data-pn]').forEach(c => { tampil(c); hitung(c); });
    })();
    </script>
@endif
