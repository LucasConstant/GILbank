<nav x-data="{ open: false }" class="app-nav">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="brand-lockup">
                        <span class="brand-mark">G</span>
                        <span>
                            <strong>GILbank</strong>
                            <small>painel operacional</small>
                        </span>
                    </a>
                </div>

                <div class="hidden gap-2 sm:-my-px sm:ms-10 sm:flex">
                    @if (Auth::user()->isGeneralManager())
                        <x-nav-link :href="route('general-manager.managers.index')" :active="request()->routeIs('general-manager.managers.*')">
                            Gerentes
                        </x-nav-link>
                        <x-nav-link :href="route('general-manager.limit-requests.index')" :active="request()->routeIs('general-manager.limit-requests.*')">
                            Limites
                        </x-nav-link>
                        <x-nav-link :href="route('general-manager.audits.index')" :active="request()->routeIs('general-manager.audits.*')">
                            Auditoria
                        </x-nav-link>
                    @elseif (Auth::user()->isAccountManager())
                        <x-nav-link :href="route('account-manager.accounts.index')" :active="request()->routeIs('account-manager.accounts.*')">
                            Contas
                        </x-nav-link>
                        <x-nav-link :href="route('account-manager.limit-requests.index')" :active="request()->routeIs('account-manager.limit-requests.*')">
                            Solicitações
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="role-chip">{{ Auth::user()->role->label() }}</div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="profile-trigger">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Sair
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

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

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()->isGeneralManager())
                <x-responsive-nav-link :href="route('general-manager.managers.index')" :active="request()->routeIs('general-manager.managers.*')">Gerentes</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('general-manager.limit-requests.index')" :active="request()->routeIs('general-manager.limit-requests.*')">Limites</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('general-manager.audits.index')" :active="request()->routeIs('general-manager.audits.*')">Auditoria</x-responsive-nav-link>
            @elseif (Auth::user()->isAccountManager())
                <x-responsive-nav-link :href="route('account-manager.accounts.index')" :active="request()->routeIs('account-manager.accounts.*')">Contas</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('account-manager.limit-requests.index')" :active="request()->routeIs('account-manager.limit-requests.*')">Solicitações</x-responsive-nav-link>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Perfil</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Sair</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
