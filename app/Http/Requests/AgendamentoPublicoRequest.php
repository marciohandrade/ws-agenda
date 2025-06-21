<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Carbon\Carbon;

class AgendamentoPublicoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Agendamento público - todos podem acessar
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'servico_id' => [
                'required',
                'integer',
                'exists:servicos,id,ativo,1'
            ],
            'data_agendamento' => [
                'required',
                'date',
                'after:today',
                'before:' . Carbon::now()->addMonths(2)->toDateString() // Máximo 2 meses à frente
            ],
            'horario_agendamento' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    // Validar se horário está dentro do horário comercial (8h-18h)
                    $hora = (int) substr($value, 0, 2);
                    if ($hora < 8 || $hora >= 18) {
                        $fail('Horário deve estar entre 08:00 e 17:59.');
                    }
                }
            ],
            'nome' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s]+$/' // Apenas letras e espaços
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'lowercase'
            ],
            'telefone' => [
                'required',
                'string',
                'min:10',
                'max:15',
                'regex:/^[\(\)\d\s\-\+]+$/' // Formato de telefone flexível
            ],
            'observacoes' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'servico_id.required' => 'Selecione um serviço.',
            'servico_id.exists' => 'Serviço selecionado não está disponível.',
            
            'data_agendamento.required' => 'Selecione uma data.',
            'data_agendamento.after' => 'A data deve ser posterior a hoje.',
            'data_agendamento.before' => 'Agendamentos disponíveis apenas para os próximos 2 meses.',
            
            'horario_agendamento.required' => 'Selecione um horário.',
            'horario_agendamento.date_format' => 'Formato de horário inválido.',
            
            'nome.required' => 'Informe seu nome completo.',
            'nome.min' => 'Nome deve ter pelo menos 2 caracteres.',
            'nome.regex' => 'Nome deve conter apenas letras.',
            
            'email.required' => 'Informe seu email.',
            'email.email' => 'Email deve ter um formato válido.',
            
            'telefone.required' => 'Informe seu telefone.',
            'telefone.min' => 'Telefone deve ter pelo menos 10 dígitos.',
            'telefone.regex' => 'Formato de telefone inválido.',
            
            'observacoes.max' => 'Observações não podem exceder 500 caracteres.'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => $this->sanitizeName($this->nome),
            'email' => strtolower(trim($this->email ?? '')),
            'telefone' => $this->sanitizePhone($this->telefone),
            'observacoes' => trim($this->observacoes ?? '')
        ]);
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        
        // Adicionar campos calculados
        $validated['origem'] = 'publico';
        $validated['status'] = 'pendente';
        $validated['ativo'] = true;
        $validated['cliente_cadastrado_automaticamente'] = false;
        
        // Formatar horário para timestamp completo
        $data = Carbon::parse($validated['data_agendamento']);
        $horario = $validated['horario_agendamento'];
        $validated['horario_agendamento'] = $data->format('Y-m-d') . ' ' . $horario . ':00';
        
        return $validated;
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        $errors = $validator->errors()->toArray();
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados inválidos. Verifique os campos destacados.',
                'errors' => $errors,
                'first_error' => $validator->errors()->first()
            ], 422)
        );
    }

    /**
     * Sanitiza o nome removendo caracteres especiais
     */
    private function sanitizeName(?string $name): string
    {
        if (!$name) return '';
        
        // Remove múltiplos espaços e trim
        $name = preg_replace('/\s+/', ' ', trim($name));
        
        // Capitaliza cada palavra
        return mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Sanitiza o telefone removendo caracteres especiais
     */
    private function sanitizePhone(?string $phone): string
    {
        if (!$phone) return '';
        
        // Remove tudo exceto números
        $numbers = preg_replace('/\D/', '', $phone);
        
        // Formata conforme o padrão brasileiro
        if (strlen($numbers) === 11) {
            return "({$numbers[0]}{$numbers[1]}) {$numbers[2]}{$numbers[3]}{$numbers[4]}{$numbers[5]}{$numbers[6]}-{$numbers[7]}{$numbers[8]}{$numbers[9]}{$numbers[10]}";
        } elseif (strlen($numbers) === 10) {
            return "({$numbers[0]}{$numbers[1]}) {$numbers[2]}{$numbers[3]}{$numbers[4]}{$numbers[5]}-{$numbers[6]}{$numbers[7]}{$numbers[8]}{$numbers[9]}";
        }
        
        return $phone; // Retorna original se não conseguir formatar
    }
}