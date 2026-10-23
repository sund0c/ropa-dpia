{{-- Satu entri hasil di dalam satu butir uji. $no = nomor butir, $j = indeks entri (atau __j__), $row = isian. --}}
@php
    use App\Http\Controllers\PengujianController as PC;

    $nm  = fn (string $f) => "butir[{$no}][entri][{$j}][{$f}]";
    $val = fn (string $f) => (string) ($row[$f] ?? '');
    $err = fn (string $f) => is_int($j) ? $errors->first("butir.$no.entri.$j.$f") : null;
    $hasil = $val('hasil');
@endphp
<div class="en" data-en>
    <div class="en-head">
        <span class="en-no" data-en-no>Entri</span>
        <button type="button" class="en-hapus" data-en-hapus title="Hapus entri" aria-label="Hapus entri">✕</button>
    </div>

    <div class="field">
        <label>Hasil <span class="req">*</span></label>
        <select name="{{ $nm('hasil') }}" data-en-hasil>
            <option value="">— Pilih —</option>
            @foreach (PC::HASIL as $k => $t)
                <option value="{{ $k }}" @selected($hasil === $k)>{{ $t }}</option>
            @endforeach
        </select>
        @if ($err('hasil')) <small class="error">{{ $err('hasil') }}</small> @endif
    </div>

    {{-- Diisi bila diuji (Aman / Kerentanan / Perlu tinjauan) --}}
    <div data-en-diuji @class(['bt-hide' => ! in_array($hasil, ['aman', 'rentan', 'tinjau'], true)])>
        <div class="row">
            <div class="field">
                <label>Endpoint / Objek yang Diuji <span class="req">*</span></label>
                <textarea name="{{ $nm('objek') }}" rows="2" maxlength="500"
                          placeholder="method + URL. Boleh lebih dari satu, satu per baris. Mis. GET /api/v1/pengajuan/{id}">{{ $val('objek') }}</textarea>
                @if ($err('objek')) <small class="error">{{ $err('objek') }}</small> @endif
            </div>
            <div class="field">
                <label>Peran yang Diuji</label>
                <input type="text" name="{{ $nm('peran') }}" value="{{ $val('peran') }}" maxlength="255"
                       placeholder="mis. Pemohon A vs Pemohon B">
                @if ($err('peran')) <small class="error">{{ $err('peran') }}</small> @endif
            </div>
        </div>
        <div class="field">
            <label>Catatan Bukti <span class="req">*</span></label>
            <textarea name="{{ $nm('bukti') }}" rows="2" maxlength="3000"
                      placeholder="Request, respons, dan respons patokan pembanding. Sanitasi token & data pribadi.">{{ $val('bukti') }}</textarea>
            @if ($err('bukti')) <small class="error">{{ $err('bukti') }}</small> @endif
        </div>
    </div>

    {{-- Diisi bila Kerentanan --}}
    <div class="row" data-en-rentan @class(['bt-hide' => $hasil !== 'rentan'])>
        <div class="field">
            <label>Tingkat Risiko <span class="req">*</span></label>
            <select name="{{ $nm('risiko') }}">
                <option value="">— Pilih —</option>
                @foreach (PC::RISIKO as $k => $t)
                    <option value="{{ $k }}" @selected($val('risiko') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('risiko')) <small class="error">{{ $err('risiko') }}</small> @endif
        </div>
        <div></div>
    </div>

    {{-- Rekomendasi: Kerentanan (wajib) atau Perlu tinjauan (opsional) --}}
    <div class="field" data-en-rekom @class(['bt-hide' => ! in_array($hasil, ['rentan', 'tinjau'], true)])>
        <label>Rekomendasi Perbaikan <span class="req" data-en-rekom-wajib @class(['bt-hide' => $hasil !== 'rentan'])>*</span></label>
        <textarea name="{{ $nm('rekomendasi') }}" rows="2" maxlength="2000"
                  placeholder="Perbaikan yang disarankan.">{{ $val('rekomendasi') }}</textarea>
        @if ($err('rekomendasi')) <small class="error">{{ $err('rekomendasi') }}</small> @endif
    </div>

    {{-- Diisi bila Tidak diuji / Tidak berlaku --}}
    <div class="field" data-en-alasan @class(['bt-hide' => ! in_array($hasil, ['tidak_diuji', 'tidak_berlaku'], true)])>
        <label>Alasan <span class="req">*</span></label>
        <input type="text" name="{{ $nm('alasan') }}" value="{{ $val('alasan') }}" maxlength="500"
               placeholder="Mis. fitur tidak ada pada aplikasi ini / di luar cakupan / tidak sempat diuji">
        @if ($err('alasan')) <small class="error">{{ $err('alasan') }}</small> @endif
    </div>
</div>
