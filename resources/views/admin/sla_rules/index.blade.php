<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ __('Aturan SLA (Service Level Agreement)') }}
            </h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal'))" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-md hover:shadow-lg flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                    </svg>
                    Tambah SLA Rule
                </button>
            </div>
    </x-slot>

    <div class="py-12" x-data="slaManager()" @open-modal.window="openAddModal()">
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
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tingkat Prioritas</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target Respon Awal</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target Penyelesaian</th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($slaRules as $rule)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white shadow-sm" style="background-color: {{ $rule->priority->color ?? '#999' }}">
                                            {{ $rule->priority->name ?? 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            {{ $rule->response_time_hours }} Jam
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2 text-sm text-gray-700 font-medium">
                                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ $rule->resolution_time_hours }} Jam
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editItem({{ $rule->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mx-2 transition">Edit</button>
                                        <form action="{{ route('admin.sla-rules.destroy', $rule) }}" method="POST" class="inline" onsubmit="return confirm('Anda yakin ingin menghapus aturan SLA ini?')">
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada Aturan SLA</h3>
                                        <p class="mt-1 text-sm text-gray-500">Tetapkan target waktu penyelesaian untuk tiap tingkat prioritas.</p>
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
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                            <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title" x-text="editMode ? 'Edit Aturan SLA' : 'Tambah Aturan SLA Baru'"></h3>
                                            
                                            <div class="mt-6 space-y-4">
                                                <div x-show="!editMode">
                                                    <label class="block text-sm font-semibold text-gray-700">Pilih Prioritas <span class="text-rose-500">*</span></label>
                                                    <select name="priority_id" x-model="form.priority_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition" :required="!editMode">
                                                        <option value="">-- Pilih --</option>
                                                        @foreach($priorities as $p)
                                                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-1 text-xs text-gray-500">Hanya prioritas yang belum memiliki aturan SLA yang tampil.</p>
                                                </div>
                                                
                                                <div x-show="editMode">
                                                    <label class="block text-sm font-semibold text-gray-700">Prioritas Terkait</label>
                                                    <div class="mt-1 px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-gray-500 text-sm" x-text="form.priority_name"></div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700">Target Respon Awal <span class="text-rose-500">*</span></label>
                                                        <div class="mt-1 relative rounded-xl shadow-sm">
                                                            <input type="number" name="response_time_hours" x-model="form.response_time_hours" required min="1" class="block w-full rounded-xl border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition">
                                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                                <span class="text-gray-500 sm:text-sm font-medium">Jam</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="block text-sm font-semibold text-gray-700">Target Penyelesaian <span class="text-rose-500">*</span></label>
                                                        <div class="mt-1 relative rounded-xl shadow-sm">
                                                            <input type="number" name="resolution_time_hours" x-model="form.resolution_time_hours" required min="1" class="block w-full rounded-xl border-gray-300 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition">
                                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                                                <span class="text-gray-500 sm:text-sm font-medium">Jam</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto transition">Simpan SLA</button>
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
                Alpine.data('slaManager', () => ({
                    showModal: false,
                    editMode: false,
                    formAction: '{{ route('admin.sla-rules.store') }}',
                    form: {
                        priority_id: '',
                        priority_name: '',
                        response_time_hours: 24,
                        resolution_time_hours: 48
                    },
                    openAddModal() {
                        this.editMode = false;
                        this.formAction = '{{ route('admin.sla-rules.store') }}';
                        this.form = { priority_id: '', priority_name: '', response_time_hours: 24, resolution_time_hours: 48 };
                        this.showModal = true;
                    },
                    editItem(item) {
                        this.editMode = true;
                        this.formAction = `/admin/sla-rules/${item.id}`;
                        this.form = {
                            priority_id: item.priority_id,
                            priority_name: item.priority ? item.priority.name : '',
                            response_time_hours: item.response_time_hours,
                            resolution_time_hours: item.resolution_time_hours
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
