<?php

// config/agendamentos.php - VERSÃO MÍNIMA PARA FUNCIONAMENTO IMEDIATO

return [
    'status' => [
        'principais' => [
            'pendente' => [
                'label' => 'Pendentes',
                'emoji' => '📋',
                'cor' => 'yellow',
                'forma' => 'circle',
                'prioridade' => 1,
                'descricao' => 'Aguardando confirmação',
                'acoes' => ['confirmar', 'cancelar', 'editar'],
                'transicoes_permitidas' => ['confirmado', 'cancelado']
            ],
            'confirmado' => [
                'label' => 'Confirmados',
                'emoji' => '✅',
                'cor' => 'green',
                'forma' => 'circle',
                'prioridade' => 2,
                'descricao' => 'Confirmado pelo cliente',
                'acoes' => ['concluir', 'cancelar', 'editar'],
                'transicoes_permitidas' => ['concluido', 'cancelado']
            ],
            'concluido' => [
                'label' => 'Concluídos',
                'emoji' => '🏁',
                'cor' => 'blue',
                'forma' => 'circle',
                'prioridade' => 3,
                'descricao' => 'Atendimento realizado',
                'acoes' => ['ver_detalhes'],
                'transicoes_permitidas' => []
            ],
            'cancelado' => [
                'label' => 'Cancelados',
                'emoji' => '❌',
                'cor' => 'red',
                'forma' => 'circle',
                'prioridade' => 4,
                'descricao' => 'Cancelado',
                'acoes' => ['ver_detalhes'],
                'transicoes_permitidas' => []
            ]
        ],
        
        'secundarios' => [
            'em_tratamento' => [
                'label' => 'Em Tratamento',
                'emoji' => '🔄',
                'cor' => 'orange',
                'forma' => 'diamond',
                'prioridade' => 5,
                'descricao' => 'Cliente em atendimento',
                'acoes' => ['concluir'],
                'transicoes_permitidas' => ['concluido']
            ],
            'novo_status' => [
                'label' => 'Novo Status',
                'emoji' => '🔥',
                'cor' => 'purple',
                'forma' => 'star',
                'transicoes_permitidas' => ['confirmado']
            ]

        ],
        
        'cores' => [
            'yellow' => [
                'bg' => 'bg-yellow-50',
                'text' => 'text-yellow-800',
                'border' => 'border-yellow-200',
                'hover' => 'hover:bg-yellow-100',
                'ring' => 'ring-yellow-500'
            ],
            'green' => [
                'bg' => 'bg-green-50',
                'text' => 'text-green-800',
                'border' => 'border-green-200',
                'hover' => 'hover:bg-green-100',
                'ring' => 'ring-green-500'
            ],
            'blue' => [
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-800',
                'border' => 'border-blue-200',
                'hover' => 'hover:bg-blue-100',
                'ring' => 'ring-blue-500'
            ],
            'red' => [
                'bg' => 'bg-red-50',
                'text' => 'text-red-800',
                'border' => 'border-red-200',
                'hover' => 'hover:bg-red-100',
                'ring' => 'ring-red-500'
            ],
            'orange' => [
                'bg' => 'bg-orange-50',
                'text' => 'text-orange-800',
                'border' => 'border-orange-200',
                'hover' => 'hover:bg-orange-100',
                'ring' => 'ring-orange-500'
            ],
            'gray' => [
                'bg' => 'bg-gray-50',
                'text' => 'text-gray-800',
                'border' => 'border-gray-200',
                'hover' => 'hover:bg-gray-100',
                'ring' => 'ring-gray-500'
            ]
        ],
        
        'formas' => [
            'circle' => '●',
            'diamond' => '◆',
            'triangle' => '▲',
            'square' => '■'
        ],
        
        'comportamento' => [
            'mostrar_contadores' => true,
            'validar_transicoes' => false,
            'log_mudancas_status' => false,
            'permitir_transicoes_livres' => true
        ]
    ],
    
    'performance' => [
        'cache_contadores' => [
            'enabled' => false,
            'duration' => 300
        ],
        'pagination' => [
            'mobile' => 8,
            'tablet' => 12,
            'desktop' => 15
        ]
    ]
];