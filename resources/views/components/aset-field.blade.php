@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'rows' => 3,
    'options' => [],
    'placeholder' => '— Pilih —',
])
<div class="field">
    <label for="{{ $name }}">{{ $label }} @if ($required)
            <span class="req">*</span>
        @endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        @php $pilih = (string) old($name, $value); @endphp
        <select id="{{ $name }}" name="{{ $name }}" {{ $attributes }}>
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $key => $text)
                <option value="{{ $key }}" @selected($pilih === (string) $key)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            value="{{ old($name, $value) }}" {{ $attributes }}>
    @endif

    @if ($hint)
        <small class="hint">{{ $hint }}</small>
    @endif
    @error($name)
        <small class="error">{{ $message }}</small>
    @enderror
</div>
