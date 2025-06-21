<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgendamentoPublicoRequest;
use App\Models\Agendamento;
use App\Models\Servico;
use App\Models\HorarioFuncionamento;
use App\Models\BloqueioAgendamento;
use App\Services\AgendamentoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AgendamentoPublicoController extends Controller
{
    protected AgendamentoService $agendamentoService;

    public function __construct(AgendamentoService $agendamentoService)
    {
        $this->agendamentoService = $agendamentoService;
        
        // Rate limiting para proteção
        $this->middleware('throttle:agendamento')->only(['store']);
    }

    /**
     * Exibe a página principal de agendamento público
     */
    public function index()
    {
        return view('agendamento.publico');
    }

    /**
     * Lista todos os serviços ativos
     */
    public function servicos(): JsonResponse
    {
        try {
            $servicos = Cache::remember('servicos_ativos', 300, function () {
                return Servico::where('ativo', true)
                    ->select('id', 'nome', 'descricao', 'duracao_minutos', 'preco')
                    ->orderBy('nome')
                    ->get()
                    ->map(function ($servico) {
                        return [
                            'id' => $servico->id,
                            'nome' => $servico->nome,
                            'descricao' => $servico->descricao,
                            'duracao' => $servico->duracao_minutos,
                            'preco' => number_format($servico->preco, 2, ',', '.'),
                            'display' => "{$servico->nome} - R$ " . number_format($servico->preco, 2, ',', '.') . " ({$servico->duracao_minutos}min)"
                        ];
                    });
            });

            return response()->json([
                'success' => true,
                'servicos' => $servicos
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar serviços: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar serviços'
            ], 500);
        }
    }

    /**
     * Retorna os dias da semana que o estabelecimento funciona
     */
    public function diasFuncionamento(): JsonResponse
    {
        try {
            $dias = Cache::remember('dias_funcionamento', 600, function () {
                return HorarioFuncionamento::where('ativo', true)
                    ->pluck('dia_semana')
                    ->unique()
                    ->values()
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'dias' => $dias
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dias de funcionamento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dias de funcionamento',
                'dias' => [1, 2, 3, 4, 5] // fallback seg-sex
            ]);
        }
    }

    /**
     * Verifica se uma data específica está disponível para agendamentos
     */
    public function verificarDiaDisponivel(string $data): JsonResponse
    {
        try {
            $resultado = $this->agendamentoService->verificarDiaDisponivel($data);
            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar dia disponível: ' . $e->getMessage());
            return response()->json([
                'disponivel' => false,
                'motivo' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Lista os horários disponíveis para uma data específica
     */
    public function horariosDisponiveis(string $data): JsonResponse
    {
        try {
            $resultado = $this->agendamentoService->obterHorariosDisponiveis($data);
            return response()->json($resultado);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar horários disponíveis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar horários',
                'horarios' => []
            ], 500);
        }
    }

    /**
     * Salva um novo agendamento público
     */
    public function store(AgendamentoPublicoRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            // Verificar disponibilidade novamente antes de salvar
            $disponivel = $this->agendamentoService->verificarHorarioDisponivel(
                $request->data_agendamento,
                $request->horario_agendamento
            );

            if (!$disponivel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Horário não está mais disponível. Selecione outro horário.',
                    'codigo' => 'HORARIO_OCUPADO'
                ], 409);
            }

            // Criar o agendamento
            $agendamento = $this->agendamentoService->criarAgendamentoPublico($request->validated());

            DB::commit();

            // Limpar cache relacionado
            $this->limparCacheHorarios($request->data_agendamento);

            return response()->json([
                'success' => true,
                'message' => 'Agendamento realizado com sucesso! Você receberá uma confirmação em breve.',
                'agendamento' => [
                    'id' => $agendamento->id,
                    'data' => $agendamento->data_agendamento->format('d/m/Y'),
                    'horario' => $agendamento->horario_agendamento->format('H:i'),
                    'servico' => $agendamento->servico->nome,
                    'cliente' => $agendamento->cliente_nome
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar agendamento: ' . $e->getMessage(), [
                'dados' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno. Tente novamente em alguns instantes.'
            ], 500);
        }
    }

    /**
     * Limpa o cache de horários para uma data específica
     */
    private function limparCacheHorarios(string $data): void
    {
        Cache::forget("horarios_disponiveis_{$data}");
        Cache::forget("dia_disponivel_{$data}");
    }
}