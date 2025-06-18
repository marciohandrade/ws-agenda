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
        // PASSO 1: Migrar dados existentes antes de remover colunas
        $this->migrarHorariosParaHorarioFuncionamento();

        // PASSO 2: Remover campos redundantes de configuracoes_agendamento (nome correto)
        Schema::table('configuracoes_agendamento', function (Blueprint $table) {
            // Verificar se as colunas existem antes de tentar remover
            if (Schema::hasColumn('configuracoes_agendamento', 'horario_inicio')) {
                $table->dropColumn('horario_inicio');
            }
            if (Schema::hasColumn('configuracoes_agendamento', 'horario_fim')) {
                $table->dropColumn('horario_fim');
            }
            if (Schema::hasColumn('configuracoes_agendamento', 'almoco_inicio')) {
                $table->dropColumn('almoco_inicio');
            }
            if (Schema::hasColumn('configuracoes_agendamento', 'almoco_fim')) {
                $table->dropColumn('almoco_fim');
            }
            if (Schema::hasColumn('configuracoes_agendamento', 'tem_horario_almoco')) {
                $table->dropColumn('tem_horario_almoco');
            }
            if (Schema::hasColumn('configuracoes_agendamento', 'dias_funcionamento')) {
                $table->dropColumn('dias_funcionamento');
            }
        });

        // PASSO 3: Garantir que horarios_funcionamento tem todos os campos necessários
        if (Schema::hasTable('horarios_funcionamento')) {
            Schema::table('horarios_funcionamento', function (Blueprint $table) {
                if (!Schema::hasColumn('horarios_funcionamento', 'ativo')) {
                    $table->boolean('ativo')->default(true);
                }
            });
        } else {
            // Criar tabela se não existir
            Schema::create('horarios_funcionamento', function (Blueprint $table) {
                $table->id();
                $table->foreignId('configuracao_agendamento_id')->constrained('configuracoes_agendamento')->onDelete('cascade');
                $table->tinyInteger('dia_semana'); // 1=segunda, 2=terça, ..., 7=domingo
                $table->time('horario_inicio');
                $table->time('horario_fim');
                $table->boolean('tem_almoco')->default(false);
                $table->time('almoco_inicio')->nullable();
                $table->time('almoco_fim')->nullable();
                $table->boolean('ativo')->default(true);
                $table->timestamps();

                // Índices
                $table->index(['configuracao_agendamento_id', 'dia_semana']);
                $table->unique(['configuracao_agendamento_id', 'dia_semana']);
            });
        }
    }

    /**
     * Migra dados existentes da configuracoes_agendamento para horarios_funcionamento
     */
    private function migrarHorariosParaHorarioFuncionamento(): void
    {
        // Usar o nome correto da tabela
        $configuracoes = DB::table('configuracoes_agendamento')->get();

        foreach ($configuracoes as $config) {
            // Verificar se já existem horários específicos
            $horariosExistentes = DB::table('horarios_funcionamento')
                ->where('configuracao_agendamento_id', $config->id)
                ->count();

            // Se não existem horários específicos, criar baseado na configuração geral
            if ($horariosExistentes === 0) {
                // Verificar se os campos existem antes de tentar acessar
                $diasFuncionamento = [];
                if (isset($config->dias_funcionamento)) {
                    $diasFuncionamento = json_decode($config->dias_funcionamento ?? '[]', true);
                }
                
                // Se não tem dias definidos, usar padrão segunda-sexta
                if (empty($diasFuncionamento)) {
                    $diasFuncionamento = [1, 2, 3, 4, 5]; // Seg-Sex
                }
                
                for ($dia = 1; $dia <= 7; $dia++) {
                    DB::table('horarios_funcionamento')->insert([
                        'configuracao_agendamento_id' => $config->id,
                        'dia_semana' => $dia,
                        'horario_inicio' => $config->horario_inicio ?? '08:00',
                        'horario_fim' => $config->horario_fim ?? '18:00',
                        'tem_almoco' => $config->tem_horario_almoco ?? false,
                        'almoco_inicio' => $config->almoco_inicio ?? '12:00',
                        'almoco_fim' => $config->almoco_fim ?? '13:00',
                        'ativo' => in_array($dia, $diasFuncionamento), // Verificar se dia está ativo
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar campos removidos (caso necessário fazer rollback)
        Schema::table('configuracoes_agendamento', function (Blueprint $table) {
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            $table->time('almoco_inicio')->nullable();
            $table->time('almoco_fim')->nullable();
            $table->boolean('tem_horario_almoco')->default(false);
            $table->json('dias_funcionamento')->nullable();
        });
    }
};