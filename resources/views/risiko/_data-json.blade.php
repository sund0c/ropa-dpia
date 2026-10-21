<div class="data-json">
    @if (filled(session('risiko.nomor')))
        <a class="btn-garis" href="{{ route('risiko.data.ekspor') }}">⬇ Ekspor data (JSON)</a>
    @endif
    <button type="button" class="secondary" data-open-dialog="dialog-impor">⬆ Impor data (JSON)</button>
</div>
<small class="hint">
    Simpan file ekspor untuk melanjutkan pengisian di lain waktu. File tidak terenkripsi, jadi simpan di tempat yang aman.
</small>

<dialog id="dialog-impor" class="dialog">
    <form method="POST" action="{{ route('risiko.data.impor') }}" enctype="multipart/form-data">
        @csrf
        <h3 style="margin-top:0">Impor Data Manajemen Risiko</h3>
        <p>
            Pilih file JSON hasil ekspor modul ini.
            <strong>Seluruh data manajemen risiko di sesi ini akan digantikan</strong> oleh isi file.
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
