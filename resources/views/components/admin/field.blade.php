@props(['name', 'label', 'type' => 'text', 'value' => null, 'hint' => null])

<div>
    <label for="{{ $name }}" class="admin-label">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" {{ $attributes->class('admin-input') }} @error($name) aria-invalid="true" @enderror>{{ is_scalar(old($name, $value)) ? old($name, $value) : '' }}</textarea>
    @elseif ($type === 'file' || $type === 'password')
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" {{ $attributes->class('admin-input') }} @error($name) aria-invalid="true" @enderror>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ is_scalar(old($name, $value)) ? old($name, $value) : '' }}" {{ $attributes->class('admin-input') }} @error($name) aria-invalid="true" @enderror>
    @endif
    @if ($hint)
        <p class="mt-1 text-sm text-slate-600">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="mt-1 text-sm text-red-800">{{ $message }}</p>
    @enderror
</div>

