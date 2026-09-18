<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Extrato — Conta #{{ $account->id }} ({{ $account->customer->name }})
            </h2>
            <a href="{{ route('account-manager.accounts.show', $account) }}" class="text-sm text-gray-600 hover:underline">Voltar</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-700">
                Período: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d/m/Y') }}
                até {{ \Illuminate\Support\Carbon::parse($endDate)->format('d/m/Y') }}
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $movement->tipo->label() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $movement->descricao }}</td>
                                <td class="px-6 py-4 text-sm text-right font-medium {{ $movement->isInbound() ? 'text-green-700' : 'text-red-600' }}">
                                    {{ $movement->isInbound() ? '+' : '-' }} R$ {{ number_format($movement->valor, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Nenhuma movimentação no período.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
