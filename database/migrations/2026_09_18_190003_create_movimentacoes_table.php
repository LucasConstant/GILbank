<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->string('tipo', 32);
            $table->decimal('valor', 15, 2);
            $table->string('direcao', 16);
            $table->foreignId('conta_destino_id')->nullable()->constrained('contas')->nullOnDelete();
            $table->string('aplicacao_tipo', 16)->nullable();
            $table->string('descricao')->nullable();
            $table->timestamps();

            $table->index(['conta_id', 'created_at']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
    }
};
