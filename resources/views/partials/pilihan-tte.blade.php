@php
    $orgTte = $ropa['organisasi'] ?? [];
    $penandatangan = [
        'pj'         => ['Penanggung Jawab Layanan/Aktivitas', $ropa['aktivitas']['penanggung_jawab'] ?? null],
        'ppdp'       => ['Pejabat Pelindung Data Pribadi',     $orgTte['nama_ppdp'] ?? null],
        'pengendali' => ['Pengendali Data Pribadi',            $orgTte['nama_pengendali'] ?? null],
    ];
@endphp
<fieldset class="field" style="margin-top:.5rem">
    <legend>Penandatangan yang menggunakan TTE</legend>
    <small class="hint">
        Centang bila penandatangan menggunakan Tanda Tangan Elektronik, lalu unggah gambar label TTE-nya
        (PNG/JPG, maks. {{ \App\Support\LabelTte::MAKS_KB }} KB). Gambar hanya dipakai untuk cetakan ini dan tidak disimpan.
        Kosongkan untuk tanda tangan basah.
    </small>
    <div class="checks" style="margin-top:.4rem">
        @foreach ($penandatangan as $k => [$peran, $nama])
            <div data-tte-item>
                <label class="check">
                    <input type="checkbox" name="tte[]" value="{{ $k }}" data-tte-cek>
                    <span>{{ $peran }}
                        @if (filled($nama)) <small class="hint">({{ $nama }})</small> @endif
                    </span>
                </label>
                <div class="tte-unggah" style="margin:.3rem 0 .5rem 1.6rem" hidden>
                    <input type="file" name="tte_label[{{ $k }}]" accept="image/png,image/jpeg" disabled>
                </div>
            </div>
        @endforeach
    </div>
</fieldset>
@include('partials.tte-js')
