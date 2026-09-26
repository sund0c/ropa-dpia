<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RoPA & DPIA')</title>
    <style>
:root { --latar: #1e293b; }   /* abu-abu gelap kebiruan; ganti di sini bila ingin warna lain */
body { margin: 0; min-height: 100vh; font-family: system-ui, sans-serif; color: #1f2937; background: var(--latar); }
.wadah { max-width: 920px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }
.kepala-app { color: #cbd5e1; font-size: .85rem; letter-spacing: .2px; margin-bottom: .75rem; }
.kepala-app strong { color: #fff; }
.kartu { background: #fff; border-radius: 12px; padding: 1.75rem 2rem; box-shadow: 0 12px 32px rgba(0, 0, 0, .35); }
@media (max-width: 640px) { .kartu { padding: 1.25rem 1rem; border-radius: 8px; } }        .meta { background: #f3f4f6; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .tabs { display: flex; gap: .25rem; border-bottom: 2px solid #e5e7eb; margin-bottom: 1.25rem; flex-wrap: wrap; }
        .tabs a { padding: .5rem .9rem; text-decoration: none; color: #4b5563; border-radius: 6px 6px 0 0; }
        .tabs a.active { background: #1e40af; color: #fff; }
        .field { margin-bottom: 1rem; display: flex; flex-direction: column; gap: .25rem; }
        .field input, .field textarea { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .req { color: #dc2626; }
        .hint { color: #6b7280; }
        .error { color: #dc2626; }
        .alert { padding: .6rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
        .alert.ok { background: #dcfce7; } .alert.warn { background: #fef3c7; } .alert.err { background: #fee2e2; }
        button { padding: .55rem 1.1rem; border: 0; border-radius: 6px; background: #1e40af; color: #fff; cursor: pointer; }
        button.secondary { background: #6b7280; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; }

.checks { display: flex; flex-direction: column; gap: .4rem; margin-top: .25rem; }
.check { display: flex; gap: .5rem; align-items: flex-start; font-weight: normal; }
.check input { margin-top: .2rem; }
.rep-rows { counter-reset: rep; display: flex; flex-direction: column; gap: .5rem; }
.rep-row { counter-increment: rep; }
.rep-line { display: flex; gap: .5rem; align-items: center; }
.rep-line input { flex: 1; }
.rep-no::before { content: counter(rep) "."; display: inline-block; min-width: 1.6rem; color: #6b7280; }
button.icon { background: transparent; color: #dc2626; padding: .3rem .5rem; font-size: 1rem; }
button.small { padding: .35rem .8rem; font-size: .85rem; align-self: flex-start; margin-top: .25rem; }


[hidden] { display: none !important; }
.field select { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; background: #fff; }
fieldset.field { border: 0; padding: 0; margin: 0 0 1rem; }
fieldset.field legend { padding: 0; margin-bottom: .25rem; }
.rep-line.pair, .pair-head { display: grid; grid-template-columns: 1.6rem 1fr 1fr auto; gap: .5rem; align-items: center; }
.pair-head { font-size: .85rem; color: #6b7280; }
.card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; }
.card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .75rem; }
.rep-count::before { content: "#" counter(rep); }

.radios { display: flex; gap: 1.5rem; margin-top: .25rem; }

a.btn { display: inline-block; padding: .55rem 1.1rem; border-radius: 6px; background: #047857; color: #fff; text-decoration: none; }
a.btn:hover { background: #065f46; }


button.btn-hijau { background: #047857; }
button.btn-hijau:hover { background: #065f46; }
dialog.dialog { border: 0; border-radius: 10px; padding: 1.5rem; width: min(420px, 90vw); box-shadow: 0 10px 40px rgba(0,0,0,.25); }
dialog.dialog::backdrop { background: rgba(0,0,0,.45); }

.field .ro { background: #f3f4f6; color: #374151; cursor: not-allowed; }
.riwayat-line { display: grid; grid-template-columns: 1.6rem 4.5rem 9.5rem 1fr 1fr auto; gap: .5rem; align-items: center; }
.riwayat-line input { padding: .45rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; min-width: 0; }
.riwayat-head { font-size: .8rem; color: #6b7280; }

.tabel-wrap { overflow-x: auto; }
.tabel-form { width: 100%; border-collapse: collapse; }
.tabel-form th, .tabel-form td { border: 1px solid #d1d5db; padding: .5rem; vertical-align: top; text-align: left; }
.tabel-form th { background: #f3f4f6; font-size: .9rem; }
.tabel-form textarea { width: 100%; box-sizing: border-box; padding: .45rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
.tabel-form textarea.ro { background: #f3f4f6; color: #6b7280; cursor: not-allowed; }
.tabel-form tr.tidak td:first-child { color: #6b7280; }
.badge { display: inline-block; padding: .15rem .6rem; border-radius: 999px; font-size: .8rem; font-weight: bold; }
.badge.ya { background: #fee2e2; color: #991b1b; }
.badge.no { background: #e5e7eb; color: #4b5563; }

    h3.sub { font-size: 1rem; margin: 1.75rem 0 .6rem; padding-bottom: .3rem; border-bottom: 1px solid #e5e7eb; }
.ro-box { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; padding: .7rem 1rem; color: #374151; }
.ro-box ol { margin: .4rem 0 0; }
.checks.ro .check { color: #6b7280; }
.card .field textarea, .card .field input[type=text] { padding: .5rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }

.tabel-form .pilihan { display: flex; flex-direction: column; gap: .35rem; }

    .bar-default { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: .5rem; }
.matriks { border-collapse: collapse; margin-bottom: .35rem; }
.matriks th, .matriks td { border: 1px solid #374151; padding: .4rem .6rem; text-align: center; font-size: .85rem; }
.matriks th { background: #fff; font-weight: normal; }
.matriks td { width: 4.8rem; height: 2.6rem; font-weight: bold; }
.matriks th.vertikal span { writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; }
.tabel-form input[type=text] { width: 100%; box-sizing: border-box; padding: .4rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; background: rgba(255,255,255,.92); }
.tabel-form input.angka { width: 4.5rem; padding: .35rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; }
.tabel-form.dampak td { font-size: .85rem; }
.tabel-form.dampak textarea { font-size: .85rem; }

.tabel-form select { width: 100%; padding: .4rem; border: 1px solid #d1d5db; border-radius: 6px; font: inherit; background: #fff; }
.tabel-form.inheren tr.siklus-head td { background: #f3f4f6; }
.tabel-form.inheren td.skor { text-align: center; font-weight: bold; font-size: 1.05rem; vertical-align: middle; }
.tabel-form.inheren tr.tambah td { border-top: 0; padding-top: .25rem; }

.risk-block { border: 2px solid #c7d2fe; border-radius: 10px; padding: 1rem 1.1rem; margin: 1.25rem 0; background: #fafbff; }
.risk-head { display: grid; grid-template-columns: auto 1fr auto; gap: .9rem; align-items: center; padding-bottom: .75rem; border-bottom: 1px solid #e5e7eb; }
.risk-kode { font-weight: bold; font-size: 1.05rem; color: #1e3a8a; background: #e0e7ff; padding: .3rem .6rem; border-radius: 6px; }
.risk-skor { display: flex; flex-direction: column; align-items: center; gap: .2rem; }
.risk-skor small { color: #6b7280; font-size: .75rem; }
h4.sub4 { font-size: .95rem; margin: 1rem 0 .5rem; color: #1f2937; }
h5.sub5 { font-size: .9rem; margin: .5rem 0; color: #1f2937; }
.penanganan { margin-top: .75rem; padding: .6rem .8rem; border-left: 3px solid #047857; background: #f0fdf4; border-radius: 0 6px 6px 0; }
.risk-block .card { background: #fff; }

dialog.dialog-lebar { width: min(680px, 92vw); }
ol.persetujuan { padding-left: 1.3rem; line-height: 1.55; }
ol.persetujuan li { margin-bottom: .7rem; }
button.btn-merah { background: #b91c1c; }
button.btn-merah:hover { background: #991b1b; }
button:disabled { opacity: .5; cursor: not-allowed; }

.status-dpia { margin-top: 1.5rem; padding: .8rem 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb; }
.status-dpia ul { margin: .4rem 0 0; }

.data-json { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: .35rem; }
a.btn-garis { display: inline-block; padding: .55rem 1.1rem; border: 1px solid #1e40af; border-radius: 6px; color: #1e40af; text-decoration: none; }
a.btn-garis:hover { background: #eff6ff; }

    </style>
</head>
<body>
    <div class="wadah">
        <div class="kepala-app"><strong>Alat bantu membuat dokumen RoPA &amp; DPIA</strong></div>

        <main class="kartu">
            @if (session('expired'))
                <div class="alert warn">Sesi sebelumnya sudah lebih dari 24 jam dan telah dihapus. Silakan mulai baru.</div>
            @endif
            @if (session('saved'))
                <div class="alert ok">{{ session('saved') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
let repSeq = Date.now();   // indeks unik untuk baris baru dari <template>

function clearRow(row) {
    row.querySelectorAll('input[type=text], input[type=email], input[type=tel], input[type=number], input[type=date], textarea')       .forEach(el => el.value = '');
    row.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false);
    row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    row.querySelectorAll('.error').forEach(el => el.remove());
}


function updateEmpty(rep) {
    const empty = rep.querySelector(':scope > [data-empty]');
    if (empty) empty.hidden = rep.querySelectorAll(':scope > [data-rows] > [data-row]').length > 0;
}

// Opsi "data yang dikirim" hanya menampilkan jenis data yang dicentang
function syncTransferData() {
    const selected = new Set([...document.querySelectorAll('[data-jenis]:checked')].map(c => c.value));
    document.querySelectorAll('[data-transfer-option]').forEach(label => {
        const cb = label.querySelector('input');
        label.hidden = !selected.has(cb.value);
        if (label.hidden) cb.checked = false;
    });
    document.querySelectorAll('[data-transfer-empty]').forEach(el => el.hidden = selected.size > 0);
}

document.addEventListener('click', (e) => {
    const add = e.target.closest('[data-add]');
    if (add) {
        const rep  = add.closest('[data-repeater]');
        const rows = rep.querySelector(':scope > [data-rows]');
        const tpl  = rep.querySelector(':scope > template[data-template]');
        let row;

        if (tpl) {
            const ph = tpl.dataset.placeholder || '__i__';
            rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll(ph, String(repSeq++)));
            row = rows.lastElementChild;
        } else {
            row = rows.querySelector(':scope > [data-row]').cloneNode(true);
            clearRow(row);
            rows.appendChild(row);
        }

        syncTransferData();
        updateEmpty(rep);
        row.dispatchEvent(new CustomEvent('repeater:add', { bubbles: true }));
        row.querySelector('input:not([type=hidden]), textarea, select')?.focus();
        return;
    }

    const rm = e.target.closest('[data-remove]');
    if (rm) {
        const rep   = rm.closest('[data-repeater]');
        const row   = rm.closest('[data-row]');
        const min   = parseInt(rep.dataset.min ?? '1', 10);
        const count = rep.querySelectorAll(':scope > [data-rows] > [data-row]').length;

        if (count > min) row.remove();
        else clearRow(row);

        updateEmpty(rep);
    }
});

document.addEventListener('change', (e) => {
    if (e.target.matches('[data-jenis]')) syncTransferData();
});

// Tombol "Centang semua" / "Hapus semua centang"
document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-check-all]');
    if (!btn) return;
    const boxes = [...document.querySelectorAll(`input[name="${btn.dataset.checkAll}"]`)];
    const allOn = boxes.every(b => b.checked);
    boxes.forEach(b => b.checked = !allOn);
    btn.textContent = allOn ? 'Centang semua' : 'Hapus semua centang';
});


// Buka/tutup popup (<dialog>)
document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-open-dialog]');
    if (opener) {
        document.getElementById(opener.dataset.openDialog)?.showModal();
        return;
    }
    const closer = e.target.closest('[data-close-dialog]');
    if (closer) closer.closest('dialog')?.close();
});

// Tutup popup setelah form cetak dikirim (PDF terbuka di tab baru)
document.addEventListener('submit', (e) => {
    if (e.target.matches('[data-close-on-submit]')) {
        setTimeout(() => e.target.closest('dialog')?.close(), 0);
    }
});

syncTransferData();
</script>

</body>


</html>
