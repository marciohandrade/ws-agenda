<?php

namespace App\Http\Controllers;

use App\Models\Agendamento;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AgendamentoController extends Controller
{
    /**
     * ✅ ALTERAR STATUS - AÇÃO AJAX OTIMIZADA
     * 
     * @param Agendamento $agendamento
     * @param Request $request
     * @return JsonResponse
     */
    public function alterarStatus(Agendamento $agendamento, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:pendente,confirmado,concluido,cancelado'
            ]);

            $statusAnterior = $agendamento->status;
            $novoStatus = $request->input('status');

            // ✅ REGRAS DE NEGÓCIO
            $transicoesPossiveis = [
                'pendente' => ['confirmado', 'cancelado'],
                'confirmado' => ['concluido', 'cancelado', 'pendente'],
                'concluido' => [], // Não pode alterar após concluído
                'cancelado' => ['pendente'] // Pode reativar apenas para pendente
            ];

            if (!in_array($novoStatus, $transicoesPossiveis[$statusAnterior] ?? [])) {
                return response()->json([
                    'success' => false,
                    'message' => "Não é possível alterar de '{$statusAnterior}' para '{$novoStatus}'"
                ], 422);
            }

            // ✅ ATUALIZA STATUS
            $agendamento->update(['status' => $novoStatus]);

            // ✅ MENSAGENS PERSONALIZADAS
            $mensagens = [
                'pendente' => 'Agendamento está pendente de confirmação',
                'confirmado' => 'Agendamento confirmado com sucesso!',
                'concluido' => 'Agendamento concluído!',
                'cancelado' => 'Agendamento cancelado'
            ];

            // ✅ LOG DA ALTERAÇÃO (opcional)
            if (config('app.log_agendamentos', false)) {
                \Log::info("Status do agendamento #{$agendamento->id} alterado", [
                    'agendamento_id' => $agendamento->id,
                    'cliente' => $agendamento->cliente->nome ?? 'N/A',
                    'status_anterior' => $statusAnterior,
                    'novo_status' => $novoStatus,
                    'usuario' => auth()->user()->name ?? 'Sistema',
                    'timestamp' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $mensagens[$novoStatus],
                'data' => [
                    'id' => $agendamento->id,
                    'status_anterior' => $statusAnterior,
                    'novo_status' => $novoStatus,
                    'status_formatado' => ucfirst($novoStatus),
                    'updated_at' => $agendamento->fresh()->updated_at->format('d/m/Y H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error("Erro ao alterar status do agendamento #{$agendamento->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor. Tente novamente.'
            ], 500);
        }
    }

    /**
     * ✅ EXCLUIR AGENDAMENTO - AJAX
     * 
     * @param Agendamento $agendamento
     * @return JsonResponse
     */
    public function excluir(Agendamento $agendamento): JsonResponse
    {
        try {
            // ✅ REGRAS DE NEGÓCIO PARA EXCLUSÃO
            if ($agendamento->status === 'concluido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir agendamentos já concluídos'
                ], 422);
            }

            // ✅ BACKUP DOS DADOS ANTES DA EXCLUSÃO (para auditoria)
            $dadosBackup = [
                'id' => $agendamento->id,
                'cliente_nome' => $agendamento->cliente->nome ?? 'N/A',
                'servico_nome' => $agendamento->servico->nome ?? 'N/A',
                'data_agendamento' => $agendamento->data_agendamento,
                'horario_agendamento' => $agendamento->horario_agendamento,
                'status' => $agendamento->status,
                'excluido_por' => auth()->user()->name ?? 'Sistema',
                'excluido_em' => now()
            ];

            // ✅ LOG DA EXCLUSÃO
            if (config('app.log_agendamentos', false)) {
                \Log::info("Agendamento #{$agendamento->id} excluído", $dadosBackup);
            }

            // ✅ EXCLUI O AGENDAMENTO
            $agendamento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Agendamento excluído com sucesso!',
                'data' => [
                    'id' => $dadosBackup['id'],
                    'cliente_nome' => $dadosBackup['cliente_nome']
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro ao excluir agendamento #{$agendamento->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir agendamento. Tente novamente.'
            ], 500);
        }
    }

    /**
     * ✅ OBTER HORÁRIOS DISPONÍVEIS - AJAX
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function horariosDisponiveis(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'data' => 'required|date|after_or_equal:today',
                'agendamento_id' => 'nullable|exists:agendamentos,id'
            ]);

            $data = $request->input('data');
            $agendamentoId = $request->input('agendamento_id');

            // ✅ CONFIGURAÇÕES DE FUNCIONAMENTO
            $horarios = [];
            $inicio = \Carbon\Carbon::createFromFormat('H:i', '08:00');
            $fim = \Carbon\Carbon::createFromFormat('H:i', '18:00');
            $intervalo = 30; // minutos

            // ✅ GERA HORÁRIOS DISPONÍVEIS
            while ($inicio < $fim) {
                $horarioStr = $inicio->format('H:i');
                
                // ✅ VERIFICA SE ESTÁ OCUPADO
                $ocupado = Agendamento::where('data_agendamento', $data)
                    ->where('horario_agendamento', $horarioStr)
                    ->when($agendamentoId, function($query, $id) {
                        $query->where('id', '!=', $id);
                    })
                    ->where('status', '!=', 'cancelado') // Ignora cancelados
                    ->exists();
                    
                $horarios[] = [
                    'horario' => $horarioStr,
                    'disponivel' => !$ocupado
                ];
                
                $inicio->addMinutes($intervalo);
            }

            return response()->json([
                'success' => true,
                'data' => $horarios
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data inválida',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error("Erro ao buscar horários disponíveis", [
                'data' => $request->input('data'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar horários. Tente novamente.'
            ], 500);
        }
    }

    /**
     * ✅ ESTATÍSTICAS RÁPIDAS - AJAX
     * 
     * @return JsonResponse
     */
    public function estatisticas(): JsonResponse
    {
        try {
            $hoje = today();
            
            $stats = [
                'hoje' => Agendamento::whereDate('data_agendamento', $hoje)->count(),
                'amanha' => Agendamento::whereDate('data_agendamento', $hoje->copy()->addDay())->count(),
                'semana' => Agendamento::whereBetween('data_agendamento', [
                    $hoje, $hoje->copy()->addWeek()
                ])->count(),
                'mes' => Agendamento::whereMonth('data_agendamento', $hoje->month)
                    ->whereYear('data_agendamento', $hoje->year)->count(),
                'pendentes' => Agendamento::where('status', 'pendente')
                    ->whereDate('data_agendamento', '>=', $hoje)->count(),
                'confirmados' => Agendamento::where('status', 'confirmado')
                    ->whereDate('data_agendamento', '>=', $hoje)->count(),
                'total_geral' => Agendamento::count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'updated_at' => now()->format('d/m/Y H:i:s')
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro ao carregar estatísticas", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas'
            ], 500);
        }
    }
}