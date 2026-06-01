<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Kategori Tiket') }}
            </h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal'))" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Tambah Kategori
                </button>
            </div>
    </x-slot>

    <div class="py-12" x-data="categoryManager()" @open-modal.window="openAddModal()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <div>
                        <h3 class="text-sm font-medium text-green-800">Berhasil</h3>
                        <p class="text-sm text-green-600 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
                @endif
                
                @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <div>
                        <h3 class="text-sm font-medium text-red-800">Terdapat Kesalahan</h3>
                        <ul class="text-sm text-red-600 mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Kategori</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($categories as $category)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5 font-mono">{{ $category->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-600 line-clamp-2">{{ $category->description ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($category->is_active)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-50 text-rose-700 ring-1 ring-rose-600/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editItem({{ $category->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mx-2 transition">Edit</button>
                                        <button type="button" @click="$dispatch('open-delete-modal', { name: 'category-delete', action: '{{ route('admin.categories.destroy', $category) }}', title: 'Hapus kategori?', message: 'Kategori hanya dapat dihapus jika tidak lagi dibutuhkan oleh tiket.' })" class="text-rose-600 hover:text-rose-900 transition">Delete</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada kategori</h3>
                                        <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat kategori tiket baru.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alpine Modal -->
            <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
                
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="showModal" x-transition.origin.bottom.duration.300ms class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <form :action="formAction" method="POST">
                                @csrf
                                <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">
                                
                                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                            <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                            <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title" x-text="editMode ? 'Edit Kategori' : 'Tambah Kategori Baru'"></h3>
                                            
                                            <div class="mt-6 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700">Nama Kategori <span class="text-rose-500">*</span></label>
                                                    <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                                                    <textarea name="description" x-model="form.description" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition"></textarea>
                                                </div>

                                                <div class="pt-2">
                                                    <label class="inline-flex items-center bg-gray-50 px-4 py-3 rounded-xl border border-gray-200 w-full cursor-pointer hover:bg-gray-100 transition">
                                                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-5 h-5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                                        <div class="ml-3">
                                                            <span class="block text-sm font-semibold text-gray-800">Status Aktif</span>
                                                            <span class="block text-xs text-gray-500">Kategori yang tidak aktif akan disembunyikan dari pilihan pelanggan.</span>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition">Simpan Kategori</button>
                                    <button type="button" @click="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-confirm-delete-modal name="category-delete" />

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('categoryManager', () => ({
                    showModal: false,
                    editMode: false,
                    formAction: '{{ route('admin.categories.store') }}',
                    form: {
                        name: '',
                        description: '',
                        is_active: true
                    },
                    openAddModal() {
                        this.editMode = false;
                        this.formAction = '{{ route('admin.categories.store') }}';
                        this.form = { name: '', description: '', is_active: true };
                        this.showModal = true;
                    },
                    editItem(item) {
                        this.editMode = true;
                        this.formAction = `/admin/categories/${item.id}`;
                        this.form = {
                            name: item.name,
                            description: item.description,
                            is_active: item.is_active == 1
                        };
                        this.showModal = true;
                    },
                    closeModal() {
                        this.showModal = false;
                    }
                }))
            })
        </script>
</x-app-layout>
