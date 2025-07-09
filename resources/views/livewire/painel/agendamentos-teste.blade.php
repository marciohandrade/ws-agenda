<div style="background: lightgreen; padding: 20px;">
    <h1>🎯 TESTE VIEW AGENDAMENTOS</h1>
    <p><strong>Total de agendamentos:</strong> {{ $agendamentos->count() }}</p>
    <p><strong>Total de clientes:</strong> {{ $clientes->count() }}</p>
    <p><strong>Total de serviços:</strong> {{ $servicos->count() }}</p>
    <p><strong>Data/Hora:</strong> {{ now() }}</p>
    
    <h3>Primeiros 3 agendamentos:</h3>
    @foreach($agendamentos->take(3) as $agendamento)
        <p>- Cliente: {{ $agendamento->cliente->nome ?? 'N/A' }} | Data: {{ $agendamento->data_agendamento }}</p>
    @endforeach
</div>