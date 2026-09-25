@props(['label', 'value' => null, 'multiline' => false, 'hint' => null])

{{-- Tanpa atribut name: tidak ikut terkirim, nilai selalu dibaca dari RoPA di server --}}
<div class="field">
    <label>{{ $label }}</label>
    @if ($multiline)
        <textarea rows="3" class="ro" readonly disabled>{{ $value }}</textarea>
    @else
        <input type="text" class="ro" value="{{ $value }}" readonly disabled>
    @endif
    @if ($hint) <small class="hint">{{ $hint }}</small> @endif
</div>
