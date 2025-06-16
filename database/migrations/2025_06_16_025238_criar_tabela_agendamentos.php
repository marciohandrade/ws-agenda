<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agendamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('servico_id')->constrained('servicos')->onDelete('cascade');
            $table->date('data_agendamento');
            $table->time('horario_agendamento');
            $table->enum('status', ['pendente', 'confirmado', 'concluido', 'cancelado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->boolean('cliente_cadastrado_automaticamente')->default(false);
            $table->boolean('ativo')->default(true); // ✅ Campo ativo adicionado
            $table->timestamp('data_cancelamento')->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->timestamps();

            // Índices para melhor performance
            $table->index(['data_agendamento', 'horario_agendamento']);
            $table->index('status');
            $table->index('cliente_id');
            $table->index('ativo'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendamentos');
    }
};
