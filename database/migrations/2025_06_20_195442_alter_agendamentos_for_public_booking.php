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
        Schema::table('agendamentos', function (Blueprint $table) {
            // Permitir cliente_id NULL para agendamentos públicos
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
            
            // Campos para dados temporários de clientes não cadastrados
            $table->string('cliente_nome')->nullable()->after('cliente_id');
            $table->string('cliente_email')->nullable()->after('cliente_nome');
            $table->string('cliente_telefone', 20)->nullable()->after('cliente_email');
            
            // Índices para otimização de consultas
            $table->index(['data_agendamento', 'horario_agendamento'], 'idx_agendamentos_data_horario');
            $table->index(['status', 'ativo'], 'idx_agendamentos_status_ativo');
            $table->index('cliente_email', 'idx_agendamentos_cliente_email');
            $table->index(['origem', 'cliente_cadastrado_automaticamente'], 'idx_agendamentos_origem_cadastro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            // Reverter cliente_id para NOT NULL (cuidado com dados existentes)
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
            
            // Remover campos adicionados
            $table->dropColumn(['cliente_nome', 'cliente_email', 'cliente_telefone']);
            
            // Remover índices
            $table->dropIndex('idx_agendamentos_data_horario');
            $table->dropIndex('idx_agendamentos_status_ativo');
            $table->dropIndex('idx_agendamentos_cliente_email');
            $table->dropIndex('idx_agendamentos_origem_cadastro');
        });
    }
};