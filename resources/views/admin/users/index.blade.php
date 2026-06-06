<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">{{ __('Manage Users') }}</h2>
            <button onclick="window.dispatchEvent(new CustomEvent('open-user-modal'))" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                Tambah User
            </button>
        </div>
    </x-slot>

    <div class="py-12" x-data="userManager()" @open-user-modal.window="openAddModal()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100">
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="flex-1 rounded-xl border-gray-300">
                <select name="role_id" class="rounded-xl border-gray-300">
                    <option value="">Semua Role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(request('role_id') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-xl">Filter</button>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-xl text-center">Reset</a>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">User</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Role</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase">Tim</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        <div class="text-xs text-gray-400">{{ $user->phone ?: '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->role?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $user->team?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <button @click="editItem({{ $user->toJson() }})" class="text-indigo-600 hover:text-indigo-900 mx-2">Edit</button>
                                        @if (! Auth::user()->is($user))
                                            <button
                                                type="button"
                                                @click="$dispatch('open-delete-modal', {
                                                    name: 'user-delete',
                                                    action: '{{ route('admin.users.destroy', $user) }}',
                                                    title: 'Hapus user?',
                                                    message: 'User hanya dapat dihapus jika belum terhubung dengan tiket, tim, komentar, atau lampiran.'
                                                })"
                                                class="text-rose-600 hover:text-rose-900"
                                            >Delete</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">Belum ada user yang ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $users->links() }}</div>
            </div>
        </div>

        <div x-show="showModal" class="relative z-50" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50" @click="closeModal()"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <form :action="formAction" method="POST" class="w-full max-w-xl rounded-2xl bg-white shadow-xl">
                        @csrf
                        <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900" x-text="editMode ? 'Edit User' : 'Tambah User'"></h3>
                            <div class="mt-5 grid gap-4">
                                <input name="name" x-model="form.name" required placeholder="Nama" class="rounded-xl border-gray-300">
                                <input type="email" name="email" x-model="form.email" required placeholder="Email" class="rounded-xl border-gray-300">
                                <input name="phone" x-model="form.phone" placeholder="Nomor telepon (opsional)" class="rounded-xl border-gray-300">
                                <select name="role_id" x-model="form.role_id" @change="clearTeamWhenNotApplicable()" required class="rounded-xl border-gray-300">
                                    <option value="">Pilih Role</option>
                                    @foreach ($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach
                                </select>
                                <div x-show="teamIsApplicable()">
                                    <select name="team_id" x-model="form.team_id" class="w-full rounded-xl border-gray-300">
                                        <option value="">Tanpa Tim</option>
                                        @foreach ($teams as $team)<option value="{{ $team->id }}">{{ $team->name }}</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <input type="password" name="password" minlength="8" :required="!editMode" placeholder="Password" class="w-full rounded-xl border-gray-300">
                                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter. <span x-show="editMode">Kosongkan jika tidak ingin mengubah password.</span></p>
                                </div>
                                <input type="password" name="password_confirmation" minlength="8" :required="!editMode" placeholder="Konfirmasi password" class="rounded-xl border-gray-300">
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 rounded-b-2xl">
                            <button type="button" @click="closeModal()" class="px-4 py-2 rounded-xl bg-white border border-gray-300">Batal</button>
                            <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <x-confirm-delete-modal name="user-delete" />

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userManager', () => ({
                showModal: false,
                editMode: false,
                formAction: '{{ route('admin.users.store') }}',
                teamRoleIds: @js($roles->where('slug', 'agent')->pluck('id')->map(fn ($id) => (string) $id)->values()),
                form: { name: '', email: '', phone: '', role_id: '', team_id: '' },
                openAddModal() {
                    this.editMode = false;
                    this.formAction = '{{ route('admin.users.store') }}';
                    this.form = { name: '', email: '', phone: '', role_id: '', team_id: '' };
                    this.showModal = true;
                },
                editItem(item) {
                    this.editMode = true;
                    this.formAction = `/admin/users/${item.id}`;
                    this.form = { name: item.name, email: item.email, phone: item.phone ?? '', role_id: item.role_id, team_id: item.team_id ?? '' };
                    this.showModal = true;
                },
                teamIsApplicable() {
                    return this.teamRoleIds.includes(String(this.form.role_id));
                },
                clearTeamWhenNotApplicable() {
                    if (!this.teamIsApplicable()) this.form.team_id = '';
                },
                closeModal() { this.showModal = false; }
            }))
        })
    </script>
</x-app-layout>
