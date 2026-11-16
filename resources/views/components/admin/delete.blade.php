@props(['action', 'message', 'label' => 'Hapus'])

<form method="POST" action="{{ $action }}" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button class="admin-button admin-danger" type="submit">{{ $label }}</button>
</form>

