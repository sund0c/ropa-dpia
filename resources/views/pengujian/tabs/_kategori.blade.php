@php
    use App\Http\Controllers\PengujianController as PC;
    use App\Support\RunbookOwasp;

    $kat   = RunbookOwasp::kategori($tab);
    $isian = $d['butir'] ?? [];
    $lama  = session()->hasOldInput();

    // Entri per butir: input terakhir bila validasi gagal, lalu data tersimpan, minimal satu entri
    $entriButir = function (int $no) use ($lama, $isian) {
        $e = $lama
            ? array_values(array_filter((array) old("butir.$no.entri", []), 'is_array'))
            : array_values((array) ($isian[$no]['entri'] ?? []));
        return $e === [] ? [[]] : $e;
    };

    $contoh = \App\Support\ContohPengujian::untuk($tab);
@endphp

<style>
    .contoh-bar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
                  margin-bottom: 1rem; padding: .6rem .9rem; background: #f0f9ff; border: 1px dashed #93c5fd; border-radius: 8px; font-size: .9rem; }
    .contoh-bar button { padding: .4rem .9rem; font-size: .88rem; background: #0369a1; }
    .kat-desk { margin: 0 0 1rem; padding: .6rem .9rem; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; font-size: .9rem; color: #374151; }
    .kat-tip { margin: -.5rem 0 1rem; font-size: .85rem; color: #6b7280; }
    .bt-list { display: flex; flex-direction: column; gap: .9rem; }
    .bt { border: 1px solid #e5e7eb; border-radius: 10px; }
    .bt-head { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; padding: .6rem .9rem; background: #eff6ff; border-bottom: 1px solid #dbeafe; border-radius: 10px 10px 0 0; }
    .bt-kode { font-weight: bold; color: #1e3a8a; letter-spacing: .5px; }
    .bt-judul { font-weight: bold; }
    .bt-metode { margin-left: auto; font-size: .75rem; border: 1px solid; border-radius: 999px; padding: .1rem .55rem; }
    .bt-pedoman { padding: .3rem .9rem; border-bottom: 1px dashed #e5e7eb; }
    .bt-pedoman summary { cursor: pointer; color: #1e40af; font-size: .88rem; padding: .3rem 0; }
    .bt-tujuan { margin: .4rem 0; font-size: .9rem; }
    .bt-pg { display: grid; grid-template-columns: 1fr 1fr; gap: 0 1.25rem; }
    .bt-lbl { font-size: .78rem; text-transform: uppercase; letter-spacing: .4px; color: #6b7280; font-weight: bold; }
    .bt-pg ol, .bt-pg ul { margin: .25rem 0 .6rem; padding-left: 1.2rem; font-size: .86rem; color: #374151; line-height: 1.5; }
    .bt-isi { padding: .75rem .9rem; }
    .bt-isi .field select, .bt-isi .field input, .bt-isi .field textarea { width: 100%; box-sizing: border-box; }
    .en-list { display: flex; flex-direction: column; gap: .6rem; }
    .en { border: 1px solid #e5e7eb; border-left: 4px solid #c7d2fe; border-radius: 8px; padding: .3rem .75rem .1rem; background: #fcfcfd; }
    .en-head { display: flex; justify-content: space-between; align-items: center; }
    .en-no { font-size: .78rem; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; }
    button.en-hapus { background: transparent; color: #b91c1c; padding: .2rem .5rem; font-size: 1rem; }
    button.en-hapus:disabled { color: #d1d5db; cursor: not-allowed; }
    .en-tambah { margin-top: .6rem; font-size: .85rem; padding: .35rem .8rem; }
    .bt-hide { display: none !important; }
    @media (max-width: 700px) { .bt-pg { grid-template-columns: 1fr; } }
</style>

<div class="contoh-bar">
    <span>{{ $kat['kode'] }} — {{ count($kat['butir']) }} butir uji.
        @if ($contoh) Muat contoh hasil (fiktif) untuk melihat cara pengisian. @else Isi hasil setiap butir sesuai pengujian. @endif</span>
    @if ($contoh) <button type="button" data-isi-contoh>Isi dengan contoh</button> @endif
</div>

<div class="kat-desk">{{ $kat['desk'] }}</div>
<p class="kat-tip">
    Tiap butir bisa memuat beberapa entri — satu entri untuk tiap endpoint/objek yang diuji.
    Gunakan <strong>+ Tambah entri</strong> untuk menambah, atau ✕ untuk menghapus (minimal satu entri).
</p>

<div id="kategori" class="bt-list">
    @foreach ($kat['butir'] as $no => $butir)
        @include('pengujian.tabs._butir', ['kode' => $kat['kode'], 'no' => $no, 'butir' => $butir, 'daftarEntri' => $entriButir($no)])
    @endforeach
</div>

<script>
(function () {
    const root   = document.getElementById('kategori');
    const form   = root.closest('form');
    const contoh = @json($contoh ?: new stdClass);
    const MAKS   = {{ PC::MAKS_ENTRI }};
    let seq = Date.now();

    // Tampilkan/sembunyikan blok isian sesuai pilihan Hasil pada satu entri
    function tampil(en) {
        const h = en.querySelector('[data-en-hasil]').value;
        const diuji = ['aman', 'rentan', 'tinjau'].includes(h);
        const set = (sel, on) => { const el = en.querySelector(sel); if (el) el.classList.toggle('bt-hide', !on); };
        set('[data-en-diuji]', diuji);
        set('[data-en-rentan]', h === 'rentan');
        set('[data-en-rekom]', h === 'rentan' || h === 'tinjau');
        set('[data-en-rekom-wajib]', h === 'rentan');
        set('[data-en-alasan]', h === 'tidak_diuji' || h === 'tidak_berlaku');
    }

    // Beri nomor entri & atur tombol hapus (minimal satu entri per butir)
    function segarkan(bt) {
        const list = bt.querySelector('[data-en-list]');
        const items = list.querySelectorAll('[data-en]');
        items.forEach((en, i) => {
            en.querySelector('[data-en-no]').textContent = 'Entri ' + (i + 1);
            en.querySelector('[data-en-hapus]').disabled = items.length <= 1;
        });
        bt.querySelector('[data-en-tambah]').disabled = items.length >= MAKS;
    }

    function tambahEntri(bt, isi) {
        const list = bt.querySelector('[data-en-list]');
        const tpl  = bt.querySelector('[data-en-template]');
        list.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__j__', String(seq++)));
        const en = list.lastElementChild;
        if (isi) for (const [f, v] of Object.entries(isi)) {
            const el = en.querySelector(`[name$="[${f}]"]`);
            if (el) el.value = v ?? '';
        }
        tampil(en);
        return en;
    }

    root.addEventListener('change', (e) => {
        if (e.target.matches('[data-en-hasil]')) tampil(e.target.closest('[data-en]'));
    });

    root.addEventListener('click', (e) => {
        const tombolTambah = e.target.closest('[data-en-tambah]');
        if (tombolTambah) {
            const bt = tombolTambah.closest('[data-bt]');
            tambahEntri(bt).querySelector('select, textarea, input')?.focus();
            segarkan(bt);
            return;
        }
        const hapus = e.target.closest('[data-en-hapus]');
        if (hapus) {
            const bt = hapus.closest('[data-bt]');
            const list = bt.querySelector('[data-en-list]');
            if (list.querySelectorAll('[data-en]').length > 1) {
                hapus.closest('[data-en]').remove();
                segarkan(bt);
            }
        }
    });

    document.querySelector('[data-isi-contoh]')?.addEventListener('click', () => {
        const ada = [...root.querySelectorAll('select, input, textarea')].some(x => x.value.trim() !== '');
        if (ada && !confirm('Isian tab ini yang sudah ada akan diganti dengan contoh. Lanjutkan?')) return;
        root.querySelectorAll('[data-bt]').forEach(bt => {
            const c = contoh[bt.dataset.btKode];
            const list = bt.querySelector('[data-en-list]');
            list.innerHTML = '';
            tambahEntri(bt, c || null);   // contoh = satu entri per butir; butir tanpa contoh → satu entri kosong
            segarkan(bt);
        });
    });

    root.querySelectorAll('[data-bt]').forEach(bt => {
        bt.querySelectorAll('[data-en]').forEach(tampil);
        segarkan(bt);
    });
})();
</script>
