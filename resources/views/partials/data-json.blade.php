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
            <strong>Seluruh data di sesi ini akan digantikan</strong> oleh isi file, termasuk nomor RoPA dan kode DPIA.
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

<dialog id="dialog-contoh" class="dialog">
    <form method="POST" action="{{ route('data.impor-contoh') }}">
        @csrf
        <h3 style="margin-top:0">Impor Contoh Data</h3>
        <p>
            Memuat contoh RoPA &amp; DPIA lengkap (layanan PPID Pemprov Bali) untuk melihat cara pengisian setiap tab.
            <strong>Seluruh data di sesi ini akan digantikan</strong> oleh contoh tersebut.
        </p>
        <p class="hint">Ekspor dulu data Anda bila masih diperlukan. Contoh dapat diubah dan disesuaikan dengan layanan Anda.</p>
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit" class="btn-oranye">Muat contoh</button>
        </div>
    </form>
</dialog>
