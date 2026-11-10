<div class="data-json">
    @if (filled(session('aset.nomor')))
        <a class="btn-garis" href="{{ route('aset.data.ekspor') }}">⬇ Ekspor data (JSON)</a>
    @endif
    <button type="button" class="secondary" data-open-dialog="dialog-impor">⬆ Impor data (JSON)</button>
    <button type="button" class="btn-oranye" data-open-dialog="dialog-contoh">⬆ Impor contoh data</button>
    <button type="button" class="btn-hijau" data-open-dialog="dialog-register">Aset Register (PDF)</button>
</div>
<small class="hint">
    Simpan file ekspor untuk melanjutkan pengisian di lain waktu. File tidak terenkripsi, jadi simpan di tempat yang aman.
</small>

<dialog id="dialog-impor" class="dialog">
    <form method="POST" action="{{ route('aset.data.impor') }}" enctype="multipart/form-data">
        @csrf
        <h3 style="margin-top:0">Impor Data Aset</h3>
        <p>
            Pilih file JSON hasil ekspor aplikasi ini.
            <strong>Seluruh data di sesi ini akan digantikan</strong> oleh isi file, termasuk nomor aset.
        </p>
        <div class="field">
            <input type="file" name="berkas" accept=".json,application/json" required>
        </div>
        <p class="hint">Setiap bagian diperiksa ulang. Bagian yang tidak valid akan dilewati dan disebutkan setelah impor.</p>
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit">Impor</button>
        </div>
    </form>
</dialog>

@php $contohAset = \App\Http\Controllers\AsetDataController::daftarContoh(); @endphp
<dialog id="dialog-contoh" class="dialog">
    <form method="POST" action="{{ route('aset.data.impor-contoh') }}">
        @csrf
        <h3 style="margin-top:0">Impor Contoh Data Aset</h3>
        <p>
            Pilih klasifikasi aset untuk melihat contoh pengisian setiap tab.
            <strong>Seluruh data aset di sesi ini akan digantikan</strong> oleh contoh tersebut.
        </p>
        <div class="field contoh-aset">
            @forelse ($contohAset as $k => $nama)
                <label style="display:block; margin:.35rem 0; font-weight:normal">
                    <input type="radio" name="klasifikasi" value="{{ $k }}" required>
                    <strong>{{ \App\Http\Controllers\AsetController::KLASIFIKASI[$k] }}</strong>
                    ({{ \App\Http\Controllers\AsetController::KODE_KLASIFIKASI[$k] }})
                    @if ($nama) — <span class="hint">contoh: {{ $nama }}</span> @endif
                </label>
            @empty
                <p class="hint">Belum ada contoh data yang tersedia.</p>
            @endforelse
        </div>
        <p class="hint">Ekspor dulu data Anda bila masih diperlukan. Contoh dapat diubah dan disesuaikan dengan aset Anda.</p>
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit" class="btn-oranye">Muat contoh</button>
        </div>
    </form>
</dialog>

