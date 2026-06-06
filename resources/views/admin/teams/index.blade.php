<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">{{ __('Manajemen Tim') }}</h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-modal'))" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-md flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Tim
            </button>
        </div>
    </x-slot>

    <div class="py-12" x-data="teamManager(@js($supervisors))" @open-modal.window="openAddModal()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100">
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="GET" action="{{ route('admin.teams.index') }}" class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama tim..." class="flex-1 rounded-xl border-gray-300">
                <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl">Cari</button>
                <a href="{{ route('admin.teams.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl text-center">Reset</a>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama Tim</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supervisor</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jumlah Agent</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($teams as $team)
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $team->name }}</div>
                                        @if($team->description)
                                            <div class="text-xs text-gray-400 mt-0.5">{{ $team->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $team->supervisor?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($team->agents_count > 0)
                                            <button type="button"
                                                @click="openMembersModal($el.dataset.teamName, JSON.parse($el.dataset.agents))"
                                                data-team-name="{{ $team->name }}"
                                                data-agents='@json($team->agents->map(fn($a) => ["name" => $a->name, "email" => $a->email]))'
                                                class="text-indigo-600 hover:text-indigo-900 hover:underline font-medium transition">
                                                {{ $team->agents_count }} agent
                                            </button>
                                        @else
                                            <span class="text-gray-400">0 agent</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="editItem({{ $team->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mx-2 transition">Edit</button>
                                        <button type="button"
                                            @click="$dispatch('open-delete-modal', {
                                                name: 'team-delete',
                                                action: '{{ route('admin.teams.destroy', $team) }}',
                                                title: 'Hapus tim?',
                                                message: 'Tim hanya dapat dihapus jika tidak memiliki agent.'
                                            })"
                                            class="text-rose-600 hover:text-rose-900 transition">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada tim</h3>
                                        <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat tim support baru.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $teams->links() }}</div>
            </div>
        </div>

        {{-- Modal Tambah/Edit --}}
        <div x-show="showModal" class="relative z-50" style="display: none;">
            <div x-show="showModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showModal" x-transition.origin.bottom.duration.300ms class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form :action="formAction" method="POST">
                            @csrf
                            <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">
                            <div class="bg-white px-6 pt-6 pb-4">
                                <h3 class="text-lg font-bold text-gray-900" x-text="editMode ? 'Edit Tim' : 'Tambah Tim Baru'"></h3>
                                <div class="mt-5 space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700">Nama Tim <span class="text-rose-500">*</span></label>
                                        <input type="text" name="name" x-model="form.name" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                                        <textarea name="description" x-model="form.description" rows="2" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700">Supervisor</label>
                                        <select name="supervisor_id" x-model="form.supervisor_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Tanpa Supervisor</option>
                                            <template x-for="s in availableSupervisors()" :key="s.id">
                                                <option :value="s.id" x-text="s.name + ' (' + s.email + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl">
                                <button type="button" @click="closeModal()" class="px-4 py-2.5 rounded-xl bg-white border border-gray-300 text-sm font-semibold text-gray-700">Batal</button>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Members modal --}}
        <div x-show="membersModal.show" class="relative z-50" style="display: none;">
            <div x-show="membersModal.show" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="membersModal.show = false"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div x-show="membersModal.show" x-transition.origin.bottom.duration.300ms class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl">
                        <div class="px-6 pt-6 pb-2 flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900" x-text="membersModal.teamName"></h3>
                                <p class="text-sm text-gray-500 mt-0.5">Daftar agent dalam tim ini</p>
                            </div>
                            <button @click="membersModal.show = false" class="text-gray-400 hover:text-gray-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <ul class="px-6 pb-6 mt-3 space-y-2 max-h-72 overflow-y-auto">
                            <template x-for="agent in membersModal.agents" :key="agent.email">
                                <li class="flex items-center gap-3 p-3 rounded-xl bg-gray-50">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700"
                                        x-text="agent.name.charAt(0).toUpperCase()"></span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="agent.name"></p>
                                        <p class="text-xs text-gray-500 truncate" x-text="agent.email"></p>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-delete-modal name="team-delete" />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('teamManager', (supervisors) => ({
                showModal: false,
                editMode: false,
                formAction: '{{ route('admin.teams.store') }}',
                allSupervisors: supervisors,
                membersModal: { show: false, teamName: '', agents: [] },
                form: { name: '', description: '', supervisor_id: '' },
                // Show supervisors without a team, plus the one currently assigned to this team
                availableSupervisors() {
                    const currentId = String(this.form.supervisor_id);
                    return this.allSupervisors.filter(s =>
                        s.team_id === null || String(s.id) === currentId
                    );
                },
                openAddModal() {
                    this.editMode = false;
                    this.formAction = '{{ route('admin.teams.store') }}';
                    this.form = { name: '', description: '', supervisor_id: '' };
                    this.showModal = true;
                },
                editItem(item) {
                    this.editMode = true;
                    this.formAction = `/admin/teams/${item.id}`;
                    this.form = {
                        name: item.name,
                        description: item.description ?? '',
                        supervisor_id: item.supervisor_id ?? ''
                    };
                    this.showModal = true;
                },
                openMembersModal(teamName, agents) {
                    this.membersModal = { show: true, teamName, agents };
                },
                closeModal() { this.showModal = false; }
            }))
        })
    </script>
</x-app-layout>
