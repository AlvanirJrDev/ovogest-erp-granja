<?php

namespace App\Http\Controllers;

use App\Models\CargaItem;
use App\Models\Cliente;
use App\Models\Conciliacao;
use App\Models\Recebimento;
use App\Models\RetornoItem;
use App\Models\Venda;
use App\Models\VendaItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RelatorioController extends Controller
{
    private function autorizar(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']), 403);
    }

    public function fechamento(int $ano, int $mes)
    {
        $this->autorizar();

        $inicio = Carbon::create($ano, $mes, 1)->startOfDay();
        $fim = $inicio->copy()->endOfMonth()->endOfDay();

        $vendas = Venda::ativas()
            ->whereBetween('data_hora', [$inicio, $fim])
            ->with(['cliente', 'vendedor', 'carga'])
            ->orderBy('numero')
            ->get();

        $faturamento = $vendas->sum(fn (Venda $v) => $v->valor_total);
        $emAberto = $vendas->sum(fn (Venda $v) => $v->valor_em_aberto);

        $custo = (float) VendaItem::query()
            ->join('produtos', 'produtos.id', '=', 'venda_itens.produto_id')
            ->whereHas('venda', fn ($q) => $q->whereNull('cancelada_em')->whereBetween('data_hora', [$inicio, $fim]))
            ->selectRaw('coalesce(sum(venda_itens.quantidade * produtos.custo_unitario), 0) as total')
            ->value('total');

        $recebidoNoMes = (float) Venda::ativas()->whereBetween('data_hora', [$inicio, $fim])->sum('valor_pago')
            + (float) Recebimento::whereBetween('data', [$inicio, $fim])
                ->whereHas('venda', fn ($q) => $q->whereNull('cancelada_em'))
                ->sum('valor');

        $totalSaiu = (int) CargaItem::whereHas('carga', fn ($q) => $q->whereBetween('data_hora_saida', [$inicio, $fim]))->sum('quantidade');
        $quebra = (int) RetornoItem::where('motivo', 'quebra')
            ->whereHas('retorno', fn ($q) => $q->whereBetween('data_hora_retorno', [$inicio, $fim]))
            ->sum('quantidade');

        $conciliacoes = Conciliacao::whereBetween('created_at', [$inicio, $fim])
            ->with('carga.rota')
            ->get();

        $canceladas = Venda::whereNotNull('cancelada_em')
            ->whereBetween('data_hora', [$inicio, $fim])
            ->with('cliente')
            ->get();

        return Pdf::loadView('pdf.fechamento', [
            'granja' => auth()->user()->granja,
            'inicio' => $inicio,
            'fim' => $fim,
            'vendas' => $vendas,
            'faturamento' => $faturamento,
            'custo' => $custo,
            'margem' => $faturamento - $custo,
            'recebidoNoMes' => $recebidoNoMes,
            'emAberto' => $emAberto,
            'totalSaiu' => $totalSaiu,
            'quebra' => $quebra,
            'conciliacoes' => $conciliacoes,
            'canceladas' => $canceladas,
        ])->setPaper('a4')->stream(sprintf('fechamento-%04d-%02d.pdf', $ano, $mes));
    }

    public function extrato(Cliente $cliente, Request $request)
    {
        $this->autorizar();

        $de = Carbon::parse($request->query('de', now()->startOfMonth()->toDateString()))->startOfDay();
        $ate = Carbon::parse($request->query('ate', now()->toDateString()))->endOfDay();

        $vendas = Venda::ativas()
            ->where('cliente_id', $cliente->id)
            ->whereBetween('data_hora', [$de, $ate])
            ->with(['itens.produto', 'recebimentos'])
            ->orderBy('data_hora')
            ->get();

        $saldoDevedorTotal = Venda::ativas()
            ->where('cliente_id', $cliente->id)
            ->get()
            ->sum(fn (Venda $v) => $v->valor_em_aberto);

        return Pdf::loadView('pdf.extrato-cliente', [
            'granja' => auth()->user()->granja,
            'cliente' => $cliente,
            'de' => $de,
            'ate' => $ate,
            'vendas' => $vendas,
            'saldoDevedorTotal' => $saldoDevedorTotal,
        ])->setPaper('a4')->stream('extrato-'.str($cliente->nome)->slug().'.pdf');
    }
}
