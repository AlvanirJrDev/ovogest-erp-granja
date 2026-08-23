<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Extrato — {{ $cliente->nome }}</title>
<style>
    * { font-family: "DejaVu Sans", sans-serif; color: #1f2937; }
    body { font-size: 11px; margin: 0; }
    .header { border-bottom: 3px solid #d97706; padding-bottom: 10px; margin-bottom: 14px; }
    .header h1 { margin: 0; font-size: 20px; }
    .header .doc { float: right; text-align: right; }
    .header .doc .titulo { font-size: 15px; font-weight: bold; color: #d97706; }
    .cliente { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; }
    table.dados { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.dados th { background: #1f2937; color: #fff; padding: 5px 7px; text-align: left; font-size: 9.5px; }
    table.dados td { padding: 4px 7px; border-bottom: 1px solid #e5e7eb; font-size: 9.5px; }
    table.dados .num { text-align: right; }
    .saldo { background: #fffbeb; border: 2px solid #f59e0b; border-radius: 8px; padding: 10px 14px; text-align: right; font-size: 13px; font-weight: bold; color: #92400e; margin-top: 10px; }
    .rodape { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
    <div class="header">
        <div class="doc">
            <div class="titulo">EXTRATO DO CLIENTE</div>
            <div>{{ $de->format('d/m/Y') }} a {{ $ate->format('d/m/Y') }}</div>
        </div>
        <h1>{{ $granja?->nome ?? 'OvoGest' }}</h1>
        <div>{{ $granja?->documento ? 'CNPJ '.$granja->documento : '' }}</div>
    </div>

    <div class="cliente">
        <b>{{ $cliente->nome }}</b>
        {{ collect([$cliente->documento, $cliente->telefone, $cliente->endereco])->filter()->implode(' · ') }}
    </div>

    @forelse ($vendas as $v)
        <table class="dados">
            <tr>
                <th colspan="3">Venda #{{ $v->numero }} · {{ $v->data_hora->format('d/m/Y H:i') }} · {{ $v->forma_pagamento->getLabel() }}</th>
                <th class="num">Total R$ {{ number_format($v->valor_total, 2, ',', '.') }}</th>
            </tr>
            @foreach ($v->itens as $item)
                <tr>
                    <td colspan="2">{{ $item->produto->nome }} — {{ $item->produto->tipo_bandeja->getLabel() }}</td>
                    <td class="num">{{ $item->quantidade }} × R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="num">R$ {{ number_format($item->quantidade * $item->valor_unitario, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            @if ((float) $v->valor_pago > 0)
                <tr><td colspan="3">Pago no ato</td><td class="num">R$ {{ number_format($v->valor_pago, 2, ',', '.') }}</td></tr>
            @endif
            @foreach ($v->recebimentos as $r)
                <tr><td colspan="3">Recebimento em {{ $r->data->format('d/m/Y') }} ({{ $r->forma }})</td><td class="num">R$ {{ number_format($r->valor, 2, ',', '.') }}</td></tr>
            @endforeach
            <tr>
                <td colspan="3"><b>Em aberto desta venda</b>{{ $v->data_vencimento ? ' · vencimento '.$v->data_vencimento->format('d/m/Y') : '' }}</td>
                <td class="num"><b>R$ {{ number_format($v->valor_em_aberto, 2, ',', '.') }}</b></td>
            </tr>
        </table>
    @empty
        <p>Nenhuma venda no período selecionado.</p>
    @endforelse

    <div class="saldo">Saldo devedor total do cliente (todas as vendas): R$ {{ number_format($saldoDevedorTotal, 2, ',', '.') }}</div>

    <div class="rodape">Extrato do cliente · emitido via OvoGest · AJR Software em {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
