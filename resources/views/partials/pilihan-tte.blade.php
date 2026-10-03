@php $orgTte = $ropa['organisasi'] ?? []; @endphp
<fieldset class="field" style="margin-top:.5rem">
    <legend>Penandatangan yang menggunakan TTE</legend>
    <small class="hint">Centang bila penandatangan akan menggunakan Tanda Tangan Elektronik. Kosongkan untuk tanda
        tangan basah.</small>
    <div class="checks" style="margin-top:.4rem">
        <label class="check">
            <input type="checkbox" name="tte[]" value="pj">
            <span>Penanggung Jawab Layanan/Aktivitas
                @if (filled($ropa['aktivitas']['penanggung_jawab'] ?? null))
                    <small class="hint">({{ $ropa['aktivitas']['penanggung_jawab'] }})</small>
                @endif
            </span>
        </label>
        <label class="check">
            <input type="checkbox" name="tte[]" value="ppdp">
            <span>Pejabat Pelindung Data Pribadi
                @if (filled($orgTte['nama_ppdp'] ?? null))
                    <small class="hint">({{ $orgTte['nama_ppdp'] }})</small>
                @endif
            </span>
        </label>
        <label class="check">
            <input type="checkbox" name="tte[]" value="pengendali">
            <span>Pengendali Data Pribadi
                @if (filled($orgTte['nama_pengendali'] ?? null))
                    <small class="hint">({{ $orgTte['nama_pengendali'] }})</small>
                @endif
            </span>
        </label>
    </div>
</fieldset>
