<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendamentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Agendamento público é permitido para todos
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Dados pessoais
            'nome' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/' // Apenas letras e espaços
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255'
            ],
            'telefone' => [
                'required',
                'string',
                'regex:/^\(\d{2}\)\s\d{4,5}-\d{4}$/' // Formato (11) 99999-9999
            ],
            'data_nascimento' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'genero' => [
                'nullable',
                Rule::in(['Masculino', 'Feminino', 'Não-binário', 'Prefere não informar'])
            ],
            'cpf' => [
                'nullable',
                'string',
                'regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', // Formato 000.000.000-00
                'cpf' // Custom rule (se implementada)
            ],
            'cep' => [
                'nullable',
                'string',
                'regex:/^\d{5}-\d{3}$/' // Formato 00000-000
            ],
            'endereco' => [
                'nullable',
                'string',
                'max:255'
            ],
            'numero' => [
                'nullable',
                'string',
                'max:10'
            ],
            'complemento' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Dados do agendamento
            'servico_id' => [
                'required',
                'exists:servicos,id',
                function ($attribute, $value, $fail) {
                    $servico = \App\Models\Servico::find($value);
                    if (!$servico || !$servico->ativo) {
                        $fail('O serviço selecionado não está disponível.');
                    }
                }
            ],
            'data_agendamento' => [
                'required',
                'date',
                'after_or_equal:today',
                'before:' . now()->addMonths(3)->format('Y-m-d'), // Máximo 3 meses no futuro
                function ($attribute, $value, $fail) {
                    $data = \Carbon\Carbon::parse($value);
                    if ($data->isWeekend()) {
                        $fail('Não atendemos aos finais de semana.');
                    }
                }
            ],
            'horario_agendamento' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $horario = \Carbon\Carbon::createFromFormat('H:i', $value);
                    $inicio = \Carbon\Carbon::createFromTime(8, 0);
                    $fim = \Carbon\Carbon::createFromTime(18, 0);
                    
                    if ($horario < $inicio || $horario > $fim) {
                        $fail('Horário deve estar entre 08:00 e 18:00.');
                    }
                    
                    // Verificar se horário já está ocupado
                    if ($this->horarioJaOcupado()) {
                        $fail('Este horário já está ocupado.');
                    }
                }
            ],
            'observacoes' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Dados pessoais
            'nome.required' => 'O nome é obrigatório.',
            'nome.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'nome.regex' => 'O nome deve conter apenas letras.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'telefone.required' => 'O telefone é obrigatório.',
            'telefone.regex' => 'Telefone deve estar no formato (11) 99999-9999.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'data_nascimento.after' => 'Data de nascimento inválida.',
            'cpf.regex' => 'CPF deve estar no formato 000.000.000-00.',
            'cep.regex' => 'CEP deve estar no formato 00000-000.',
            'endereco.max' => 'Endereço deve ter no máximo 255 caracteres.',
            'numero.max' => 'Número deve ter no máximo 10 caracteres.',
            'complemento.max' => 'Complemento deve ter no máximo 100 caracteres.',

            // Dados do agendamento
            'servico_id.required' => 'Selecione um serviço.',
            'servico_id.exists' => 'Serviço inválido.',
            'data_agendamento.required' => 'A data é obrigatória.',
            'data_agendamento.after_or_equal' => 'A data não pode ser anterior a hoje.',
            'data_agendamento.before' => 'Data muito distante. Máximo 3 meses.',
            'horario_agendamento.required' => 'O horário é obrigatório.',
            'horario_agendamento.date_format' => 'Formato de horário inválido.',
            'observacoes.max' => 'Observações devem ter no máximo 1000 caracteres.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'email' => 'e-mail',
            'telefone' => 'telefone',
            'data_nascimento' => 'data de nascimento',
            'genero' => 'gênero',
            'cpf' => 'CPF',
            'cep' => 'CEP',
            'endereco' => 'endereço',
            'numero' => 'número',
            'complemento' => 'complemento',
            'servico_id' => 'serviço',
            'data_agendamento' => 'data do agendamento',
            'horario_agendamento' => 'horário do agendamento',
            'observacoes' => 'observações'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpar e formatar dados antes da validação
        if ($this->has('cpf')) {
            $this->merge([
                'cpf_limpo' => preg_replace('/[^0-9]/', '', $this->cpf)
            ]);
        }

        if ($this->has('cep')) {
            $this->merge([
                'cep_limpo' => preg_replace('/[^0-9]/', '', $this->cep)
            ]);
        }

        if ($this->has('telefone')) {
            $this->merge([
                'telefone_limpo' => preg_replace('/[^0-9]/', '', $this->telefone)
            ]);
        }
    }

    /**
     * Verifica se o horário já está ocupado
     */
    private function horarioJaOcupado(): bool
    {
        if (!$this->data_agendamento || !$this->horario_agendamento) {
            return false;
        }

        return \App\Models\Agendamento::where('data_agendamento', $this->data_agendamento)
            ->where('horario_agendamento', $this->horario_agendamento)
            ->whereNotIn('status', ['cancelado'])
            ->exists();
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validações adicionais que dependem de múltiplos campos
            
            // Se informou CEP, deve informar endereço
            if ($this->filled('cep') && $this->missing('endereco')) {
                $validator->errors()->add('endereco', 'Endereço é obrigatório quando CEP é informado.');
            }
            
            // Se informou endereço, deve informar número
            if ($this->filled('endereco') && $this->missing('numero')) {
                $validator->errors()->add('numero', 'Número é obrigatório quando endereço é informado.');
            }
            
            // Validar se a data é um dia útil (segunda a sexta)
            if ($this->filled('data_agendamento')) {
                $data = \Carbon\Carbon::parse($this->data_agendamento);
                if ($data->isWeekend()) {
                    $validator->errors()->add('data_agendamento', 'Não atendemos aos finais de semana.');
                }
            }
        });
    }
}