<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Label Tiket') }}
            </h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal'))" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    Tambah Label
                </button>
            </div>
    </x-slot>

    <div class="py-12" x-data="labelManager()" @open-modal.window="openAddModal()">
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
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tampilan Label</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Slug</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kode Warna</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($labels as $label)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm" style="background-color: {{ $label->color }}">
                                            {{ $label->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 font-mono">{{ $label->slug }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-md shadow-inner border border-gray-200" style="background-color: {{ $label->color }}"></div>
                                            <span class="text-sm font-mono text-gray-600">{{ $label->color }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editItem({{ $label->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mx-2 transition">Edit</button>
                                        <form action="{{ route('admin.labels.destroy', $label) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus label ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-900 transition">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada label</h3>
                                        <p class="mt-1 text-sm text-gray-500">Buat label untuk mempermudah klasifikasi tiket.</p>
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
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                            <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title" x-text="editMode ? 'Edit Label' : 'Tambah Label Baru'"></h3>
                                            
                                            <div class="mt-6 space-y-4">
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700">Nama Label <span class="text-rose-500">*</span></label>
                                                    <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition" placeholder="Contoh: Frontend, Urgent">
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700">Warna Background</label>
                                                    <div class="mt-1 flex items-center gap-3">
                                                        <input type="color" name="color" x-model="form.color" class="h-10 w-14 rounded border border-gray-300 cursor-pointer">
                                                        <span class="text-sm font-mono text-gray-500" x-text="form.color"></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-4 p-4 bg-gray-50 rounded-xl border border-gray-200 flex flex-col items-center justify-center">
                                                    <span class="text-xs text-gray-500 mb-2 font-medium">Pratinjau:</span>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm" :style="'background-color: ' + form.color" x-text="form.name || 'Nama Label'">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition">Simpan Label</button>
                                    <button type="button" @click="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition">Batal</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('labelManager', () => ({
                    showModal: false,
                    editMode: false,
                    formAction: '{{ route('admin.labels.store') }}',
                    form: {
                        name: '',
                        color: '#4f46e5'
                    },
                    openAddModal() {
                        this.editMode = false;
                        this.formAction = '{{ route('admin.labels.store') }}';
                        this.form = { name: '', color: '#4f46e5' };
                        this.showModal = true;
                    },
                    editItem(item) {
                        this.editMode = true;
                        this.formAction = `/admin/labels/${item.id}`;
                        this.form = {
                            name: item.name,
                            color: item.color
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
