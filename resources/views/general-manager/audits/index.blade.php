<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Logs de Auditoria</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <x-input-label for="manager_id" value="Gerente responsável" />
                        <select id="manager_id" name="manager_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                            <option value="">Todos</option>
                            @foreach ($managers as $manager)
                                <option value="{{ $manager->id }}" @selected((string) ($filters['manager_id'] ?? '') === (string) $manager->id)>
                                    {{ $manager->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="event" value="Evento" />
                        <select id="event" name="event" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                            <option value="">Todos</option>
                            @foreach (['created' => 'Criação', 'updated' => 'Atualização', 'deleted' => 'Exclusão'] as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['event'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-primary-button>Filtrar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quando</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modelo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($audits as $audit)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $audit->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $audit->user?->name ?? 'Sistema' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $audit->event }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ class_basename($audit->auditable_type) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $audit->auditable_id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum log encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $audits->links() }}</div>
        </div>
    </div>
</x-app-layout>
