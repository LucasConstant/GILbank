<x-mail::message>
# Bem-vindo(a) ao GILbank

Olá, **{{ $user->name }}**.

{{ $context }}

**E-mail (login):** {{ $user->email }}  
**Senha temporária:** {{ $plainPassword }}

Recomendamos alterar a senha no primeiro acesso.

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
