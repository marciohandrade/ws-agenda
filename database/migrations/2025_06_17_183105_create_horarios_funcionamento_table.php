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
        Schema::create('horarios_funcionamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('configuracao_agendamento_id')->constrained('configuracoes_agendamento')->onDelete('cascade');
            
            // Dia da semana (1=segunda, 7=domingo)
            $table->integer('dia_semana'); // 1-7
            
            // Horários específicos para este dia
            $table->time('horario_inicio');
            $table->time('horario_fim');
            
            // Horário de almoço específico (opcional)
            $table->boolean('tem_almoco')->default(false);
            $table->time('almoco_inicio')->nullable();
            $table->time('almoco_fim')->nullable();
            
            // Se este dia está ativo
            $table->boolean('ativo')->default(true);
            
            $table->timestamps();
            
            // Índices com nomes personalizados mais curtos
            $table->index(['configuracao_agendamento_id', 'dia_semana'], 'idx_config_dia');
            $table->unique(['configuracao_agendamento_id', 'dia_semana'], 'unq_config_dia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_funcionamento');
    }
};