<?php

namespace App\Livewire\Painel;

use Livewire\Component;
use App\Models\ConfiguracaoAgendamento;
use App\Models\HorarioFuncionamento;

class ConfiguracoesAgendamento extends Component
{
    // Estados das abas
    public $aba_ativa = 'horarios';
    
    // Perfil selecionado
    public $perfil_ativo = 'publico';
    
    // Horários específicos por dia (1-7)
    public $horarios_especificos = [];
    
    // Regras de negócio
    public $antecedencia_minima_horas = 2;
    public $antecedencia_maxima_dias = 30;
    public $configuracao_ativa = true;

    // ✅ PROPRIEDADES DOS BLOQUEIOS
    public $bloqueios = [];
    public $mostrarModalBloqueio = false;
    public $editandoBloqueio = false;
    public $bloqueioId = null;
    
    // Dados do bloqueio
    public $tipo_bloqueio = '';
    public $data_inicio_bloqueio = '';
    public $data_fim_bloqueio = '';
    public $horario_inicio_bloqueio = '';
    public $horario_fim_bloqueio = '';
    public $motivo_bloqueio = '';
    public $observacoes_bloqueio = '';
    public $recorrente_bloqueio = false;
    public $perfis_afetados = [];

    public function mount()
    {
        $this->carregarConfiguracoes();
        // Carregar bloqueios apenas se estiver na aba de bloqueios
        if ($this->aba_ativa === 'bloqueios') {
            $this->carregarBloqueios();
        }
    }

    public function render()
    {
        return view('livewire.painel.configuracoes-agendamento')
            ->layout('layouts.painel');
    }

    /**
     * Carrega configurações existentes
     */
    public function carregarConfiguracoes()
    {
        $config = ConfiguracaoAgendamento::where('perfil', $this->perfil_ativo)->first();
        
        if ($config) {
            $this->antecedencia_minima_horas = $config->antecedencia_minima_horas;
            $this->antecedencia_maxima_dias = $config->antecedencia_maxima_dias;
            $this->configuracao_ativa = $config->ativo;
            
            // Garantir que existem horários para todos os dias
            $config->garantirHorariosTodosDias();
            
            // Carregar horários específicos
            $this->carregarHorariosEspecificos($config);
        } else {
            $this->inicializarHorariosPadrao();
        }
    }

    /**
     * Carrega horários específicos do banco
     */
    public function carregarHorariosEspecificos($config)
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $horario = $config->horariosFuncionamento()
                            ->where('dia_semana', $dia)
                            ->first();
            
