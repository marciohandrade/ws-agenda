<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Servico;
use Carbon\Carbon;

class AgendamentoSeeder extends Seeder
{
    public function run()
    {
        // ✅ NÃO LIMPAR - MANTER AGENDAMENTOS EXISTENTES
        // Agendamento::truncate(); // Comentado por causa de FK
        
        // ✅ VERIFICAR SE EXISTEM CLIENTES E SERVIÇOS
        $clientes = Cliente::all();
        $servicos = Servico::all();
        
        if ($clientes->isEmpty()) {
            $this->createTestClientes();
            $clientes = Cliente::all();
        }
        
        if ($servicos->isEmpty()) {
            $this->createTestServicos();
            $servicos = Servico::all();
        }
        
        // ✅ CRIAR AGENDA COMPLETA ATÉ O FINAL DO MÊS
        $this->createAgendaCompleta($clientes, $servicos);
        
        $this->command->info('✅ Agenda completa criada até o final do mês!');
    }
    
    private function createAgendaCompleta($clientes, $servicos)
    {
        $hoje = Carbon::today();
        $fimDoMes = Carbon::today()->endOfMonth();
        $proximoMes = Carbon::today()->addMonth()->endOfMonth();
        
        // ✅ 1. HOJE - Vários agendamentos
        $this->createAgendamentosParaDia($hoje, $clientes, $servicos, [
            ['09:00', 'confirmado', 'Primeiro atendimento do dia'],
            ['09:30', 'confirmado', 'Cliente fidelizado'],
            ['10:30', 'pendente', 'Aguardando confirmação'],
            ['11:00', 'confirmado', 'Consulta de rotina'],
            ['14:00', 'confirmado', 'Atendimento pós-almoço'],
            ['15:30', 'pendente', 'Primeiro agendamento'],
            ['16:00', 'cancelado', 'Cliente cancelou - problemas pessoais'],
            ['17:00', 'confirmado', 'Último atendimento do dia'],
        ]);
        
        // ✅ 2. AMANHÃ - Bastante movimento
        $amanha = $hoje->copy()->addDay();
        $this->createAgendamentosParaDia($amanha, $clientes, $servicos, [
            ['08:00', 'confirmado', 'Primeiro horário disponível'],
            ['08:30', 'confirmado', 'Cliente madrugador'],
            ['09:30', 'pendente', 'Aguardando retorno'],
            ['10:00', 'confirmado', 'Consulta de acompanhamento'],
            ['11:30', 'confirmado', 'Cliente preferencial'],
            ['14:30', 'pendente', 'Precisa confirmar horário'],
            ['15:00', 'confirmado', 'Atendimento especial'],
            ['16:30', 'confirmado', 'Consulta de retorno'],
            ['17:30', 'pendente', 'Último horário da tarde'],
        ]);
        
        // ✅ 3. RESTO DA SEMANA ATUAL
        $inicioSemana = $hoje->copy()->startOfWeek();
        $fimSemana = $hoje->copy()->endOfWeek();
        
        for ($data = $inicioSemana->copy(); $data <= $fimSemana; $data->addDay()) {
            if ($data->lt($hoje)) continue; // Pular dias passados
            if ($data->eq($hoje) || $data->eq($amanha)) continue; // Já criados
            
            // Quantidade aleatória por dia (3-7 agendamentos)
            $quantidade = rand(3, 7);
            $horarios = $this->getHorariosAleatorios($quantidade);
            
            foreach ($horarios as $horario) {
                Agendamento::create([
                    'cliente_id' => $clientes->random()->id,
                    'servico_id' => $servicos->random()->id,
                    'data_agendamento' => $data->format('Y-m-d'),
                    'horario_agendamento' => $horario,
                    'status' => $this->getRandomStatus(),
                    'observacoes' => rand(0, 1) ? $this->getRandomObservacao() : null
                ]);
            }
        }
        
        // ✅ 4. RESTO DO MÊS ATUAL
        $proximaSemana = $fimSemana->copy()->addDay();
        
        for ($data = $proximaSemana->copy(); $data <= $fimDoMes; $data->addDay()) {
            // Pular domingos (assumindo que não trabalha)
            if ($data->dayOfWeek === 0) continue;
            
            // Quantidade por dia (2-8 agendamentos)
            $quantidade = rand(2, 8);
            $horarios = $this->getHorariosAleatorios($quantidade);
            
            foreach ($horarios as $horario) {
                Agendamento::create([
                    'cliente_id' => $clientes->random()->id,
                    'servico_id' => $servicos->random()->id,
                    'data_agendamento' => $data->format('Y-m-d'),
                    'horario_agendamento' => $horario,
                    'status' => $this->getRandomStatus(),
                    'observacoes' => rand(0, 2) ? $this->getRandomObservacao() : null
                ]);
            }
        }
        
        // ✅ 5. PRÓXIMO MÊS (para testar filtro "todos")
        $inicioProximoMes = Carbon::today()->addMonth()->startOfMonth();
        
        for ($data = $inicioProximoMes->copy(); $data <= $proximoMes && $data < $inicioProximoMes->copy()->addDays(15); $data->addDay()) {
            if ($data->dayOfWeek === 0) continue; // Pular domingos
            
            $quantidade = rand(1, 5);
            $horarios = $this->getHorariosAleatorios($quantidade);
            
            foreach ($horarios as $horario) {
                Agendamento::create([
                    'cliente_id' => $clientes->random()->id,
                    'servico_id' => $servicos->random()->id,
                    'data_agendamento' => $data->format('Y-m-d'),
                    'horario_agendamento' => $horario,
                    'status' => $this->getRandomStatus(),
                    'observacoes' => rand(0, 3) ? $this->getRandomObservacao() : null
                ]);
            }
        }
        
        // ✅ 6. ALGUNS AGENDAMENTOS DO MÊS PASSADO (histórico)
        $mesPassado = Carbon::today()->subMonth();
        for ($i = 0; $i < 15; $i++) {
            $data = $mesPassado->copy()->addDays(rand(1, $mesPassado->daysInMonth));
            
            if ($data->dayOfWeek === 0) continue;
            
            Agendamento::create([
                'cliente_id' => $clientes->random()->id,
                'servico_id' => $servicos->random()->id,
                'data_agendamento' => $data->format('Y-m-d'),
                'horario_agendamento' => $this->getRandomTime(),
                'status' => 'concluido', // Agendamentos passados são concluídos
                'observacoes' => 'Atendimento realizado - ' . $this->getRandomObservacao()
            ]);
        }
    }
    
