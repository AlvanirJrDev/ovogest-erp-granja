<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Nota de Saída Nº {{ str_pad($carga->numero, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        * { font-family: "DejaVu Sans", sans-serif; color: #1f2937; }
        body { font-size: 12px; margin: 0; }
        .header { border-bottom: 3px solid #d97706; padding-bottom: 12px; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header .doc { float: right; text-align: right; }
        .header .doc .numero { font-size: 18px; font-weight: bold; color: #d97706; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .info td { padding: 4px 8px; vertical-align: top; }
        .info .label { color: #6b7280; font-size: 10px; text-transform: uppercase; }
        .info .valor { font-size: 13px; font-weight: bold; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 10px; font-weight: bold; background: #fef3c7; color: #92400e; }
        table.itens { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.itens th { background: #1f2937; color: #ffffff; padding: 6px 8px; text-align: left; font-size: 11px; }
        table.itens td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        table.itens .num { text-align: right; }
        table.itens tfoot td { font-weight: bold; border-top: 2px solid #1f2937; border-bottom: none; font-size: 13px; }
        .clientes { margin: 12px 0 20px; }
        .clientes h3 { font-size: 12px; margin: 0 0 6px; text-transform: uppercase; color: #6b7280; }
        .clientes ol { margin: 0; padding-left: 20px; }
        .clientes li { padding: 2px 0; }
        .assinaturas { width: 100%; margin-top: 60px; }
        .assinaturas td { width: 50%; text-align: center; padding: 0 24px; }
        .assinaturas .linha { border-top: 1px solid #1f2937; padding-top: 6px; font-size: 11px; }
        .rodape { margin-top: 30px; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    @php
        $granja = $carga->granja;
        $logo = $granja?->logo_path ? storage_path('app/public/'.$granja->logo_path) : null;
    @endphp
    <div class="header">
        <div class="doc">
            <div>NOTA DE SAÍDA</div>
            <div class="numero">Nº {{ str_pad($carga->numero, 6, '0', STR_PAD_LEFT) }}</div>
        </div>
        @if ($logo && file_exists($logo))
            <img src="{{ $logo }}" style="height: 42px; margin-bottom: 4px;">
        @endif
        <h1>{{ $granja?->nome ?? 'OvoGest' }}</h1>
        <div>
            @if ($granja)
                {{ collect([$granja->documento ? 'CNPJ '.$granja->documento : null, $granja->telefone, $granja->email, $granja->endereco])->filter()->implode(' · ') }}
            @else
                Gestão de granja e distribuição — controle de carregamento
            @endif
        </div>
    </div>

    <table class="info">
        <tr>
            <td>
                <div class="label">Data/hora de saída</div>
                <div class="valor">{{ $carga->data_hora_saida->format('d/m/Y H:i') }}</div>
            </td>
            <td>
                <div class="label">Rota</div>
                <div class="valor">{{ $carga->rota->nome }} — {{ $carga->rota->data->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="label">Status</div>
                <div class="valor"><span class="badge">{{ $carga->status->getLabel() }}</span></div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Veículo</div>
                <div class="valor">{{ $carga->rota->veiculo->placa }} — {{ $carga->rota->veiculo->modelo }}</div>
            </td>
            <td>
                <div class="label">Vendedor responsável</div>
                <div class="valor">{{ $carga->rota->responsavel->name }}</div>
            </td>
            <td>
                <div class="label">Emitido em</div>
                <div class="valor">{{ now()->format('d/m/Y H:i') }}</div>
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
            @foreach ($carga->itens as $item)
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
                <td class="num">{{ $carga->itens->sum('quantidade') }} bandeja(s)</td>
                <td></td>
                <td class="num">R$ {{ number_format($carga->itens->sum(fn ($i) => $i->quantidade * $i->valor_unitario), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if ($carga->rota->clientes->isNotEmpty())
        <div class="clientes">
            <h3>Clientes previstos na rota</h3>
            <ol>
                @foreach ($carga->rota->clientes as $cliente)
                    <li>
                        {{ $cliente->nome }}
                        @if ($cliente->endereco) — {{ $cliente->endereco }} @endif
                        @if ($cliente->telefone) ({{ $cliente->telefone }}) @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    <table class="assinaturas">
        <tr>
            <td><div class="linha">Vendedor responsável<br>{{ $carga->rota->responsavel->name }}</div></td>
            <td><div class="linha">Conferente / Expedição</div></td>
        </tr>
    </table>

    <div class="rodape">
        Documento de controle interno — Nota de Saída Nº {{ str_pad($carga->numero, 6, '0', STR_PAD_LEFT) }} · emitido via OvoGest · AJR Software em {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
