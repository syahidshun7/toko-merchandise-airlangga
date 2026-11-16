<div class="grid items-end gap-3 rounded-lg border border-slate-200 p-3 sm:grid-cols-3" data-variant-row>
    @if (isset($row['id']) && is_scalar($row['id']))
        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $row['id'] }}" data-variant-field="id">
    @endif
    <label class="admin-label">
        Nama varian biasa
        <input class="admin-input font-normal" type="text" name="variants[{{ $index }}][name]" value="{{ is_scalar($row['name'] ?? null) ? $row['name'] : '' }}" maxlength="100" data-variant-field="name">
    </label>
    <label class="admin-label">
        Ukuran
        <input class="admin-input font-normal" type="text" name="variants[{{ $index }}][size]" value="{{ is_scalar($row['size'] ?? null) ? $row['size'] : '' }}" maxlength="24" data-variant-field="size">
    </label>
    <label class="admin-label">
        Warna
        <input class="admin-input font-normal" type="text" name="variants[{{ $index }}][color]" value="{{ is_scalar($row['color'] ?? null) ? $row['color'] : '' }}" maxlength="48" data-variant-field="color">
    </label>
    <label class="admin-label">
        Harga varian (Rp)
        <input class="admin-input font-normal" type="number" name="variants[{{ $index }}][price]" value="{{ is_scalar($row['price'] ?? null) ? $row['price'] : '' }}" min="0" max="999999999999" data-variant-field="price">
    </label>
    <label class="admin-label">
        Stok
        <input class="admin-input font-normal" type="number" name="variants[{{ $index }}][stock]" value="{{ is_scalar($row['stock'] ?? null) ? $row['stock'] : '' }}" min="0" max="2147483647" data-variant-field="stock" required>
    </label>
    <button class="admin-button admin-danger" type="button" data-remove-variant>Hapus varian</button>
</div>

