<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar Conta #{{ $account->id }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('account-manager.accounts.update', $account) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Nome do cliente" />
                        <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $account->customer->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="E-mail" />
                        <x-text-input id="email" type="email" name="email" class="block mt-1 w-full" :value="old('email', $account->customer->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Nova senha (opcional)" />
                        <x-text-input id="password" type="password" name="password" class="block mt-1 w-full" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="saldo" value="Saldo" />
                            <x-text-input id="saldo" type="number" step="0.01" min="0" name="saldo" class="block mt-1 w-full" :value="old('saldo', $account->saldo)" required />
                            <x-input-error :messages="$errors->get('saldo')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="limite" value="Limite" />
                            <x-text-input id="limite" type="number" step="0.01" min="0" name="limite" class="block mt-1 w-full" :value="old('limite', $account->limite)" required />
                            <x-input-error :messages="$errors->get('limite')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Atualizar</x-primary-button>
                        <a href="{{ route('account-manager.accounts.show', $account) }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
