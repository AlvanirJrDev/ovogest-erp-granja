<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Nota de Venda Nº {{ str_pad($venda->numero, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @font-face {
            font-family: 'assinatura';
            src: url('{{ storage_path('fonts/GreatVibes-Regular.ttf') }}') format('truetype');
        }
        * { font-family: "DejaVu Sans", sans-serif; color: #1f2937; }
        body { font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #d97706; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header h1 .g { color: #d97706; }
        .header .doc { float: right; text-align: right; }
        .header .doc .numero { font-size: 18px; font-weight: bold; color: #d97706; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { padding: 4px 8px; vertical-align: top; }
        .info .label { color: #6b7280; font-size: 10px; text-transform: uppercase; }
        .info .valor { font-size: 13px; font-weight: bold; }
        table.itens { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.itens th { background: #1f2937; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.itens td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.itens .num { text-align: right; }
        table.itens tfoot td { font-weight: bold; border-top: 2px solid #1f2937; border-bottom: none; font-size: 13px; }
        .pagamento { width: 45%; margin-left: 55%; border-collapse: collapse; margin-top: 12px; }
        .pagamento td { padding: 5px 8px; font-size: 12px; }
        .pagamento .rotulo { color: #6b7280; }
        .pagamento .num { text-align: right; font-weight: bold; }
        .pagamento .destaque td { border-top: 2px solid #1f2937; font-size: 14px; }
        .aberto { color: #dc2626; }
        .quitado { color: #16a34a; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: bold; background: #fef3c7; color: #92400e; }
        .rodape { margin-top: 40px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    @php
        $granja = $venda->granja;
        $logo = $granja?->logo_path ? storage_path('app/public/'.$granja->logo_path) : null;
    @endphp
    <div class="header">
        <div class="doc">
            <div>NOTA DE VENDA</div>
            <div class="numero">Nº {{ str_pad($venda->numero, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
        @if ($logo && file_exists($logo))
            <img src="{{ $logo }}" style="height: 42px; margin-bottom: 4px;">
        @endif
        <h1>{{ $granja?->nome ?? 'OvoGest' }}</h1>
        <div>
            @if ($granja)
                {{ collect([$granja->documento ? 'CNPJ '.$granja->documento : null, $granja->telefone, $granja->email, $granja->endereco])->filter()->implode(' · ') }}
            @else
                Gestão de granja e distribuição
            @endif
        </div>
    </div>

    <table class="info">
        <tr>
            <td style="width:34%;">
                <div class="label">Estabelecimento</div>
                <div class="valor">{{ $venda->cliente->nome }}</div>
                @if ($venda->cliente->documento) <div>{{ $venda->cliente->documento }}</div> @endif
                @if ($venda->cliente->endereco) <div>{{ $venda->cliente->endereco }}</div> @endif
            </td>
            <td style="width:33%;">
                <div class="label">Data/hora da venda</div>
                <div class="valor">{{ $venda->data_hora->format('d/m/Y H:i') }}</div>
                <div class="label" style="margin-top:6px;">Rota / Carga</div>
                <div>{{ $venda->carga->rota->nome }} — Carga #{{ $venda->carga->numero }}</div>
            </td>
            <td style="width:33%;">
                <div class="label">Vendedor</div>
                <div class="valor">{{ $venda->vendedor?->name ?? '—' }}</div>
                <div class="label" style="margin-top:6px;">Forma de pagamento</div>
                <div><span class="badge">{{ $venda->forma_pagamento->getLabel() }}</span></div>
            </td>
        </tr>
    </table>

    <table class="itens">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Tipo de bandeja</th>
                <th class="num">Quantidade</th>
                <th class="num">Valor unitário</th>
                <th class="num">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($venda->itens as $item)
                <tr>
                    <td>{{ $item->produto->nome }}</td>
                    <td>{{ $item->produto->tipo_bandeja->getLabel() }}</td>
                    <td class="num">{{ $item->quantidade }}</td>
                    <td class="num">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                    <td class="num">R$ {{ number_format($item->quantidade * $item->valor_unitario, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">TOTAL</td>
                <td class="num">{{ $venda->itens->sum('quantidade') }} bandeja(s)</td>
                <td></td>
                <td class="num">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="pagamento">
        <tr>
            <td class="rotulo">Total da venda</td>
            <td class="num">R$ {{ number_format($venda->valor_total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="rotulo">Valor pago</td>
            <td class="num">R$ {{ number_format($venda->valor_pago, 2, ',', '.') }}</td>
        </tr>
        @if ($venda->data_vencimento)
            <tr>
                <td class="rotulo">Vencimento</td>
                <td class="num">{{ $venda->data_vencimento->format('d/m/Y') }}</td>
            </tr>
        @endif
        <tr class="destaque">
            <td class="rotulo">Valor em aberto</td>
            <td class="num {{ $venda->valor_em_aberto > 0 ? 'aberto' : 'quitado' }}">
                R$ {{ number_format($venda->valor_em_aberto, 2, ',', '.') }}
            </td>
        </tr>
    </table>

    <table style="width:100%; margin-top:34px; border-collapse:collapse;">
        <tr>
            <td style="width:50%; padding:0 28px; text-align:center; vertical-align:bottom;">
                <div style="height:36px;"></div>
                <div style="border-top:1px solid #1f2937; padding-top:6px; font-size:10.5px; color:#374151; height:26px;">Recebido por — assinatura do estabelecimento</div>
            </td>
            <td style="width:50%; padding:0 28px; text-align:center; vertical-align:bottom;">
                <div style="height:36px; line-height:36px; font-family:'assinatura'; font-size:30px; color:#1f2937;">{{ $venda->vendedor?->name ?? '' }}</div>
                <div style="border-top:1px solid #1f2937; padding-top:6px; font-size:10.5px; color:#374151; height:26px;">Vendedor — assinatura eletrônica OvoGest · {{ $venda->created_at->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="rodape">
        Documento de controle interno — não substitui documento fiscal · Nota de Venda Nº {{ str_pad($venda->numero, 6, '0', STR_PAD_LEFT) }} · emitida via OvoGest · AJR Software em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
