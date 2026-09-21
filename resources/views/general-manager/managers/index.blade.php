<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-teal-700">Gestão de pessoas</p>
                <h2 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Gerentes de conta</h2>
                <p class="mt-2 max-w-xl text-sm text-slate-500">Administre os responsáveis pela operação diária das contas de clientes.</p>
            </div>
            <a href="{{ route('general-manager.managers.create') }}" class="action-primary">
                <span class="text-lg leading-none">+</span> Novo gerente
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="notice-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="notice-error">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="metric-grid">
                <div class="metric-card"><span>Gerentes cadastrados</span><strong>{{ $managers->total() }}</strong><small>equipe ativa no sistema</small></div>
                <div class="metric-card metric-card-accent"><span>Exibindo agora</span><strong>{{ $managers->count() }}</strong><small>registros nesta página</small></div>
            </div>

            <div class="data-card">
                <div class="data-card-header">
                    <div><h3>Equipe de gestão</h3><p>Dados de acesso e responsáveis cadastrados.</p></div>
                    <span class="table-count">{{ $managers->total() }} registros</span>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="table-heading">Responsável</th>
                            <th class="table-heading">Contato</th>
                            <th class="table-heading text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($managers as $manager)
                            <tr class="table-row">
                                <td class="table-cell"><div class="person-cell"><span class="avatar">{{ strtoupper(substr($manager->name, 0, 1)) }}</span><strong>{{ $manager->name }}</strong></div></td>
                                <td class="table-cell"><span class="text-slate-600">{{ $manager->email }}</span></td>
                                <td class="table-cell text-right space-x-3">
                                    <a href="{{ route('general-manager.managers.edit', $manager) }}" class="action-link">Editar</a>
                                    <form action="{{ route('general-manager.managers.destroy', $manager) }}" method="POST" class="inline" onsubmit="return confirm('Remover este gerente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-link action-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="empty-state">Nenhum gerente cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div>

            <div>{{ $managers->links() }}</div>
        </div>
    </div>
</x-app-layout>
