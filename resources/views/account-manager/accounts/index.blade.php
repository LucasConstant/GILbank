<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700">Carteira sob gestão</p><h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Contas de clientes</h2><p class="mt-2 text-sm text-slate-500">Acompanhe saldo, limite e situação operacional da sua carteira.</p></div>
            <a href="{{ route('account-manager.accounts.create') }}" class="action-primary"><span class="text-lg leading-none">+</span> Nova conta</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="notice-success">{{ session('status') }}</div>
            @endif

            <div class="metric-grid metric-grid-three">
                <div class="metric-card"><span>Contas na carteira</span><strong>{{ $accounts->total() }}</strong><small>clientes sob responsabilidade</small></div>
                <div class="metric-card metric-card-accent"><span>Exibindo agora</span><strong>{{ $accounts->count() }}</strong><small>registros nesta página</small></div>
                <div class="metric-card"><span>Rotina operacional</span><strong>Ativa</strong><small>dados sincronizados com o banco</small></div>
            </div>

            <div class="data-card">
                <div class="data-card-header"><div><h3>Carteira de clientes</h3><p>Consulte rapidamente o estado financeiro de cada conta.</p></div><span class="table-count">{{ $accounts->total() }} contas</span></div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="table-heading">Conta</th><th class="table-heading">Cliente</th><th class="table-heading">Saldo</th><th class="table-heading">Limite</th><th class="table-heading">Status</th><th class="table-heading text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($accounts as $account)
                            <tr class="table-row">
                                <td class="table-cell"><span class="account-tag">#{{ $account->id }}</span></td>
                                <td class="table-cell"><div class="person-cell"><span class="avatar">{{ strtoupper(substr($account->customer->name, 0, 1)) }}</span><span><strong>{{ $account->customer->name }}</strong><small>{{ $account->customer->email }}</small></span></div>
                                </td>
                                <td class="table-cell font-semibold text-slate-800">R$ {{ number_format($account->saldo, 2, ',', '.') }}</td><td class="table-cell text-slate-600">R$ {{ number_format($account->limite, 2, ',', '.') }}</td>
                                <td class="table-cell">
                                    @if ($account->isBlocked())
                                        <span class="status-pill status-blocked">Bloqueada</span>
                                    @else
                                        <span class="status-pill status-active">Ativa</span>
                                    @endif
                                </td>
                                <td class="table-cell text-right space-x-3">
                                    <a href="{{ route('account-manager.accounts.show', $account) }}" class="action-link">Detalhes</a><a href="{{ route('account-manager.accounts.edit', $account) }}" class="action-link">Editar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">Nenhuma conta cadastrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>

            <div>{{ $accounts->links() }}</div>
        </div>
    </div>
</x-app-layout>
