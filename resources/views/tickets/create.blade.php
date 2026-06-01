<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Tiket Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="block font-medium text-sm text-gray-700">Judul Tiket <span class="text-red-500">*</span></label>
                            <input id="title" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="title" value="{{ old('title') }}" required autofocus />
                            @error('title')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="block font-medium text-sm text-gray-700">Kategori <span class="text-red-500">*</span></label>
                            <select id="category_id" name="category_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Priority -->
                        <div class="mb-4">
                            <label for="priority_id" class="block font-medium text-sm text-gray-700">Prioritas Masalah <span class="text-red-500">*</span></label>
                            <select id="priority_id" name="priority_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Seberapa Mendesak? --</option>
                                @foreach($priorities as $priority)
                                    <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                                @endforeach
                            </select>
                            @error('priority_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Pilih tingkat urgensi dengan bijak. Prioritas tinggi memiliki SLA yang lebih ketat.</p>
                        </div>

                        {{-- Dropdown requester khusus Admin --}}
                        @isset($customers)
                        <div class="mb-4">
                            <label for="created_by" class="block font-medium text-sm text-gray-700">Requester / Customer <span class="text-red-500">*</span></label>
                            <select id="created_by" name="created_by" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Customer --</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('created_by') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ $customer->email }})</option>
                                @endforeach
                            </select>
                            @error('created_by')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Tiket ini akan tercatat atas nama customer yang dipilih.</p>
                        </div>
                        @endisset

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block font-medium text-sm text-gray-700">Deskripsi Detail <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="5" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Jelaskan masalah Anda selengkap mungkin agar tim agen kami bisa lebih cepat membantu.</p>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-6">
                            <label for="attachments" class="block font-medium text-sm text-gray-700">Lampiran Bukti / Screenshot (Opsional)</label>
                            <input type="file" id="attachments" name="attachments[]" multiple class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            @error('attachments')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                            @error('attachments.*')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Maksimal 5 file. Ukuran maks 2MB per file. (Format: jpg, png, pdf, doc, xls)</p>
                        </div>

                        <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('tickets.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition">
                                Kirim Tiket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
