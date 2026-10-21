@php
    use App\Support\KatalogRisiko as KR;
    use App\Support\MetodologiRisiko as MR;
    use App\Support\PetaRisiko as PR;

    $klas    = $risiko['informasi']['klasifikasi'] ?? null;
    $katalog = KR::untuk($klas);
    $peta    = PR::nilai($risiko['peta'] ?? []);

    $areaDampak  = collect(PR::DIMENSI)->map(fn ($x) => $x['label'])->all();
    $levelDampak = collect(range(1, 5))->mapWithKeys(fn ($i) => [$i => $i . ' – ' . $peta['dampak'][$i]['nama']])->all();

    // Kriteria dampak per [level][area] dari Tab II, untuk keterangan di bawah tiap risiko
    $kriteria = [];
    foreach (range(1, 5) as $i) {
        foreach (array_keys(PR::DIMENSI) as $a) {
            $kriteria[$i][$a] = $peta['dampak'][$i][$a] ?? '';
        }
    }

    $baris = session()->hasOldInput()
        ? array_values(array_filter((array) old('risiko', []), 'is_array'))
        : ($d['risiko'] ?? [['id' => 'r1']]);
    if ($baris === []) $baris = [['id' => 'r1']];
@endphp

<style>
    .idr-info { display: flex; gap: .5rem 1.5rem; flex-wrap: wrap; font-size: .88rem; color: #374151; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: .6rem .9rem; margin-bottom: 1rem; }
    .idr-list { display: flex; flex-direction: column; gap: 1rem; }
    .idr { border: 1px solid #e5e7eb; border-radius: 10px; padding: .9rem 1rem .4rem; background: #fff; }
    .idr-head { display: flex; justify-content: space-between; align-items: center; margin: -.9rem -1rem .8rem; padding: .55rem 1rem; background: #eff6ff; border-bottom: 1px solid #dbeafe; border-radius: 10px 10px 0 0; }
    .idr-kode { font-weight: bold; color: #1e3a8a; letter-spacing: .5px; }
    button.idr-hapus { background: transparent; color: #b91c1c; padding: .25rem .5rem; font-size: .85rem; }
    button.idr-hapus:disabled { color: #9ca3af; }
    .idr .field input[type=text], .idr .field select { width: 100%; box-sizing: border-box; }
    .idr-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr; gap: 0 1rem; }
    .idr-kriteria { margin: -.25rem 0 .75rem; padding: .5rem .75rem; border-left: 4px solid #9ca3af; background: #f9fafb; border-radius: 0 6px 6px 0; font-size: .85rem; color: #374151; }
    .idr-tambah { margin-top: .75rem; }
    @media (max-width: 700px) { .idr-grid { grid-template-columns: 1fr; } }
</style>

<div class="idr-info">
    <span><strong>Klasifikasi aset:</strong> {{ $klas ? \App\Http\Controllers\AsetController::KLASIFIKASI[$klas] : 'belum diisi (Tab I)' }}</span>
    <span><strong>Saran ancaman:</strong> {{ count($katalog) }} ancaman sesuai klasifikasi</span>
    <span><strong>Kriteria dampak:</strong> dari Tab II{{ isset($risiko['peta']) ? '' : ' (nilai default, Tab II belum disimpan)' }}</span>
</div>

<p class="hint" style="margin-top:0">
    Setiap kartu adalah satu risiko: ancaman yang dapat terjadi, kerentanan yang memungkinkannya, kategori risiko,
    serta area dan tingkat dampaknya. Ancaman dan kerentanan dapat dipilih dari saran atau ditulis sendiri.
    Minimal satu risiko.
</p>

@error('risiko') <div class="alert err">{{ $message }}</div> @enderror

<datalist id="katalog-ancaman">
    @foreach (array_keys($katalog) as $a) <option value="{{ $a }}"></option> @endforeach
</datalist>

<div id="identifikasi"
     data-katalog="{{ json_encode($katalog, JSON_UNESCAPED_UNICODE) }}"
     data-kriteria="{{ json_encode($kriteria, JSON_UNESCAPED_UNICODE) }}"
     data-warna="{{ json_encode(collect(MR::KATEGORI)->map(fn ($k) => $k['warna'])->all()) }}">

    <div class="idr-list" data-idr-list>
        @foreach ($baris as $i => $row)
            @include('risiko.tabs._identifikasi-row', ['i' => $i, 'row' => $row])
        @endforeach
    </div>

    <template data-idr-template>
        @include('risiko.tabs._identifikasi-row', ['i' => '__i__', 'row' => []])
    </template>

    <button type="button" class="secondary idr-tambah" data-idr-tambah>+ Tambah risiko</button>
</div>

<script>
(function () {
    const root     = document.getElementById('identifikasi');
    const list     = root.querySelector('[data-idr-list]');
    const tpl      = root.querySelector('[data-idr-template]');
    const katalog  = JSON.parse(root.dataset.katalog);
    const kriteria = JSON.parse(root.dataset.kriteria);
    const warna    = JSON.parse(root.dataset.warna);
    let seq = Date.now();

    // Saran kerentanan menyesuaikan ancaman yang dipilih
    function isiSaranKerentanan(card) {
        const ancaman = card.querySelector('[data-idr-ancaman]').value.trim();
        const dl = card.querySelector('[data-idr-dl]');
        const saran = katalog[ancaman] || [...new Set(Object.values(katalog).flat())];
        dl.innerHTML = '';
        saran.forEach(v => { const o = document.createElement('option'); o.value = v; dl.appendChild(o); });
    }

    // Kriteria dampak dari Tab II sesuai area + level yang dipilih
    function tampilKriteria(card) {
        const area = card.querySelector('[data-idr-area]').value;
        const lv   = card.querySelector('[data-idr-dampak]').value;
        const box  = card.querySelector('[data-idr-kriteria]');
        const teks = (kriteria[lv] || {})[area];
        box.hidden = !teks;
        if (teks) {
            box.textContent = 'Kriteria Tab II: ' + teks;
            box.style.borderLeftColor = warna[lv];
        }
    }

    function nomori() {
        const cards = list.querySelectorAll('[data-idr]');
        cards.forEach((c, n) => c.querySelector('[data-idr-kode]').textContent = 'R' + String(n + 1).padStart(2, '0'));
        cards.forEach(c => c.querySelector('[data-idr-hapus]').disabled = cards.length <= 1);
    }

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-idr-tambah]')) {
            const n = String(seq++);
            list.insertAdjacentHTML('beforeend',
                tpl.innerHTML.replaceAll('__i__', n).replace('__id__', 'r' + n.slice(-9) + Math.floor(Math.random() * 1000)));
            const card = list.lastElementChild;
            isiSaranKerentanan(card);
            nomori();
            card.querySelector('[data-idr-ancaman]').focus();
            return;
        }
        const hapus = e.target.closest('[data-idr-hapus]');
        if (hapus && list.querySelectorAll('[data-idr]').length > 1) {
            hapus.closest('[data-idr]').remove();
            nomori();
        }
    });

    root.addEventListener('input', (e) => {
        if (e.target.matches('[data-idr-ancaman]')) isiSaranKerentanan(e.target.closest('[data-idr]'));
    });
    root.addEventListener('change', (e) => {
        if (e.target.matches('[data-idr-area], [data-idr-dampak]')) tampilKriteria(e.target.closest('[data-idr]'));
    });

    list.querySelectorAll('[data-idr]').forEach(c => { isiSaranKerentanan(c); tampilKriteria(c); });
    nomori();
})();
</script>
