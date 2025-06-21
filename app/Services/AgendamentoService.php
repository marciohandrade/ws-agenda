<?php

namespace App\Services;

use App\Models\Agendamento;
use App\Models\HorarioFuncionamento;
use App\Models\BloqueioAgendamento;
use App\Models\Servico;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AgendamentoService
{
    /**
     * Verifica se uma data específica está disponível para agendamentos
     */
    public function verificarDiaDisponivel(string $data): array
    {
        $cacheKey = "dia_disponivel_{$data}";
        
        return Cache::remember($cacheKey, 300, function () use ($data) {
            try {
                $dataCarbon = Carbon::parse($data);
                $diaSemana = $dataCarbon->dayOfWeek;
                
                // 1. Verificar se estabelecimento funciona neste dia
                $horarioFuncionamento = HorarioFuncionamento::where('dia_semana', $diaSemana)
                    ->where('ativo', true)
                    ->first();
                
                if (!$horarioFuncionamento) {
                    return [
                        'disponivel' => false,
                        'motivo' => 'Estabelecimento fechado neste dia da semana'
                    ];
                }
                
                // 2. Verificar bloqueios específicos (feriados, etc)
                $bloqueado = BloqueioAgendamento::where('ativo', true)
                    ->where(function ($query) use ($dataCarbon) {
                        $query->where('tipo', 'data_completa')
                            ->where(function ($subQuery) use ($dataCarbon) {
                                // Bloqueio específico da data
                                $subQuery->where('data_inicio', '<=', $dataCarbon->format('Y-m-d'))
                                    ->where(function ($dateQuery) use ($dataCarbon) {
                                        $dateQuery->whereNull('data_fim')
                                            ->orWhere('data_fim', '>=', $dataCarbon->format('Y-m-d'));
                                    });
                            });
                    })
                    ->where(function ($query) {
                        // Verificar se afeta clientes públicos
                        $query->whereRaw('JSON_CONTAINS(perfis_afetados, ?)', ['"publico"']);
                    })
                    ->exists();
                
                if ($bloqueado) {
                    return [
                        'disponivel' => false,
                        'motivo' => 'Data bloqueada/feriado'
                    ];
                }
                
                // 3. Se chegou até aqui, está disponível
                return [
                    'disponivel' => true,
                    'motivo' => null,
                    'funcionamento' => [
                        'inicio' => substr($horarioFuncionamento->horario_inicio, 0, 5),
                        'fim' => substr($horarioFuncionamento->horario_fim, 0, 5),
                        'tem_almoco' => $horarioFuncionamento->tem_almoco,
                        'almoco_inicio' => $horarioFuncionamento->tem_almoco ? substr($horarioFuncionamento->almoco_inicio, 0, 5) : null,
                        'almoco_fim' => $horarioFuncionamento->tem_almoco ? substr($horarioFuncionamento->almoco_fim, 0, 5) : null
                    ]
                ];
                
            } catch (\Exception $e) {
                return [
                    'disponivel' => false,
                    'motivo' => 'Erro interno'
                ];
            }
        });
    }

    /**
     * Retorna todos os horários disponíveis para uma data específica
     */
    public function obterHorariosDisponiveis(string $data): array
    {
        $cacheKey = "horarios_disponiveis_{$data}";
        
        return Cache::remember($cacheKey, 180, function () use ($data) {
            try {
                $dataCarbon = Carbon::parse($data);
                $diaSemana = $dataCarbon->dayOfWeek;
                
                // Verificar se dia está disponível
                $diaDisponivel = $this->verificarDiaDisponivel($data);
                if (!$diaDisponivel['disponivel']) {
                    return [
                        'success' => false,
                        'message' => $diaDisponivel['motivo'],
                        'horarios' => []
                    ];
                }
                
                // Buscar horário de funcionamento
                $horarioFuncionamento = HorarioFuncionamento::where('dia_semana', $diaSemana)
                    ->where('ativo', true)
                    ->first();
                
                if (!$horarioFuncionamento) {
                    return [
                        'success' => false,
                        'message' => 'Horário de funcionamento não encontrado',
                        'horarios' => []
                    ];
                }
                
                // Gerar grade de horários
                $horarios = $this->gerarGradeHorarios($data, $horarioFuncionamento);
                
                // Buscar agendamentos existentes
                $agendamentosOcupados = $this->buscarHorariosOcupados($data);
                
                // Marcar horários como ocupados
                $horariosComStatus = $this->marcarHorariosOcupados($horarios, $agendamentosOcupados);
                
                return [
                    'success' => true,
                    'horarios' => $horariosComStatus,
                    'total_disponivel' => count(array_filter($horariosComStatus, fn($h) => $h['disponivel']))
                ];
                
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Erro ao processar horários',
                    'horarios' => []
                ];
            }
        });
    }

    /**
     * Verifica se um horário específico está disponível
     */
    public function verificarHorarioDisponivel(string $data, string $horario): bool
    {
        $dataHora = Carbon::parse("{$data} {$horario}:00");
        
        return !Agendamento::where('data_agendamento', $data)
            ->where('horario_agendamento', $dataHora)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('ativo', true)
            ->exists();
    }

    /**
     * Cria um novo agendamento público
     */
    public function criarAgendamentoPublico(array $dados): Agendamento
    {
        return Agendamento::create([
            'servico_id' => $dados['servico_id'],
            'data_agendamento' => $dados['data_agendamento'],
            'horario_agendamento' => $dados['horario_agendamento'],
            'cliente_nome' => $dados['nome'],
            'cliente_email' => $dados['email'],
            'cliente_telefone' => $dados['telefone'],
            'observacoes' => $dados['observacoes'] ?? null,
            'status' => $dados['status'],
            'origem' => $dados['origem'],
            'cliente_cadastrado_automaticamente' => $dados['cliente_cadastrado_automaticamente'],
            'ativo' => $dados['ativo']
        ]);
    }

    /**
     * Gera a grade completa de horários para um dia
     */
    private function gerarGradeHorarios(string $data, HorarioFuncionamento $funcionamento): array
    {
        $horarios = [];
        $dataStr = Carbon::parse($data)->format('Y-m-d');
        
        $horaInicio = substr($funcionamento->horario_inicio, 0, 8);
        $horaFim = substr($funcionamento->horario_fim, 0, 8);
        
        $inicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
        $fim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
        
        $current = $inicio->copy();
        $intervalo = 30; // minutos
        
        while ($current < $fim) {
            // Pular horário de almoço se configurado
            if ($funcionamento->tem_almoco) {
                $almocoInicio = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($funcionamento->almoco_inicio, 0, 8));
                $almocoFim = Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($funcionamento->almoco_fim, 0, 8));
                
                if ($current >= $almocoInicio && $current < $almocoFim) {
                    $current->addMinutes($intervalo);
                    continue;
                }
            }
            
            $horarios[] = [
                'value' => $current->format('H:i'),
                'display' => $current->format('H:i'),
                'timestamp' => $current->timestamp
            ];
            
            $current->addMinutes($intervalo);
        }
        
        return $horarios;
    }

    /**
     * Busca horários já ocupados em uma data
     */
    private function buscarHorariosOcupados(string $data): array
    {
        return Agendamento::where('data_agendamento', $data)
            ->whereIn('status', ['pendente', 'confirmado'])
            ->where('ativo', true)
            ->pluck('horario_agendamento')
            ->map(function ($horario) {
                return Carbon::parse($horario)->format('H:i');
            })
            ->toArray();
    }

    /**
     * Marca horários como ocupados na grade
     */
    private function marcarHorariosOcupados(array $horarios, array $ocupados): array
    {
        return array_map(function ($horario) use ($ocupados) {
            $disponivel = !in_array($horario['value'], $ocupados);
            
            return [
                'value' => $horario['value'],
                'display' => $horario['display'],
                'disponivel' => $disponivel,
                'status' => $disponivel ? 'livre' : 'ocupado'
            ];
        }, $horarios);
    }
}