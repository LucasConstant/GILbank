<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gerente_id')->constrained('users')->restrictOnDelete();
            $table->decimal('saldo', 15, 2)->default(0);
            $table->decimal('limite', 15, 2)->default(0);
            $table->boolean('bloqueada')->default(false);
            $table->timestamps();

            $table->unique('user_id');
            $table->index('gerente_id');
            $table->index('bloqueada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas');
    }
};
