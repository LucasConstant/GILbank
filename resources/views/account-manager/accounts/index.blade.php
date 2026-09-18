<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Contas de Clientes</h2>
            <a href="{{ route('account-manager.accounts.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Nova conta
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Conta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limite</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($accounts as $account)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">#{{ $account->id }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="text-gray-900">{{ $account->customer->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $account->customer->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">R$ {{ number_format($account->saldo, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">R$ {{ number_format($account->limite, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($account->isBlocked())
                                        <span class="text-red-600 font-medium">Bloqueada</span>
                                    @else
                                        <span class="text-green-700 font-medium">Ativa</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    <a href="{{ route('account-manager.accounts.show', $account) }}" class="text-indigo-600 hover:underline">Detalhes</a>
                                    <a href="{{ route('account-manager.accounts.edit', $account) }}" class="text-indigo-600 hover:underline">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">Nenhuma conta cadastrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $accounts->links() }}</div>
        </div>
    </div>
</x-app-layout>
