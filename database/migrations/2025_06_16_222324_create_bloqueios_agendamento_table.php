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
        Schema::create('bloqueios_agendamento', function (Blueprint $table) {
            $table->id();
            
            // Tipo de bloqueio
            $table->enum('tipo', ['data_completa', 'horario_especifico', 'periodo']);
            
            // Datas
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            
            // Horários (para bloqueios específicos)
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            
            // Informações do bloqueio
            $table->string('motivo')->nullable();
            $table->text('observacoes')->nullable();
            
            // Recorrência (para feriados anuais)
            $table->boolean('recorrente')->default(false);
            
            // Perfis afetados - SEM DEFAULT
            $table->json('perfis_afetados');
            
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index(['data_inicio', 'data_fim']);
            $table->index('tipo');
            $table->index('ativo');
        });
        
        // Inserir alguns bloqueios de exemplo após criar a tabela
        DB::table('bloqueios_agendamento')->insert([
            [
                'tipo' => 'data_completa',
                'data_inicio' => '2024-12-25',
                'data_fim' => null,
                'horario_inicio' => null,
                'horario_fim' => null,
                'motivo' => 'Natal',
                'observacoes' => 'Feriado Nacional',
                'recorrente' => true,
                'perfis_afetados' => json_encode(['publico', 'cliente_cadastrado']),
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tipo' => 'data_completa',
                'data_inicio' => '2025-01-01',
                'data_fim' => null,
                'horario_inicio' => null,
                'horario_fim' => null,
                'motivo' => 'Ano Novo',
                'observacoes' => 'Feriado Nacional',
                'recorrente' => true,
                'perfis_afetados' => json_encode(['publico', 'cliente_cadastrado']),
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bloqueios_agendamento');
    }
};