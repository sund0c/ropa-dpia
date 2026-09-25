<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'RoPA & DPIA')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 860px; margin: 2rem auto; padding: 0 1rem; color: #1f2937; }
        .meta { background: #f3f4f6; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
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


    </style>
</head>
<body>
    @if (session('expired'))
        <div class="alert warn">Sesi sebelumnya sudah lebih dari 24 jam dan telah dihapus. Silakan mulai baru.</div>
    @endif
    @if (session('saved'))
        <div class="alert ok">{{ session('saved') }}</div>
    @endif

    @yield('content')
</body>

<script>
let repSeq = Date.now();   // indeks unik untuk baris baru dari <template>

function clearRow(row) {
    row.querySelectorAll('input[type=text], input[type=email], input[type=tel], input[type=number], textarea')
       .forEach(el => el.value = '');
    row.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false);
    row.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
    row.querySelectorAll('.error').forEach(el => el.remove());
}

function updateEmpty(rep) {
    const empty = rep.querySelector('[data-empty]');
    if (empty) empty.hidden = rep.querySelectorAll('[data-rows] > [data-row]').length > 0;
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
        const rows = rep.querySelector('[data-rows]');
        const tpl  = rep.querySelector('template[data-template]');
        let row;

        if (tpl) {
            rows.insertAdjacentHTML('beforeend', tpl.innerHTML.replaceAll('__i__', String(repSeq++)));
            row = rows.lastElementChild;
        } else {
            row = rows.querySelector('[data-row]').cloneNode(true);
            clearRow(row);
            rows.appendChild(row);
        }

        syncTransferData();
        updateEmpty(rep);
        row.querySelector('input:not([type=hidden]), textarea, select')?.focus();
        return;
    }

    const rm = e.target.closest('[data-remove]');
    if (rm) {
        const rep  = rm.closest('[data-repeater]');
        const row  = rm.closest('[data-row]');
        const min  = parseInt(rep.dataset.min ?? '1', 10);
        const count = rep.querySelectorAll('[data-rows] > [data-row]').length;

        if (count > min) row.remove();
        else clearRow(row);   // jangan sampai di bawah jumlah minimum

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


</html>
