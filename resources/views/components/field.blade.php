@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])

<div class="field">
    <label for="{{ $name }}">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>

    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" {{ $attributes }}>
    @endif

    @if ($hint) <small class="hint">{{ $hint }}</small> @endif
    @error($name) <small class="error">{{ $message }}</small> @enderror
</div>
