<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                        {{ Auth::user()->isCustomer() ? __('Tiket Saya') : __('Semua Tiket') }}
                    </x-nav-link>

                    @can('access-admin')
                    <!-- Master Data Dropdown -->
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                    <div>Master Data</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('admin.users.index')">
                                    {{ __('Users') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.categories.index')">
                                    {{ __('Categories') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.labels.index')">
                                    {{ __('Labels') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.priorities.index')">
                                    {{ __('Priorities') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.sla-rules.index')">
                                    {{ __('SLA Rules') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Right: Bell + User -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 gap-1">
                @php $unreadCount = Auth::user()->unreadNotifications->count(); @endphp

                {{-- Bell --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="relative flex items-center justify-center h-9 w-9 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-500 text-[10px] text-white font-bold leading-none">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    {{-- Dropdown panel --}}
                    <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                        style="display: none;">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">Notifikasi</span>
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                        {{ $unreadCount }} belum dibaca
                                    </span>
                                @endif
                            </div>
                            @if($unreadCount > 0)
                                <form method="POST" action="{{ route('notifications.readAll') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                                        Tandai semua dibaca
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- List --}}
                        @php $notifications = Auth::user()->notifications()->latest()->take(8)->get(); @endphp
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                            @forelse($notifications as $notif)
                                <a href="{{ route('notifications.read', $notif->id) }}"
                                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $notif->read_at ? '' : 'bg-indigo-50/40' }}">
                                    {{-- Dot indicator --}}
                                    <div class="mt-1.5 shrink-0">
                                        @if(!$notif->read_at)
                                            <span class="block h-2 w-2 rounded-full bg-indigo-500"></span>
                                        @else
                                            <span class="block h-2 w-2 rounded-full bg-transparent"></span>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-800 {{ $notif->read_at ? '' : 'font-medium' }} line-clamp-2">
                                            {{ $notif->data['message'] ?? '-' }}
                                        </p>
                                        @if(!empty($notif->data['ticket_number']))
                                            <p class="text-xs text-gray-400 mt-0.5">#{{ $notif->data['ticket_number'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <p class="text-sm text-gray-400">Belum ada notifikasi</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- User dropdown --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 rounded-full text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition">
                            <span class="flex items-center justify-center h-7 w-7 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-xs shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <span class="hidden lg:block">{{ Auth::user()->name }}</span>
                            <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-xs text-gray-400">Masuk sebagai</p>
                            <p class="text-sm font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('tickets.index')" :active="request()->routeIs('tickets.*')">
                {{ Auth::user()->isCustomer() ? __('Tiket Saya') : __('Semua Tiket') }}
            </x-responsive-nav-link>

            @can('access-admin')
            <div class="pt-2 pb-2">
                <div class="px-4 font-medium text-sm text-gray-500">{{ __('Master Data') }}</div>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Users') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                    {{ __('Categories') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.labels.index')" :active="request()->routeIs('admin.labels.*')">
                    {{ __('Labels') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.priorities.index')" :active="request()->routeIs('admin.priorities.*')">
                    {{ __('Priorities') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.sla-rules.index')" :active="request()->routeIs('admin.sla-rules.*')">
                    {{ __('SLA Rules') }}
                </x-responsive-nav-link>
            </div>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            {{-- Notifikasi mobile --}}
            @if(Auth::user()->unreadNotifications->count() > 0)
            <div class="px-4 pt-3 pb-1">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Notifikasi</span>
                    <form method="POST" action="{{ route('notifications.readAll') }}">
                        @csrf
                        <button type="submit" class="text-xs text-indigo-600 hover:underline">Tandai dibaca</button>
                    </form>
                </div>
                @foreach(Auth::user()->unreadNotifications->take(3) as $notif)
                    <a href="{{ route('notifications.read', $notif->id) }}" class="block py-1 text-sm text-gray-700 truncate">
                        • {{ $notif->data['message'] ?? '-' }}
                    </a>
                @endforeach
            </div>
            @endif

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
