<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracoesAgendamentoSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Verificar se já existe configuração para cliente_cadastrado
        $existeClienteCadastrado = DB::table('configuracoes_agendamento')
            ->where('perfil', 'cliente_cadastrado')
            ->where('ativo', 1)
            ->exists();

        if (!$existeClienteCadastrado) {
            DB::table('configuracoes_agendamento')->insert([
                'perfil' => 'cliente_cadastrado',
                'intervalo_minutos' => 30,
                'antecedencia_minima_horas' => 2,
                'antecedencia_maxima_dias' => 60,
                'ativo' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info('✅ Configuração para perfil "cliente_cadastrado" criada');
        } else {
            $this->command->info('ℹ️  Configuração para perfil "cliente_cadastrado" já existe');
        }

        // Verificar e atualizar configuração pública se necessário
        $configPublica = DB::table('configuracoes_agendamento')
            ->where('perfil', 'publico')
            ->where('ativo', 1)
            ->first();

        if ($configPublica) {
            // Garantir configurações mínimas para perfil público
            DB::table('configuracoes_agendamento')
                ->where('id', $configPublica->id)
                ->update([
                    'intervalo_minutos' => $configPublica->intervalo_minutos ?? 30,
                    'antecedencia_minima_horas' => max($configPublica->antecedencia_minima_horas ?? 1, 1),
                    'antecedencia_maxima_dias' => $configPublica->antecedencia_maxima_dias ?? 60,
                    'updated_at' => now(),
                ]);

            $this->command->info('✅ Configuração pública verificada/atualizada');
        }

        // Verificar horários de funcionamento básicos
        $temHorariosFuncionamento = DB::table('horarios_funcionamento')
            ->where('ativo', 1)
            ->exists();

        if (!$temHorariosFuncionamento) {
            $this->command->warn('⚠️  ATENÇÃO: Nenhum horário de funcionamento ativo encontrado!');
            $this->command->warn('   Configure os horários antes de ativar o agendamento público.');
        } else {
            $this->command->info('✅ Horários de funcionamento configurados');
        }

        // Verificar se há serviços ativos
        $temServicosAtivos = DB::table('servicos')
            ->where('ativo', 1)
            ->exists();

        if (!$temServicosAtivos) {
            $this->command->warn('⚠️  ATENÇÃO: Nenhum serviço ativo encontrado!');
            $this->command->warn('   Configure pelo menos um serviço antes de ativar o agendamento público.');
        } else {
            $servicosCount = DB::table('servicos')->where('ativo', 1)->count();
            $this->command->info("✅ {$servicosCount} serviço(s) ativo(s) encontrado(s)");
        }

        $this->command->info('');
        $this->command->info('🎯 RESUMO DA CONFIGURAÇÃO:');
        
        // Mostrar configurações atuais
        $configs = DB::table('configuracoes_agendamento')
            ->where('ativo', 1)
            ->get();

        foreach ($configs as $config) {
            $this->command->info("   📋 Perfil: {$config->perfil}");
            $this->command->info("      ⏱️  Intervalo: {$config->intervalo_minutos} minutos");
            $this->command->info("      📅 Antecedência mín: {$config->antecedencia_minima_horas}h");
            $this->command->info("      📅 Antecedência máx: {$config->antecedencia_maxima_dias} dias");
            $this->command->info('');
        }
    }
}

/*
INSTRUÇÕES DE USO:

1. Executar o seeder:
   php artisan db:seed --class=ConfiguracoesAgendamentoSeeder

2. Ou incluir no DatabaseSeeder principal:
   $this->call(ConfiguracoesAgendamentoSeeder::class);

3. Verificar resultado:
   php artisan tinker
   DB::table('configuracoes_agendamento')->where('ativo', 1)->get()

VALIDAÇÕES REALIZADAS:
✅ Cria configuração para perfil "cliente_cadastrado" se não existir
✅ Verifica configuração do perfil "publico"
✅ Valida existência de horários de funcionamento
✅ Valida existência de serviços ativos
✅ Mostra resumo das configurações
*/