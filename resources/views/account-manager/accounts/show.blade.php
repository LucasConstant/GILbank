<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conta #{{ $account->id }} — {{ $account->customer->name }}</h2>
            <a href="{{ route('account-manager.accounts.index') }}" class="text-sm text-gray-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 text-red-800 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3 lg:col-span-2">
                    <h3 class="font-semibold text-gray-800">Dados da conta</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Cliente</dt>
                            <dd class="text-gray-900">{{ $account->customer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">E-mail</dt>
                            <dd class="text-gray-900">{{ $account->customer->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Saldo</dt>
                            <dd class="text-gray-900">R$ {{ number_format($account->saldo, 2, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Limite</dt>
                            <dd class="text-gray-900">R$ {{ number_format($account->limite, 2, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="{{ $account->isBlocked() ? 'text-red-600' : 'text-green-700' }}">
                                {{ $account->isBlocked() ? 'Bloqueada' : 'Ativa' }}
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap gap-2 pt-4">
                        <a href="{{ route('account-manager.accounts.edit', $account) }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Editar</a>

                        @if ($account->isBlocked())
                            <form method="POST" action="{{ route('account-manager.accounts.unblock', $account) }}">
                                @csrf
                                <button class="inline-flex items-center px-3 py-2 bg-green-700 border border-transparent rounded-md text-xs font-semibold uppercase tracking-widest text-white">Desbloquear</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('account-manager.accounts.block', $account) }}" onsubmit="return confirm('Bloquear esta conta?')">
                                @csrf
                                <button class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md text-xs font-semibold uppercase tracking-widest text-white">Bloquear</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('account-manager.accounts.destroy', $account) }}" onsubmit="return confirm('Excluir conta e cliente?')">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center px-3 py-2 bg-white border border-red-300 rounded-md text-xs font-semibold uppercase tracking-widest text-red-700 hover:bg-red-50">Excluir</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Aplicações</h3>
                    <ul class="space-y-2 text-sm">
                        @forelse ($account->investments as $investment)
                            <li class="flex justify-between">
                                <span>{{ $investment->tipo->label() }}</span>
                                <span>R$ {{ number_format($investment->saldo_aplicado, 2, ',', '.') }}</span>
                            </li>
                        @empty
                            <li class="text-gray-500">Sem aplicações.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Extrato por período</h3>
                    <form method="GET" action="{{ route('account-manager.accounts.statement', $account) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                        <div>
                            <x-input-label for="start_date" value="Data inicial" />
                            <x-text-input id="start_date" type="date" name="start_date" class="block mt-1 w-full" :value="old('start_date', now()->subDays(30)->toDateString())" required />
                        </div>
                        <div>
                            <x-input-label for="end_date" value="Data final" />
                            <x-text-input id="end_date" type="date" name="end_date" class="block mt-1 w-full" :value="old('end_date', now()->toDateString())" required />
                        </div>
                        <div>
                            <x-primary-button>Ver extrato</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Solicitar aumento de limite</h3>
                    <form method="POST" action="{{ route('account-manager.limit-requests.store', $account) }}" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="limite_solicitado" value="Novo limite desejado" />
                            <x-text-input id="limite_solicitado" type="number" step="0.01" name="limite_solicitado" class="block mt-1 w-full" :value="old('limite_solicitado')" required />
                            <p class="mt-1 text-xs text-gray-500">Limite atual: R$ {{ number_format($account->limite, 2, ',', '.') }}</p>
                            <x-input-error :messages="$errors->get('limite_solicitado')" class="mt-2" />
                        </div>
                        <x-primary-button>Enviar solicitação</x-primary-button>
                    </form>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Solicitações de limite</h3>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Valor</th>
                            <th class="px-4 py-2 text-left">Status</th>
                            <th class="px-4 py-2 text-left">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($account->limitRequests as $limitRequest)
                            <tr>
                                <td class="px-4 py-2">R$ {{ number_format($limitRequest->limite_solicitado, 2, ',', '.') }}</td>
                                <td class="px-4 py-2">{{ $limitRequest->status->label() }}</td>
                                <td class="px-4 py-2">{{ $limitRequest->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-gray-500">Nenhuma solicitação.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
