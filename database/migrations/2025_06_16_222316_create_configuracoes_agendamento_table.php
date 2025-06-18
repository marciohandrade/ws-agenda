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
        Schema::create('configuracoes_agendamento', function (Blueprint $table) {
            $table->id();
            
            // Horários de funcionamento
            $table->time('horario_inicio')->default('08:00');
            $table->time('horario_fim')->default('18:00');
            $table->integer('intervalo_minutos')->default(30);
            
            // Horário de almoço
            $table->boolean('tem_horario_almoco')->default(true);
            $table->time('almoco_inicio')->default('12:00');
            $table->time('almoco_fim')->default('13:00');
            
            // Dias da semana (1=segunda, 7=domingo) - SEM DEFAULT
            $table->json('dias_funcionamento');
            
            // Configurações por perfil
            $table->string('perfil')->default('publico'); // publico, cliente_cadastrado, admin
            $table->boolean('ativo')->default(true);
            
            // Antecedência mínima/máxima
            $table->integer('antecedencia_minima_horas')->default(2); // 2 horas
            $table->integer('antecedencia_maxima_dias')->default(60); // 60 dias
            
            $table->timestamps();
            
            // Índices
            $table->index('perfil');
            $table->index('ativo');
        });
        
        // Inserir dados padrão após criar a tabela
        DB::table('configuracoes_agendamento')->insert([
            [
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'intervalo_minutos' => 30,
                'tem_horario_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
                'dias_funcionamento' => json_encode(['1', '2', '3', '4', '5']),
                'perfil' => 'publico',
                'ativo' => true,
                'antecedencia_minima_horas' => 2,
                'antecedencia_maxima_dias' => 60,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'horario_inicio' => '08:00',
                'horario_fim' => '19:00',
                'intervalo_minutos' => 30,
                'tem_horario_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
                'dias_funcionamento' => json_encode(['1', '2', '3', '4', '5', '6']),
                'perfil' => 'cliente_cadastrado',
                'ativo' => true,
                'antecedencia_minima_horas' => 1,
                'antecedencia_maxima_dias' => 60,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'horario_inicio' => '07:00',
                'horario_fim' => '20:00',
                'intervalo_minutos' => 15,
                'tem_horario_almoco' => false,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
                'dias_funcionamento' => json_encode(['1', '2', '3', '4', '5', '6', '7']),
                'perfil' => 'admin',
                'ativo' => true,
                'antecedencia_minima_horas' => 0,
                'antecedencia_maxima_dias' => 365,
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
        Schema::dropIfExists('configuracoes_agendamento');
    }
};