    private function createAgendamentosParaDia($data, $clientes, $servicos, $agendamentos)
    {
        foreach ($agendamentos as $agendamento) {
            Agendamento::create([
                'cliente_id' => $clientes->random()->id,
                'servico_id' => $servicos->random()->id,
                'data_agendamento' => $data->format('Y-m-d'),
                'horario_agendamento' => $agendamento[0] . ':00',
                'status' => $agendamento[1],
                'observacoes' => $agendamento[2]
            ]);
        }
    }
    
    private function getHorariosAleatorios($quantidade)
    {
        $todosHorarios = [
            '08:00:00', '08:30:00', '09:00:00', '09:30:00', '10:00:00', '10:30:00', 
            '11:00:00', '11:30:00', '14:00:00', '14:30:00', '15:00:00', '15:30:00', 
            '16:00:00', '16:30:00', '17:00:00', '17:30:00'
        ];
        
        shuffle($todosHorarios);
        return array_slice($todosHorarios, 0, min($quantidade, count($todosHorarios)));
    }
    
    private function createTestClientes()
    {
        $clientes = [
            ['nome' => 'Maria Silva Santos', 'telefone' => '(11) 99999-1111', 'email' => 'maria.silva@teste.com'],
            ['nome' => 'João Pedro Costa', 'telefone' => '(11) 99999-2222', 'email' => 'joao.costa@teste.com'],
            ['nome' => 'Ana Carolina Lima', 'telefone' => '(11) 99999-3333', 'email' => 'ana.lima@teste.com'],
            ['nome' => 'Pedro Henrique Souza', 'telefone' => '(11) 99999-4444', 'email' => 'pedro.souza@teste.com'],
            ['nome' => 'Carla Fernanda Oliveira', 'telefone' => '(11) 99999-5555', 'email' => 'carla.oliveira@teste.com'],
            ['nome' => 'Roberto Carlos Mendes', 'telefone' => '(11) 99999-6666', 'email' => 'roberto.mendes@teste.com'],
            ['nome' => 'Fernanda Silva Rocha', 'telefone' => '(11) 99999-7777', 'email' => 'fernanda.rocha@teste.com'],
            ['nome' => 'Carlos Eduardo Santos', 'telefone' => '(11) 99999-8888', 'email' => 'carlos.santos@teste.com'],
            ['nome' => 'Juliana Pereira Lima', 'telefone' => '(11) 99999-9999', 'email' => 'juliana.lima@teste.com'],
            ['nome' => 'Ricardo Alves Costa', 'telefone' => '(11) 99999-0000', 'email' => 'ricardo.costa@teste.com'],
        ];
        
        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }
        
