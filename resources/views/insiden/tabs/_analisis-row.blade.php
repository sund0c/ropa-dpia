{{-- Satu baris Rantai Serangan atau IoC. $grup = 'rantai' | 'ioc', $i = indeks (atau __i__), $row = isian. --}}
@php
    $IC = \App\Http\Controllers\InsidenController::class;

    $err = fn (string $f) => is_int($i) ? $errors->first("$grup.$i.$f") : null;
    $nm  = fn (string $f) => "{$grup}[{$i}][{$f}]";
@endphp
<div class="an" data-an>
    @if ($grup === 'rantai')
        <div class="an-grid">
            <div class="field">
                <label>Tahap (Taktik ATT&amp;CK) <span class="req">*</span></label>
                <select name="{{ $nm('taktik') }}">
                    <option value="">— Pilih —</option>
                    @foreach ($IC::TAKTIK as $k => $t)
                        <option value="{{ $k }}" @selected(($row['taktik'] ?? '') === $k)>{{ $t }}</option>
                    @endforeach
                </select>
                @if ($err('taktik')) <small class="error">{{ $err('taktik') }}</small> @endif
            </div>
            <div class="field">
                <label>Teknik ATT&amp;CK</label>
                <input type="text" name="{{ $nm('teknik') }}" value="{{ $row['teknik'] ?? '' }}" maxlength="255"
                       list="daftar-teknik" placeholder="Ketik atau pilih, mis. T1078 Valid Accounts">
                @if ($err('teknik')) <small class="error">{{ $err('teknik') }}</small> @endif
            </div>
            <button type="button" class="an-hapus" data-an-hapus title="Hapus baris" aria-label="Hapus baris">✕</button>
        </div>
        <div class="field">
            <label>Temuan <span class="req">*</span></label>
            <input type="text" name="{{ $nm('temuan') }}" value="{{ $row['temuan'] ?? '' }}" maxlength="1000"
                   placeholder="Bukti yang menunjukkan teknik ini dipakai">
            @if ($err('temuan')) <small class="error">{{ $err('temuan') }}</small> @endif
        </div>
    @else
        <div class="an-grid">
            <div class="field">
                <label>Jenis <span class="req">*</span></label>
                <select name="{{ $nm('jenis') }}">
                    <option value="">— Pilih —</option>
                    @foreach ($IC::JENIS_IOC as $k => $t)
                        <option value="{{ $k }}" @selected(($row['jenis'] ?? '') === $k)>{{ $t }}</option>
                    @endforeach
                </select>
                @if ($err('jenis')) <small class="error">{{ $err('jenis') }}</small> @endif
            </div>
            <div class="field">
                <label>Nilai <span class="req">*</span></label>
                <input type="text" name="{{ $nm('nilai') }}" value="{{ $row['nilai'] ?? '' }}" maxlength="500"
                       placeholder="Mis. 203.0.113.45" style="font-family:ui-monospace,monospace">
                @if ($err('nilai')) <small class="error">{{ $err('nilai') }}</small> @endif
            </div>
            <button type="button" class="an-hapus" data-an-hapus title="Hapus baris" aria-label="Hapus baris">✕</button>
        </div>
        <div class="field">
            <label>Keterangan</label>
            <input type="text" name="{{ $nm('keterangan') }}" value="{{ $row['keterangan'] ?? '' }}" maxlength="500"
                   placeholder="Peran indikator dalam serangan">
            @if ($err('keterangan')) <small class="error">{{ $err('keterangan') }}</small> @endif
        </div>
    @endif
</div>
