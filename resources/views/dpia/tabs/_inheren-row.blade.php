@php
    $nk   = (int) ($row['kemungkinan'] ?? 0);
    $nd   = (int) ($row['dampak'] ?? 0);
    $skor = ($nk && $nd) ? $nk * $nd : null;
    $lv   = $skor ? $MR::level($skor, $m) : null;
@endphp
<tr data-inh-row>
    <td></td>
    <td>
        <textarea name="inheren[{{ $s }}][{{ $i }}][risiko]" rows="3">{{ $row['risiko'] ?? '' }}</textarea>
        @error("inheren.$s.$i.risiko") <small class="error">{{ $message }}</small> @enderror
    </td>
    <td>
        <select name="inheren[{{ $s }}][{{ $i }}][kemungkinan]" data-inh-k>
            <option value="">— Pilih —</option>
            @foreach (range(1, 5) as $v)
                <option value="{{ $v }}" @selected($nk === $v)>{{ $v }} – {{ $m['kemungkinan'][$v]['nama'] }}</option>
            @endforeach
        </select>
        @error("inheren.$s.$i.kemungkinan") <small class="error">{{ $message }}</small> @enderror
    </td>
    <td>
        <select name="inheren[{{ $s }}][{{ $i }}][dampak]" data-inh-d>
            <option value="">— Pilih —</option>
            @foreach (range(1, 5) as $v)
                <option value="{{ $v }}" @selected($nd === $v)>{{ $v }} – {{ $m['dampak'][$v]['nama'] }}</option>
            @endforeach
        </select>
        @error("inheren.$s.$i.dampak") <small class="error">{{ $message }}</small> @enderror
    </td>
    <td class="skor" data-inh-skor
        @if ($lv) style="background:{{ $kat[$lv]['warna'] }}; color:{{ $teks($lv) }}" @endif>{{ $skor ?? '–' }}</td>
    <td><button type="button" class="icon" data-inh-remove aria-label="Hapus risiko">✕</button></td>
</tr>
