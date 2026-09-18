<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Solicitações de Limite</h2>
            <div class="space-x-3 text-sm">
                <a href="{{ route('general-manager.limit-requests.index') }}" class="{{ $pendingOnly ? 'font-semibold text-gray-900' : 'text-gray-500' }}">Pendentes</a>
                <a href="{{ route('general-manager.limit-requests.index', ['status' => 'all']) }}" class="{{ ! $pendingOnly ? 'font-semibold text-gray-900' : 'text-gray-500' }}">Todas</a>
            </div>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gerente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Limite solicitado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($limitRequests as $limitRequest)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $limitRequest->account->customer->name }}
                                    <div class="text-xs text-gray-500">Conta #{{ $limitRequest->conta_id }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $limitRequest->manager->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">R$ {{ number_format($limitRequest->limite_solicitado, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $limitRequest->status->label() }}</td>
                                <td class="px-6 py-4 text-sm text-right space-x-2">
                                    @if ($limitRequest->isPending())
                                        <form action="{{ route('general-manager.limit-requests.approve', $limitRequest) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="text-green-700 hover:underline">Aprovar</button>
                                        </form>
                                        <form action="{{ route('general-manager.limit-requests.reject', $limitRequest) }}" method="POST" class="inline">
                                            @csrf
                                            <button class="text-red-600 hover:underline">Reprovar</button>
                                        </form>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Nenhuma solicitação encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $limitRequests->links() }}</div>
        </div>
    </div>
</x-app-layout>
