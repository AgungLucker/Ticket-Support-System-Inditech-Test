@props([
    'name' => 'delete-confirmation',
    'title' => 'Konfirmasi Hapus',
    'message' => 'Data yang dihapus tidak dapat dikembalikan.',
    'confirmLabel' => 'Hapus',
])

<div
    x-data="{ open: false, action: '', title: @js($title), message: @js($message) }"
    @open-delete-modal.window="
        if ($event.detail.name === @js($name)) {
            action = $event.detail.action;
            title = $event.detail.title || @js($title);
            message = $event.detail.message || @js($message);
            open = true;
        }
    "
    x-show="open"
    class="relative z-50"
    style="display: none;"
>
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="open = false"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900" x-text="title"></h3>
                    <p class="mt-3 text-sm text-gray-600" x-text="message"></p>
                </div>
                <form :action="action" method="POST" class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="open = false" class="px-4 py-2 rounded-xl bg-white border border-gray-300 text-gray-800">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white">{{ $confirmLabel }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