        $this->command->info('✅ 10 clientes de teste criados!');
    }
    
    private function createTestServicos()
    {
        $servicos = [
            ['nome' => 'Consulta Clínica Geral', 'preco' => 150.00, 'duracao' => 30],
            ['nome' => 'Consulta Cardiológica', 'preco' => 200.00, 'duracao' => 45],
            ['nome' => 'Consulta Dermatológica', 'preco' => 180.00, 'duracao' => 30],
            ['nome' => 'Exame Laboratorial', 'preco' => 80.00, 'duracao' => 15],
            ['nome' => 'Ultrassom Abdominal', 'preco' => 120.00, 'duracao' => 30],
            ['nome' => 'Eletrocardiograma', 'preco' => 60.00, 'duracao' => 15],
            ['nome' => 'Consulta Oftalmológica', 'preco' => 160.00, 'duracao' => 30],
            ['nome' => 'Fisioterapia', 'preco' => 100.00, 'duracao' => 60],
        ];
        
        foreach ($servicos as $servico) {
            Servico::create($servico);
        }
        
        $this->command->info('✅ 8 serviços de teste criados!');
    }
    
    private function getRandomTime()
    {
        $horas = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', 
                  '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30'];
        return $horas[array_rand($horas)] . ':00';
    }
    
    private function getRandomStatus()
    {
        $status = ['pendente', 'confirmado', 'concluido', 'cancelado'];
        $weights = [35, 45, 15, 5]; // Mais confirmados e pendentes
        
        $rand = rand(1, 100);
        if ($rand <= 35) return 'pendente';
        if ($rand <= 80) return 'confirmado';
        if ($rand <= 95) return 'concluido';
        return 'cancelado';
    }
    
    private function getRandomObservacao()
    {
        $observacoes = [
            'Cliente fidelizado - atendimento preferencial',
            'Primeira consulta - paciente novo',
            'Retorno agendado conforme protocolo',
            'Aguardando resultado de exames',
            'Paciente com horário flexível',
            'Necessita confirmação via WhatsApp',
            'Material especial necessário para procedimento',
            'Consulta de acompanhamento mensal',
            'Procedimento de rotina',
            'Paciente idoso - necessita acompanhante',
            'Consulta urgente - encaixe',
            'Reagendamento a pedido do paciente',
            'Primeira vez na clínica',
            'Paciente com plano de saúde',
            'Atendimento particular',
            'Necessita jejum para exame',
            'Consulta de emergência',
            'Paciente com mobilidade reduzida',
            'Retorno em 15 dias',
            'Aguardando autorização do convênio'
        ];
        
        return $observacoes[array_rand($observacoes)];
    }
}