            if ($horario) {
                $this->horarios_especificos[$dia] = [
                    'ativo' => $horario->ativo,
                    'horario_inicio' => $horario->horario_inicio ? $horario->horario_inicio->format('H:i') : '08:00',
                    'horario_fim' => $horario->horario_fim ? $horario->horario_fim->format('H:i') : '18:00',
                    'tem_almoco' => $horario->tem_almoco,
                    'almoco_inicio' => $horario->almoco_inicio ? $horario->almoco_inicio->format('H:i') : '12:00',
                    'almoco_fim' => $horario->almoco_fim ? $horario->almoco_fim->format('H:i') : '13:00',
                ];
            } else {
                $this->horarios_especificos[$dia] = $this->horarioPadrao($dia);
            }
        }
    }

    /**
     * Inicializa horários padrão se não existir configuração
     */
    public function inicializarHorariosPadrao()
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $this->horarios_especificos[$dia] = $this->horarioPadrao($dia);
        }
    }

    /**
     * Retorna horário padrão para um dia
     */
    private function horarioPadrao($dia)
    {
        return [
            'ativo' => in_array($dia, [1, 2, 3, 4, 5]), // Seg-Sex por padrão
            'horario_inicio' => '08:00',
            'horario_fim' => '18:00',
            'tem_almoco' => true,
            'almoco_inicio' => '12:00',
            'almoco_fim' => '13:00',
        ];
    }

    /**
     * Quando perfil muda, recarrega configurações
     */
    public function updatedPerfilAtivo()
    {
        $this->carregarConfiguracoes();
    }

    /**
     * Quando aba muda, carregar dados necessários
     */
    public function updatedAbaAtiva()
    {
        if ($this->aba_ativa === 'bloqueios') {
            $this->carregarBloqueios();
        }
    }

    /**
     * Salva horários de funcionamento
     */
    public function salvarHorarios()
    {
        $this->validate([
            'horarios_especificos.*.horario_inicio' => 'required_if:horarios_especificos.*.ativo,true',
            'horarios_especificos.*.horario_fim' => 'required_if:horarios_especificos.*.ativo,true',
            'horarios_especificos.*.almoco_inicio' => 'required_if:horarios_especificos.*.tem_almoco,true',
            'horarios_especificos.*.almoco_fim' => 'required_if:horarios_especificos.*.tem_almoco,true',
        ], [
            'horarios_especificos.*.horario_inicio.required_if' => 'Horário de início é obrigatório para dias ativos.',
            'horarios_especificos.*.horario_fim.required_if' => 'Horário de fim é obrigatório para dias ativos.',
            'horarios_especificos.*.almoco_inicio.required_if' => 'Horário de início do almoço é obrigatório.',
            'horarios_especificos.*.almoco_fim.required_if' => 'Horário de fim do almoço é obrigatório.',
        ]);

        try {
            // Buscar ou criar configuração
            $config = ConfiguracaoAgendamento::where('perfil', $this->perfil_ativo)->first();
            
            if (!$config) {
                $config = ConfiguracaoAgendamento::create([
                    'perfil' => $this->perfil_ativo,
                    'antecedencia_minima_horas' => $this->antecedencia_minima_horas,
                    'antecedencia_maxima_dias' => $this->antecedencia_maxima_dias,
                    'ativo' => $this->configuracao_ativa,
                ]);
            }

            // Remover horários existentes
            $config->horariosFuncionamento()->delete();

            // Salvar novos horários
            for ($dia = 1; $dia <= 7; $dia++) {
                $horario = $this->horarios_especificos[$dia];
                
                $config->horariosFuncionamento()->create([
                    'dia_semana' => $dia,
                    'horario_inicio' => $horario['horario_inicio'],
                    'horario_fim' => $horario['horario_fim'],
                    'tem_almoco' => $horario['tem_almoco'],
                    'almoco_inicio' => $horario['tem_almoco'] ? $horario['almoco_inicio'] : null,
                    'almoco_fim' => $horario['tem_almoco'] ? $horario['almoco_fim'] : null,
                    'ativo' => $horario['ativo'],
                ]);
            }

            session()->flash('sucesso', 'Horários salvos com sucesso!');

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar horários: ' . $e->getMessage());
        }
    }

    /**
     * Salva apenas regras de negócio
     */
    public function salvarRegras()
    {
        $this->validate([
            'antecedencia_minima_horas' => 'required|integer|min:1|max:72',
            'antecedencia_maxima_dias' => 'required|integer|min:1|max:365',
        ]);

        try {
            $config = ConfiguracaoAgendamento::updateOrCreate(
                ['perfil' => $this->perfil_ativo],
                [
                    'antecedencia_minima_horas' => $this->antecedencia_minima_horas,
                    'antecedencia_maxima_dias' => $this->antecedencia_maxima_dias,
                    'ativo' => $this->configuracao_ativa,
                ]
            );

            session()->flash('sucesso', 'Regras de negócio salvas com sucesso!');

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar regras: ' . $e->getMessage());
        }
    }

    /**
     * Aplicar horário comercial (Seg-Sex 8h-18h)
     */
    public function aplicarHorarioComercial()
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $this->horarios_especificos[$dia] = [
                'ativo' => in_array($dia, [1, 2, 3, 4, 5]), // Seg-Sex
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'tem_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ];
        }
    }

    /**
     * Incluir fins de semana
     */
    public function aplicarComFinsDeSemana()
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $horarios = [
                'ativo' => true,
                'tem_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ];

            if ($dia == 6) { // Sábado
                $horarios['horario_inicio'] = '08:00';
                $horarios['horario_fim'] = '14:00';
            } elseif ($dia == 7) { // Domingo
                $horarios['horario_inicio'] = '08:00';
                $horarios['horario_fim'] = '12:00';
                $horarios['tem_almoco'] = false;
            } else { // Seg-Sex
                $horarios['horario_inicio'] = '08:00';
                $horarios['horario_fim'] = '18:00';
            }

            $this->horarios_especificos[$dia] = $horarios;
        }
    }

    /**
     * Apenas dias úteis
     */
    public function aplicarSemFimDeSemana()
    {
        for ($dia = 1; $dia <= 7; $dia++) {
            $this->horarios_especificos[$dia] = [
                'ativo' => in_array($dia, [1, 2, 3, 4, 5]),
                'horario_inicio' => '08:00',
                'horario_fim' => '18:00',
                'tem_almoco' => true,
                'almoco_inicio' => '12:00',
                'almoco_fim' => '13:00',
            ];
        }
    }

    /**
     * Copiar horário de um dia para outro
     */
    public function copiarHorarioDia($diaOrigem, $diaDestino)
    {
        if (isset($this->horarios_especificos[$diaOrigem])) {
            $this->horarios_especificos[$diaDestino] = $this->horarios_especificos[$diaOrigem];
        }
    }

    /**
     * Aplicar horário de um dia para todos os outros
     */
    public function aplicarParaTodos($dia)
    {
        if (isset($this->horarios_especificos[$dia])) {
            $horarioBase = $this->horarios_especificos[$dia];
            
            for ($i = 1; $i <= 7; $i++) {
                if ($i !== $dia) {
                    $this->horarios_especificos[$i] = $horarioBase;
                }
            }
        }
    }

    /**
     * ✅ MÉTODOS DOS BLOQUEIOS
     */

    /**
     * Carrega bloqueios do banco
     */
    public function carregarBloqueios()
    {
        try {
            // ❌ PROBLEMA: paginate() retorna objeto não serializável
            // $this->bloqueios = \App\Models\BloqueioAgendamento::orderBy('data_inicio', 'desc')->paginate(10);
            
            // ✅ SOLUÇÃO: usar get() em vez de paginate()
            $this->bloqueios = \App\Models\BloqueioAgendamento::orderBy('data_inicio', 'desc')->get();
        } catch (\Exception $e) {
            $this->bloqueios = collect();
        }
    }

    /**
     * Abre modal para novo bloqueio
     */
    public function abrirModalBloqueio()
    {
        $this->resetarFormularioBloqueio();
        $this->mostrarModalBloqueio = true;
    }

    /**
     * Fecha modal de bloqueio
     */
    public function fecharModalBloqueio()
    {
        $this->mostrarModalBloqueio = false;
        $this->resetarFormularioBloqueio();
    }

    /**
     * Reseta formulário de bloqueio
     */
    private function resetarFormularioBloqueio()
    {
        $this->editandoBloqueio = false;
        $this->bloqueioId = null;
        $this->tipo_bloqueio = '';
        $this->data_inicio_bloqueio = '';
        $this->data_fim_bloqueio = '';
        $this->horario_inicio_bloqueio = '';
        $this->horario_fim_bloqueio = '';
        $this->motivo_bloqueio = '';
        $this->observacoes_bloqueio = '';
        $this->recorrente_bloqueio = false;
        $this->perfis_afetados = [];
        $this->resetErrorBag();
    }

    /**
     * Salva bloqueio
     */
    public function salvarBloqueio()
    {
        $rules = [
            'tipo_bloqueio' => 'required|in:dia_completo,periodo,horario_especifico',
            'data_inicio_bloqueio' => 'required|date',
            'motivo_bloqueio' => 'required|string|max:255',
            'perfis_afetados' => 'required|array|min:1',
        ];

        if ($this->tipo_bloqueio === 'periodo') {
            $rules['data_fim_bloqueio'] = 'required|date|after_or_equal:data_inicio_bloqueio';
        }

        if ($this->tipo_bloqueio === 'horario_especifico') {
            $rules['horario_inicio_bloqueio'] = 'required';
            $rules['horario_fim_bloqueio'] = 'required|after:horario_inicio_bloqueio';
        }

        $this->validate($rules, [
            'tipo_bloqueio.required' => 'Selecione o tipo de bloqueio.',
            'data_inicio_bloqueio.required' => 'Data de início é obrigatória.',
            'motivo_bloqueio.required' => 'Motivo é obrigatório.',
            'perfis_afetados.required' => 'Selecione pelo menos um perfil.',
            'data_fim_bloqueio.after_or_equal' => 'Data de fim deve ser igual ou posterior à data de início.',
            'horario_fim_bloqueio.after' => 'Horário de fim deve ser posterior ao horário de início.',
        ]);

        try {
            $dados = [
                'tipo' => $this->tipo_bloqueio,
                'data_inicio' => $this->data_inicio_bloqueio,
                'data_fim' => $this->tipo_bloqueio === 'periodo' ? $this->data_fim_bloqueio : null,
                'horario_inicio' => $this->tipo_bloqueio === 'horario_especifico' ? $this->horario_inicio_bloqueio : null,
                'horario_fim' => $this->tipo_bloqueio === 'horario_especifico' ? $this->horario_fim_bloqueio : null,
                'motivo' => $this->motivo_bloqueio,
                'observacoes' => $this->observacoes_bloqueio,
                'recorrente' => $this->recorrente_bloqueio,
                'perfis_afetados' => $this->perfis_afetados,
                'ativo' => true,
            ];

            if ($this->editandoBloqueio && $this->bloqueioId) {
                \App\Models\BloqueioAgendamento::find($this->bloqueioId)->update($dados);
                session()->flash('sucesso', 'Bloqueio atualizado com sucesso!');
            } else {
                \App\Models\BloqueioAgendamento::create($dados);
                session()->flash('sucesso', 'Bloqueio criado com sucesso!');
            }

            $this->fecharModalBloqueio();
            $this->carregarBloqueios();

        } catch (\Exception $e) {
            session()->flash('erro', 'Erro ao salvar bloqueio: ' . $e->getMessage());
        }
    }

    /**
     * Edita bloqueio
     */
    public function editarBloqueio($id)
    {
        $bloqueio = \App\Models\BloqueioAgendamento::find($id);
        
        if (!$bloqueio) {
            session()->flash('erro', 'Bloqueio não encontrado.');
            return;
        }

        $this->bloqueioId = $bloqueio->id;
        $this->tipo_bloqueio = $bloqueio->tipo;
        $this->data_inicio_bloqueio = $bloqueio->data_inicio->format('Y-m-d');
        $this->data_fim_bloqueio = $bloqueio->data_fim ? $bloqueio->data_fim->format('Y-m-d') : '';
        $this->horario_inicio_bloqueio = $bloqueio->horario_inicio ? $bloqueio->horario_inicio->format('H:i') : '';
        $this->horario_fim_bloqueio = $bloqueio->horario_fim ? $bloqueio->horario_fim->format('H:i') : '';
        $this->motivo_bloqueio = $bloqueio->motivo;
        $this->observacoes_bloqueio = $bloqueio->observacoes;
        $this->recorrente_bloqueio = $bloqueio->recorrente;
        $this->perfis_afetados = $bloqueio->perfis_afetados ?? [];

        $this->editandoBloqueio = true;
        $this->mostrarModalBloqueio = true;
    }

    /**
     * Alterna status do bloqueio
     */
    public function alternarStatusBloqueio($id)
    {
        $bloqueio = \App\Models\BloqueioAgendamento::find($id);
        
        if ($bloqueio) {
            $bloqueio->update(['ativo' => !$bloqueio->ativo]);
            
            $status = $bloqueio->ativo ? 'ativado' : 'desativado';
            session()->flash('sucesso', "Bloqueio {$status} com sucesso!");
            
            $this->carregarBloqueios();
        }
    }

    /**
     * Exclui bloqueio
     */
    public function excluirBloqueio($id)
    {
        $bloqueio = \App\Models\BloqueioAgendamento::find($id);
        
        if ($bloqueio) {
            $bloqueio->delete();
            session()->flash('sucesso', 'Bloqueio excluído com sucesso!');
            $this->carregarBloqueios();
        }
    }
}