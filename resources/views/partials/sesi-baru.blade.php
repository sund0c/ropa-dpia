<button type="button" class="secondary" data-open-dialog="dialog-sesi-baru">Mulai sesi baru</button>

<dialog id="dialog-sesi-baru" class="dialog">
    <h3 style="margin-top:0">Mulai Sesi Baru?</h3>
    <p>
        Anda akan membuat sesi baru dan <strong>semua data yang sebelumnya Anda inputkan akan hilang</strong>,
        termasuk seluruh isian RoPA dan DPIA.
    </p>
    <p class="hint">Pastikan PDF yang diperlukan sudah diunduh sebelum melanjutkan.</p>

    <form method="POST" action="{{ route('session.reset') }}">
        @csrf
        <div class="actions">
            <button type="button" class="secondary" data-close-dialog>Batal</button>
            <button type="submit" class="btn-merah">Ya, Mulai Sesi Baru</button>
        </div>
    </form>
</dialog>
