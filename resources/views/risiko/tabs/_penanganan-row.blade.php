{{-- Rencana penanganan untuk satu risiko. $i = indeks, $r = risiko (Tab III + nilai Tab IV), $row = isian. --}}
@php
    $RC = \App\Http\Controllers\RisikoController::class;

    $err = fn (string $f) => $errors->first("set.$i.$f");
    $pil = fn (string $f) => (string) ($row[$f] ?? '');
    $ir  = (int) $r['nilai']['dampak'] * (int) $r['nilai']['kemungkinan'];
    $lv  = \App\Support\PetaRisiko::level($ir, $peta);
@endphp
<div class="pn" data-pn>
    <div class="pn-head">
        <span class="pn-kode">{{ $r['kode'] }}</span>
        <span class="pn-meta">
            Prioritas {{ $r['nilai']['prioritas'] }}
            <span class="pn-badge" style="background:{{ $warna[$lv] }}; color:{{ in_array($lv, $putih, true) ? '#fff' : '#111' }}">
                IR {{ $ir }} · {{ $namaLevel[$lv] }}
            </span>
        </span>
    </div>

    <input type="hidden" name="set[{{ $i }}][risiko_id]" value="{{ $r['id'] }}">

    <div class="pn-ringkas">
        <div><span>Ancaman</span><b>{{ $r['ancaman'] }}</b></div>
        <div><span>Kerentanan</span><b>{{ $r['kerentanan'] }}</b></div>
    </div>
    @if ($err('risiko_id')) <small class="error">{{ $err('risiko_id') }}</small> @endif

    <div class="pn-grid2">
        <div class="field">
            <label>Opsi Penanganan <span class="req">*</span></label>
            <select name="set[{{ $i }}][opsi]">
                <option value="">— Pilih —</option>
                @foreach ($RC::OPSI as $k => $t)
                    <option value="{{ $k }}" @selected($pil('opsi') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('opsi')) <small class="error">{{ $err('opsi') }}</small> @endif
        </div>
        <div class="field">
            <label>Target Implementasi <span class="req">*</span></label>
            <input type="date" name="set[{{ $i }}][target]" value="{{ $row['target'] ?? '' }}">
            @if ($err('target')) <small class="error">{{ $err('target') }}</small> @endif
        </div>
    </div>

    <div class="pn-grid2">
        <div class="field">
            <label>Rencana Aksi Penanganan Risiko <span class="req">*</span></label>
            <textarea name="set[{{ $i }}][rencana_aksi]" rows="5" maxlength="3000"
                      placeholder="Satu aksi per baris. Contoh:&#10;1. Menerapkan enkripsi data sensitif&#10;2. Menyusun SOP hak akses">{{ $row['rencana_aksi'] ?? '' }}</textarea>
            @if ($err('rencana_aksi')) <small class="error">{{ $err('rencana_aksi') }}</small> @endif
        </div>
        <div class="field">
            <label>Keluaran <span class="req">*</span></label>
            <textarea name="set[{{ $i }}][keluaran]" rows="5" maxlength="3000"
                      placeholder="Hasil/bukti dari setiap aksi. Contoh:&#10;1. Laporan konfigurasi enkripsi&#10;2. SOP hak akses yang ditetapkan">{{ $row['keluaran'] ?? '' }}</textarea>
            @if ($err('keluaran')) <small class="error">{{ $err('keluaran') }}</small> @endif
        </div>
    </div>

    <div class="field">
        <label>Terdapat Risiko Residual? <span class="req">*</span></label>
        <div class="pn-radio">
            @foreach (['ya' => 'Ya', 'tidak' => 'Tidak'] as $k => $t)
                <label class="check"><input type="radio" name="set[{{ $i }}][residual]" value="{{ $k }}" data-pn-residual @checked($pil('residual') === $k)> <span>{{ $t }}</span></label>
            @endforeach
        </div>
        @if ($err('residual')) <small class="error">{{ $err('residual') }}</small> @endif
    </div>

    <div class="pn-residual" data-pn-blok-residual @if ($pil('residual') !== 'ya') hidden @endif>
        <div class="pn-grid4">
            <div class="field">
                <label>Dampak Residual <span class="req">*</span></label>
                <select name="set[{{ $i }}][res_dampak]" data-pn-d>
                    <option value="">— Pilih —</option>
                    @foreach ($levelDampak as $k => $t)
                        <option value="{{ $k }}" @selected($pil('res_dampak') === (string) $k)>{{ $t }}</option>
                    @endforeach
                </select>
                @if ($err('res_dampak')) <small class="error">{{ $err('res_dampak') }}</small> @endif
            </div>
            <div class="field">
                <label>Kemungkinan Residual <span class="req">*</span></label>
                <select name="set[{{ $i }}][res_kemungkinan]" data-pn-k>
                    <option value="">— Pilih —</option>
                    @foreach ($levelKemungkinan as $k => $t)
                        <option value="{{ $k }}" @selected($pil('res_kemungkinan') === (string) $k)>{{ $t }}</option>
                    @endforeach
                </select>
                @if ($err('res_kemungkinan')) <small class="error">{{ $err('res_kemungkinan') }}</small> @endif
            </div>
            <div class="field">
                <label>RR</label>
                <span class="nr-badge" data-pn-rr>–</span>
            </div>
            <div class="field">
                <label>Level Residual</label>
                <span class="nr-badge" data-pn-level>–</span>
            </div>
        </div>

        <div class="field" style="max-width:320px">
            <label>Status Risiko Residual <span class="req">*</span></label>
            <select name="set[{{ $i }}][res_status]" data-pn-status>
                <option value="">— Pilih —</option>
                @foreach ($RC::STATUS_RESIDUAL as $k => $t)
                    <option value="{{ $k }}" @selected($pil('res_status') === $k)>{{ $t }}</option>
                @endforeach
            </select>
            @if ($err('res_status')) <small class="error">{{ $err('res_status') }}</small> @endif
        </div>

        <div class="field" data-pn-blok-kontrol @if ($pil('res_status') !== 'mitigasi') hidden @endif>
            <label>Rencana Kontrol Tambahan <span class="req">*</span></label>
            <textarea name="set[{{ $i }}][kontrol_tambahan]" rows="4" maxlength="3000"
                      placeholder="Kontrol tambahan untuk menurunkan risiko residual, satu per baris.">{{ $row['kontrol_tambahan'] ?? '' }}</textarea>
            @if ($err('kontrol_tambahan')) <small class="error">{{ $err('kontrol_tambahan') }}</small> @endif
        </div>
    </div>
</div>
