<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Fechamento {{ $inicio->translatedFormat('F/Y') }}</title>
<style>
    * { font-family: "DejaVu Sans", sans-serif; color: #1f2937; }
    body { font-size: 11px; margin: 0; }
    .header { border-bottom: 3px solid #d97706; padding-bottom: 10px; margin-bottom: 14px; }
    .header h1 { margin: 0; font-size: 20px; }
    .header .doc { float: right; text-align: right; }
    .header .doc .titulo { font-size: 15px; font-weight: bold; color: #d97706; }
    table.kpis { width: 100%; border-spacing: 6px; border-collapse: separate; margin-bottom: 12px; }
    .kpi { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 9px 12px; }
    .kpi .r { font-size: 9px; color: #6b7280; text-transform: uppercase; }
    .kpi .v { font-size: 15px; font-weight: bold; margin-top: 2px; }
    h3 { font-size: 12px; margin: 14px 0 6px; color: #0f172a; }
    table.dados { width: 100%; border-collapse: collapse; }
    table.dados th { background: #1f2937; color: #fff; padding: 5px 7px; text-align: left; font-size: 9.5px; }
    table.dados td { padding: 4px 7px; border-bottom: 1px solid #e5e7eb; font-size: 9.5px; }
    table.dados .num { text-align: right; }
    .rodape { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: center; }
    .badge { display: inline-block; padding: 1px 6px; border-radius: 6px; font-size: 8.5px; font-weight: bold; background: #fef3c7; color: #92400e; }
    .badge.ok { background: #dcfce7; color: #166534; }
    .badge.divergente { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
    <div class="header">
        <div class="doc">
            <div class="titulo">FECHAMENTO MENSAL</div>
            <div>{{ $inicio->translatedFormat('F \d\e Y') }}</div>
        </div>
        <h1>{{ $granja?->nome ?? 'OvoGest' }}</h1>
        <div>{{ collect([$granja?->documento ? 'CNPJ '.$granja->documento : null, 'Período: '.$inicio->format('d/m/Y').' a '.$fim->format('d/m/Y')])->filter()->implode(' · ') }}</div>
    </div>

    <table class="kpis"><tr>
        <td class="kpi"><div class="r">Faturamento</div><div class="v">R$ {{ number_format($faturamento, 2, ',', '.') }}</div></td>
        <td class="kpi"><div class="r">Custo dos produtos</div><div class="v">R$ {{ number_format($custo, 2, ',', '.') }}</div></td>
        <td class="kpi"><div class="r">Margem</div><div class="v">R$ {{ number_format($margem, 2, ',', '.') }}</div></td>
        <td class="kpi"><div class="r">Recebido no mês</div><div class="v">R$ {{ number_format($recebidoNoMes, 2, ',', '.') }}</div></td>
        <td class="kpi"><div class="r">Em aberto (destas vendas)</div><div class="v">R$ {{ number_format($emAberto, 2, ',', '.') }}</div></td>
        <td class="kpi"><div class="r">Quebra</div><div class="v">{{ $quebra }} bandeja(s){{ $totalSaiu > 0 ? ' ('.round($quebra / $totalSaiu * 100, 1).'%)' : '' }}</div></td>
    </tr></table>

    <h3>Vendas do período ({{ $vendas->count() }})</h3>
    <table class="dados">
        <tr><th>Nº</th><th>Data</th><th>Estabelecimento</th><th>Vendedor</th><th>Carga</th><th>Pagto</th><th class="num">Total</th><th class="num">Recebido</th><th class="num">Em aberto</th></tr>
        @forelse ($vendas as $v)
            <tr>
                <td>#{{ $v->numero }}</td>
                <td>{{ $v->data_hora->format('d/m H:i') }}</td>
                <td>{{ $v->cliente->nome }}</td>
                <td>{{ $v->vendedor?->name ?? '—' }}</td>
                <td>#{{ $v->carga->numero }}</td>
                <td>{{ $v->forma_pagamento->getLabel() }}</td>
                <td class="num">R$ {{ number_format($v->valor_total, 2, ',', '.') }}</td>
                <td class="num">R$ {{ number_format($v->valor_recebido, 2, ',', '.') }}</td>
                <td class="num">R$ {{ number_format($v->valor_em_aberto, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Nenhuma venda no período.</td></tr>
        @endforelse
    </table>

    <h3>Conciliações do período ({{ $conciliacoes->count() }})</h3>
    <table class="dados">
        <tr><th>Carga</th><th>Rota</th><th class="num">Saiu</th><th class="num">Vendido</th><th class="num">Retornou</th><th class="num">Diferença</th><th>Status</th></tr>
        @forelse ($conciliacoes as $c)
            <tr>
                <td>#{{ $c->carga->numero }}</td>
                <td>{{ $c->carga->rota->nome }}</td>
                <td class="num">{{ $c->total_saiu }}</td>
                <td class="num">{{ $c->total_vendido }}</td>
                <td class="num">{{ $c->total_retornou }}</td>
                <td class="num">{{ $c->diferenca }}</td>
                <td><span class="badge {{ $c->status->value === 'ok' ? 'ok' : 'divergente' }}">{{ $c->status->getLabel() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="7">Nenhuma conciliação no período.</td></tr>
        @endforelse
    </table>

    @if ($canceladas->isNotEmpty())
        <h3>Vendas canceladas no período ({{ $canceladas->count() }}) — fora dos totais acima</h3>
        <table class="dados">
            <tr><th>Nº</th><th>Data</th><th>Estabelecimento</th><th>Motivo</th></tr>
            @foreach ($canceladas as $v)
                <tr>
                    <td>#{{ $v->numero }}</td>
                    <td>{{ $v->data_hora->format('d/m H:i') }}</td>
                    <td>{{ $v->cliente->nome }}</td>
                    <td>{{ $v->motivo_cancelamento }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="rodape">Fechamento mensal · emitido via OvoGest · AJR Software em {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
