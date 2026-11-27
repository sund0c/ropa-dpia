<div class="data-json">
    @if (filled(session('ropa.nomor')))
        <a class="btn-garis" href="{{ route('data.ekspor') }}">⬇ Ekspor data (JSON)</a>
    @endif
    <button type="button" class="secondary" data-open-dialog="dialog-impor">⬆ Impor data (JSON)</button>
    <button type="button" class="btn-oranye" data-open-dialog="dialog-contoh">⬆ Impor contoh data</button>
</div>
<small class="hint">
    Simpan file ekspor untuk melanjutkan pengisian di lain waktu. File tidak terenkripsi, jadi simpan di tempat yang aman.
</small>

<dialog id="dialog-impor" class="dialog">
    <form method="POST" action="{{ route('data.impor') }}" enctype="multipart/form-data">
        @csrf
        <h3 style="margin-top:0">Impor Data RoPA &amp; DPIA</h3>
        <p>
            Pilih file JSON hasil ekspor aplikasi ini.
            <strong>Seluruh data di sesi ini akan digantikan</strong> oleh isi file.
        </p>
        <div class="field">
            <input type="file" name="berkas" accept=".json,application/json" required>
        </div>
        <fieldset class="field">
            <legend>Nomor dokumen RoPA/DPIA <span class="req">*</span></legend>
            <label class="check" style="display:block">
                <input type="radio" name="nomor" value="file" @checked('file' === 'file')>
                Samakan dengan nomor di file <small class="hint">(melanjutkan dokumen yang sama)</small>
            </label>
            <label class="check" style="display:block">
                <input type="radio" name="nomor" value="baru" @checked('file' === 'baru')>
                Buat nomor baru <small class="hint">(dokumen baru berdasarkan isi file)</small>
            </label>
        </fieldset>

        <p class="hint">Setiap bagian diperiksa ulang. Bagian yang tidak valid akan dilewati dan disebutkan setelah impor.</p>
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit">Impor</button>
        </div>
    </form>
</dialog>

<dialog id="dialog-contoh" class="dialog">
    <form method="POST" action="{{ route('data.impor-contoh') }}">
        @csrf
        <h3 style="margin-top:0">Impor Contoh Data</h3>
        <p>
            Memuat contoh RoPA &amp; DPIA lengkap (layanan PPID Pemprov Bali) untuk melihat cara pengisian setiap tab.
            <strong>Seluruh data di sesi ini akan digantikan</strong> oleh contoh tersebut.
        </p>
        <fieldset class="field">
            <legend>Nomor dokumen RoPA/DPIA <span class="req">*</span></legend>
            <label class="check" style="display:block">
                <input type="radio" name="nomor" value="file" @checked('baru' === 'file')>
                Samakan dengan nomor di file <small class="hint">(melanjutkan dokumen yang sama)</small>
            </label>
            <label class="check" style="display:block">
                <input type="radio" name="nomor" value="baru" @checked('baru' === 'baru')>
                Buat nomor baru <small class="hint">(dokumen baru berdasarkan isi file)</small>
            </label>
        </fieldset>

        <p class="hint">Ekspor dulu data Anda bila masih diperlukan. Contoh dapat diubah dan disesuaikan dengan layanan Anda.</p>
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit" class="btn-oranye">Muat contoh</button>
        </div>
    </form>
</dialog>
