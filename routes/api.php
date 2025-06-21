<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API para buscar dados de agendamento
Route::prefix('agendamento')->group(function () {
    
    // Buscar configurações de funcionamento (para saber os dias da semana)
    Route::get('/dias-funcionamento', function () {
        try {
            $diasFuncionamento = DB::table('horarios_funcionamento')
                ->where('ativo', 1)
                ->pluck('dia_semana')
                ->unique()
                ->values()
                ->toArray();
            
            return response()->json([
                'success' => true,
                'dias' => $diasFuncionamento
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dias de funcionamento',
                'dias' => [1, 2, 3, 4, 5] // Fallback: segunda a sexta
            ]);
        }
    });
    
    // Verificar se um dia está disponível
    Route::get('/dia-disponivel/{data}', function ($data) {
        try {
            $dataCarbon = \Carbon\Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            // PRIMEIRO: Verificar se estabelecimento está aberto neste dia
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                return response()->json([
                    'disponivel' => false, 
                    'motivo' => 'Estabelecimento fechado neste dia da semana'
                ]);
            }
            
            // SEGUNDO: Verificar bloqueios apenas se estiver aberto
            $bloqueado = DB::table('bloqueios_agendamento')
                ->where('ativo', 1)
                ->where(function ($query) use ($dataCarbon) {
                    $query->where('tipo', 'data_completa')
                        ->where(function ($subQuery) use ($dataCarbon) {
                            $subQuery->where('data_inicio', $dataCarbon->format('Y-m-d'))
                                ->orWhere(function ($recurrentQuery) use ($dataCarbon) {
                                    $recurrentQuery->where('recorrente', 1)
                                        ->whereRaw('DATE_FORMAT(data_inicio, "%m-%d") = ?', [$dataCarbon->format('m-d')]);
                                });
                        });
                })
                ->where(function ($query) {
                    $query->whereRaw('JSON_CONTAINS(perfis_afetados, ?)', ['"cliente_cadastrado"'])
                        ->orWhereRaw('JSON_CONTAINS(perfis_afetados, ?)', ['"publico"']);
                })
                ->exists();
            
            if ($bloqueado) {
                return response()->json([
                    'disponivel' => false, 
                    'motivo' => 'Data bloqueada/feriado'
                ]);
            }
            
            // Se chegou até aqui: estabelecimento aberto + não bloqueado
            return response()->json([
                'disponivel' => true,
                'motivo' => null,
                'funcionamento' => [
                    'inicio' => substr($horarioFuncionamento->horario_inicio, 0, 5),
                    'fim' => substr($horarioFuncionamento->horario_fim, 0, 5),
                    'tem_almoco' => $horarioFuncionamento->tem_almoco,
                    'almoco_inicio' => $horarioFuncionamento->tem_almoco ? substr($horarioFuncionamento->almoco_inicio, 0, 5) : null,
                    'almoco_fim' => $horarioFuncionamento->tem_almoco ? substr($horarioFuncionamento->almoco_fim, 0, 5) : null
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'disponivel' => false, 
                'motivo' => 'Erro: ' . $e->getMessage()
            ]);
        }
    });
    
    // Buscar horários de funcionamento para uma data específica (para uso futuro)
    Route::get('/horarios/{data}', function ($data) {
        try {
            $dataCarbon = \Carbon\Carbon::parse($data);
            $diaSemana = $dataCarbon->dayOfWeek;
            
            // BUSCAR HORÁRIO DE FUNCIONAMENTO
            $horarioFuncionamento = DB::table('horarios_funcionamento')
                ->where('dia_semana', $diaSemana)
                ->where('ativo', 1)
                ->first();
            
            if (!$horarioFuncionamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estabelecimento fechado neste dia da semana',
                    'horarios' => []
                ]);
            }
            
            // GERAR TODOS OS HORÁRIOS DE FUNCIONAMENTO
            $horarios = [];
            $dataStr = $dataCarbon->format('Y-m-d');
            
            $horaInicio = substr($horarioFuncionamento->horario_inicio, 0, 8);
            $horaFim = substr($horarioFuncionamento->horario_fim, 0, 8);
            
            $inicio = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaInicio);
            $fim = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . $horaFim);
            
            $current = $inicio->copy();
            $intervalo = 30; // minutos
            
            // BUSCAR AGENDAMENTOS EXISTENTES PARA MARCAR COMO OCUPADOS
            $agendamentosOcupados = DB::table('agendamentos')
                ->where('data_agendamento', $dataStr)
                ->whereIn('status', ['pendente', 'confirmado'])
                ->where('ativo', 1)
                ->pluck('horario_agendamento')
                ->map(function($horario) {
                    return \Carbon\Carbon::parse($horario)->format('H:i');
                })
                ->toArray();
            
            // GERAR GRADE COMPLETA DE HORÁRIOS
            while ($current < $fim) {
                // Pular horário de almoço
                if ($horarioFuncionamento->tem_almoco) {
                    $almocoInicio = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_inicio, 0, 8));
                    $almocoFim = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataStr . ' ' . substr($horarioFuncionamento->almoco_fim, 0, 8));
                    
                    if ($current >= $almocoInicio && $current < $almocoFim) {
                        $current->addMinutes($intervalo);
                        continue;
                    }
                }
                
                $horarioFormatado = $current->format('H:i');
                
                // Verificar se este horário tem agendamento
                $temAgendamento = in_array($horarioFormatado, $agendamentosOcupados);
                
                $horarios[] = [
                    'value' => $horarioFormatado,
                    'display' => $horarioFormatado,
                    'disponivel' => !$temAgendamento
                ];
                
                $current->addMinutes($intervalo);
            }
            
            return response()->json([
                'success' => true,
                'horarios' => $horarios,
                'estabelecimento_aberto' => true
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar horários: ' . $e->getMessage(),
                'horarios' => []
            ]);
        }
    });
});