<dialog id="dialog-register" class="dialog dialog-lebar">
    <form method="POST" action="{{ route('aset.register.pdf') }}" enctype="multipart/form-data" data-register>
        @csrf
        <h3 style="margin-top:0">Aset Register (PDF)</h3>
        <p>
            Pilih file JSON hasil ekspor aset (boleh banyak sekaligus, berbagai klasifikasi). Sistem merekap
            No, Klasifikasi, Kode, Nama, Kritikalitas, Status IIV, dan Kategori SE, diurutkan menurut klasifikasi aset,
            beserta lampiran rincian setiap aset. Satu tanda tangan Pimpinan mengesahkan seluruh dokumen.
            Data sesi ini tidak berubah.
        </p>
        <div class="field">
            <label for="reg-berkas">File JSON aset <span class="req">*</span></label>
            <input id="reg-berkas" type="file" accept=".json,application/json" multiple required data-register-berkas>
            <small class="hint" data-register-info>Semua file harus dari instansi pemilik aset yang sama dan sudah dinilai lengkap.</small>
        </div>
        <textarea name="berkas_json" hidden></textarea>

        <div class="field">
            <strong>Klasifikasi yang dicetak</strong>
            <small class="hint">Hapus centang untuk mencetak sebagian klasifikasi saja (berguna bila aset sangat banyak).</small>
            <div style="display:flex; flex-wrap:wrap; gap:.25rem 1rem; margin-top:.3rem">
                @foreach (\App\Http\Controllers\AsetController::KLASIFIKASI as $k => $nm)
                    <label class="check">
                        <input type="checkbox" name="klasifikasi[]" value="{{ $k }}" checked>
                        <span>{{ $nm }} ({{ \App\Http\Controllers\AsetController::KODE_KLASIFIKASI[$k] }})</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="field">
            <label class="check">
                <input type="hidden" name="lampiran" value="0">
                <input type="checkbox" name="lampiran" value="1" checked>
                <span>Sertakan lampiran rincian setiap aset (Identifikasi dan Penilaian Aset TIK)</span>
            </label>
            <small class="hint">Tanpa lampiran, PDF hanya berisi daftar aset. Dengan lampiran, sekitar 3 halaman per aset.</small>
        </div>

        <div class="row">
            <div class="field">
                <label for="reg-lokasi">Lokasi Penetapan <span class="req">*</span></label>
                <input id="reg-lokasi" name="lokasi" required maxlength="100" placeholder="Contoh: Denpasar">
            </div>
            <div class="field">
                <label for="reg-tanggal">Tanggal Penetapan <span class="req">*</span></label>
                <input id="reg-tanggal" name="tanggal" type="date" required value="{{ now()->format('Y-m-d') }}">
            </div>
        </div>

        <div class="field" data-tte-item>
            <strong>Penandatangan:</strong> Pimpinan Instansi Pemilik Aset
            <small class="hint">(nama &amp; jabatan diambil dari file JSON)</small>
            <label class="check" style="margin-top:.3rem">
                <input type="checkbox" name="tte[]" value="pimpinan" data-tte-cek>
                <span>Menggunakan TTE</span>
            </label>
            <div class="tte-unggah" hidden>
                <input type="file" name="tte_label[pimpinan]" accept="image/png,image/jpeg" disabled>
                <small class="hint">Label TTE (PNG/JPG, maks. {{ \App\Support\LabelTte::MAKS_KB }} KB)</small>
            </div>
        </div>

        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit" class="btn-hijau">Cetak PDF</button>
        </div>
    </form>
    @include('partials.tte-js')
</dialog>

<script>
// Aset Register: baca semua file JSON di browser, kirim sebagai satu field teks
// (menghindari batas jumlah file per unggahan pada PHP).
(function () {
    const form = document.querySelector('[data-register]');
    if (!form) return;
    const MAKS_TOTAL = 7 * 1024 * 1024;   // di bawah batas post_max_size bawaan PHP (8 MB)
    const input = form.querySelector('[data-register-berkas]');
    const info  = form.querySelector('[data-register-info]');

    input.addEventListener('change', () => {
        if (input.files.length) info.textContent = input.files.length + ' file dipilih.';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const files = [...input.files];
        if (!files.length) { input.reportValidity(); return; }

        const total = files.reduce((n, f) => n + f.size, 0);
        if (total > MAKS_TOTAL) {
            info.textContent = 'Total ukuran file terlalu besar (maks. 7 MB). Kurangi jumlah file.';
            return;
        }

        info.textContent = 'Membaca ' + files.length + ' file…';
        const isi = await Promise.all(files.map(f => f.text().then(t => ({ nama: f.name, isi: t }))));
        form.elements.berkas_json.value = JSON.stringify(isi);
        info.textContent = files.length + ' file dikirim. PDF akan terunduh bila semua file valid.';

        HTMLFormElement.prototype.submit.call(form);
        setTimeout(() => form.closest('dialog')?.close(), 0);
    });
})();
</script>
