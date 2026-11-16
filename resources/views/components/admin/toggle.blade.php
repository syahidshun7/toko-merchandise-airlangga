@props(['name', 'label', 'checked' => false])

<input type="hidden" name="{{ $name }}" value="0">
<label class="flex min-h-11 items-center gap-3 text-sm font-semibold">
    <input type="checkbox" name="{{ $name }}" value="1" class="size-4 accent-school-blue"
        @checked(old($name, $checked) == 1)>
    {{ $label }}
</label>

