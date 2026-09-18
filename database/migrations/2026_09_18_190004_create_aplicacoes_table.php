<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplicacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->string('tipo', 16);
            $table->decimal('saldo_aplicado', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['conta_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplicacoes');
    }
};
