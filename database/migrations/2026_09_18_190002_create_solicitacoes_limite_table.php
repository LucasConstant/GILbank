<?php

use App\Enums\LimitRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_limite', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_id')->constrained('contas')->cascadeOnDelete();
            $table->foreignId('gerente_id')->constrained('users')->restrictOnDelete();
            $table->decimal('limite_solicitado', 15, 2);
            $table->string('status', 20)->default(LimitRequestStatus::Pending->value);
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['conta_id', 'status']);
            $table->index('gerente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_limite');
    }
};
