<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;
use Carbon\Carbon;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        $clientes = [
            [
                'nome' => 'Ana Silva Santos',
                'email' => 'ana.silva@email.com',
                'telefone' => '11987654321',
                'cpf' => '12345678901',
                'cep' => '01310100',
                'data_nascimento' => '1985-03-15',
                'genero' => 'Feminino',
                'endereco' => 'Avenida Paulista',
                'numero' => '1578',
                'complemento' => 'Apto 142'
            ],
            [
                'nome' => 'Bruno Costa Lima',
                'email' => 'bruno.costa@email.com',
                'telefone' => '11976543210',
                'cpf' => '23456789012',
                'cep' => '04038001',
                'data_nascimento' => '1990-07-22',
                'genero' => 'Masculino',
                'endereco' => 'Rua Augusta',
                'numero' => '2456',
                'complemento' => null
            ],
            [
                'nome' => 'Carla Fernandes',
                'email' => 'carla.fernandes@email.com',
                'telefone' => '11965432109',
                'cpf' => '34567890123',
                'cep' => '01414001',
                'data_nascimento' => '1988-12-10',
                'genero' => 'Feminino',
                'endereco' => 'Rua Oscar Freire',
                'numero' => '789',
                'complemento' => 'Loja 5'
            ],
            [
                'nome' => 'Diego Almeida',
                'email' => 'diego.almeida@email.com',
                'telefone' => '11954321098',
                'cpf' => '45678901234',
                'cep' => '05407002',
                'data_nascimento' => '1992-04-08',
                'genero' => 'Masculino',
                'endereco' => 'Rua Teodoro Sampaio',
                'numero' => '1245',
                'complemento' => 'Sala 301'
            ],
            [
                'nome' => 'Eduarda Ribeiro',
                'email' => 'eduarda.ribeiro@email.com',
                'telefone' => '11943210987',
                'cpf' => '56789012345',
                'cep' => '01310200',
                'data_nascimento' => '1995-11-25',
                'genero' => 'Feminino',
                'endereco' => 'Avenida Paulista',
                'numero' => '900',
                'complemento' => null
            ],
            [
                'nome' => 'Felipe Santos',
                'email' => 'felipe.santos@email.com',
                'telefone' => '11932109876',
                'cpf' => '67890123456',
                'cep' => '04094050',
                'data_nascimento' => '1987-02-14',
                'genero' => 'Masculino',
                'endereco' => 'Rua Vergueiro',
                'numero' => '3678',
                'complemento' => 'Bloco B'
            ],
            [
                'nome' => 'Gabriela Oliveira',
                'email' => 'gabriela.oliveira@email.com',
                'telefone' => '11921098765',
                'cpf' => '78901234567',
                'cep' => '01333010',
                'data_nascimento' => '1993-09-03',
                'genero' => 'Feminino',
                'endereco' => 'Rua Bela Cintra',
                'numero' => '567',
                'complemento' => 'Apto 78'
            ],
            [
                'nome' => 'Henrique Machado',
                'email' => 'henrique.machado@email.com',
                'telefone' => '11910987654',
                'cpf' => '89012345678',
                'cep' => '01452000',
                'data_nascimento' => '1989-06-17',
                'genero' => 'Masculino',
                'endereco' => 'Rua da Consolação',
                'numero' => '1890',
                'complemento' => null
            ],
            [
                'nome' => 'Isabela Martins',
                'email' => 'isabela.martins@email.com',
                'telefone' => '11999876543',
                'cpf' => '90123456789',
                'cep' => '04011001',
                'data_nascimento' => '1991-01-28',
                'genero' => 'Feminino',
                'endereco' => 'Rua Libero Badaró',
                'numero' => '425',
                'complemento' => 'Conj 1205'
            ],
            [
                'nome' => 'João Pedro Silva',
                'email' => 'joao.pedro@email.com',
                'telefone' => '11988765432',
                'cpf' => '01234567890',
                'cep' => '01310300',
                'data_nascimento' => '1986-05-12',
                'genero' => 'Masculino',
                'endereco' => 'Avenida Paulista',
                'numero' => '1450',
                'complemento' => 'Torre Norte'
            ]
        ];

        // Nomes adicionais para completar 50
        $nomesAdicionais = [
            'Karla Souza', 'Lucas Pereira', 'Mariana Torres', 'Nicolas Barbosa', 'Olivia Rocha',
            'Paulo Dias', 'Queila Nunes', 'Rafael Mendes', 'Sofia Cardoso', 'Thiago Correia',
            'Ursula Moreira', 'Victor Hugo', 'Wanda Farias', 'Xavier Lopes', 'Yara Azevedo',
            'Zoe Batista', 'Amanda Freitas', 'Bernardo Ramos', 'Cecília Vasconcelos', 'Daniel Castro',
            'Elaine Melo', 'Fabio Teixeira', 'Giovanna Pinto', 'Hugo Nascimento', 'Iris Campos',
            'Julio Cesar', 'Kelly Aragão', 'Leonardo Braga', 'Monica Vieira', 'Nathan Coelho',
            'Otavio Gomes', 'Patricia Lima', 'Quintino Reis', 'Roberta Sá', 'Samuel Borges',
            'Tatiana Cunha', 'Ulisses Franco', 'Viviane Paiva', 'Wagner Moura', 'Ximena Lago'
        ];

        $generos = ['Masculino', 'Feminino', 'Não-binário', 'Prefere não informar'];
        $enderecos = [
            'Rua das Flores', 'Avenida Brasil', 'Rua São João', 'Avenida Faria Lima',
            'Rua Joaquim Floriano', 'Avenida Rebouças', 'Rua Haddock Lobo', 'Avenida Ibirapuera',
            'Rua Pamplona', 'Avenida Europa', 'Rua Funchal', 'Avenida Pedroso de Morais',
            'Rua Cardeal Arcoverde', 'Avenida Cidade Jardim', 'Rua Estados Unidos'
        ];
        $ceps = [
            '01310100', '04038001', '01414001', '05407002', '01310200', '04094050',
            '01333010', '01452000', '04011001', '05406000', '01308100', '04567001',
            '01234567', '05678901', '02345678', '03456789', '06789012', '07890123'
        ];

        // Inserir os 10 primeiros registros detalhados
        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        // Gerar mais 40 registros automaticamente
        for ($i = 0; $i < 40; $i++) {
            $nome = $nomesAdicionais[$i];
            $email = strtolower(str_replace(' ', '.', $nome)) . '@email.com';
            $cpf = str_pad(rand(10000000000, 99999999999), 11, '0', STR_PAD_LEFT);
            $telefone = '119' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            Cliente::create([
                'nome' => $nome,
                'email' => $email,
                'telefone' => $telefone,
                'cpf' => $cpf,
                'cep' => $ceps[array_rand($ceps)],
                'data_nascimento' => Carbon::now()->subYears(rand(18, 65))->subDays(rand(1, 365))->format('Y-m-d'),
                'genero' => $generos[array_rand($generos)],
                'endereco' => $enderecos[array_rand($enderecos)],
                'numero' => rand(100, 9999),
                'complemento' => rand(0, 1) ? 'Apto ' . rand(10, 999) : null,
            ]);
        }

        $this->command->info('50 clientes criados com sucesso!');
    }
}