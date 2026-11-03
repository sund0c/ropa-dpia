@props(['name', 'label', 'items' => [], 'hint' => null, 'required' => false, 'placeholder' => '', 'addLabel' => '+ Tambah'])

@php
    $rows = old($name, $items);
    if (empty($rows)) $rows = [''];
@endphp

<div class="field repeater" data-repeater>
    <label>{{ $label }} @if ($required)<span class="req">*</span>@endif</label>
    @if ($hint) <small class="hint">{{ $hint }}</small> @endif

    <div class="rep-rows" data-rows>
        @foreach ($rows as $i => $value)
            <div class="rep-row" data-row>
                <div class="rep-line">
                    <span class="rep-no"></span>
                    <input type="text" name="{{ $name }}[]" value="{{ $value }}" placeholder="{{ $placeholder }}">
<span class="rep-aksi">
                        <button type="button" class="urut" data-move="up" title="Naikkan" aria-label="Naikkan">▲</button>
                        <button type="button" class="urut" data-move="down" title="Turunkan" aria-label="Turunkan">▼</button>
                        <button type="button" class="icon" data-remove aria-label="Hapus baris">✕</button>
                    </span>
                </div>
                @error("$name.$i") <small class="error">{{ $message }}</small> @enderror
            </div>
        @endforeach
    </div>

    <button type="button" class="secondary small" data-add>{{ $addLabel }}</button>
    @error($name) <small class="error">{{ $message }}</small> @enderror
</div>
