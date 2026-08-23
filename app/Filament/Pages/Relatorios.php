<?php

namespace App\Filament\Pages;

use App\Models\Cliente;
use App\Models\Venda;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Relatorios extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.relatorios';

    protected static ?string $title = 'Relatórios';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']) ?? false;
    }

    private function camposPeriodo(): array
    {
        return [
            Forms\Components\Select::make('mes')
                ->label('Mês')
                ->options([
                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
                ])
                ->default(now()->month)
                ->required(),
            Forms\Components\Select::make('ano')
                ->label('Ano')
                ->options(collect(range(now()->year, now()->year - 3))->mapWithKeys(fn ($a) => [$a => $a]))
                ->default(now()->year)
                ->required(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fechamento')
                ->label('Fechamento mensal (PDF)')
                ->icon('heroicon-o-document-text')
                ->form($this->camposPeriodo())
                ->action(fn (array $data) => redirect()->to(
                    route('relatorios.fechamento', ['ano' => $data['ano'], 'mes' => $data['mes']])
                )),
            Action::make('extrato')
                ->label('Extrato por cliente (PDF)')
                ->icon('heroicon-o-user')
                ->color('gray')
                ->form([
                    Forms\Components\Select::make('cliente_id')
                        ->label('Cliente')
                        ->options(Cliente::orderBy('nome')->pluck('nome', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('de')
                        ->default(now()->startOfMonth())
                        ->required(),
                    Forms\Components\DatePicker::make('ate')
                        ->label('Até')
                        ->default(today())
                        ->required(),
                ])
                ->action(fn (array $data) => redirect()->to(
                    route('relatorios.extrato', ['cliente' => $data['cliente_id'], 'de' => $data['de'], 'ate' => $data['ate']])
                )),
            Action::make('csv')
                ->label('Vendas do mês (CSV/Excel)')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->form($this->camposPeriodo())
                ->action(function (array $data) {
                    $inicio = Carbon::create($data['ano'], $data['mes'], 1)->startOfDay();
                    $fim = $inicio->copy()->endOfMonth()->endOfDay();

                    $vendas = Venda::ativas()
                        ->whereBetween('data_hora', [$inicio, $fim])
                        ->with(['cliente', 'vendedor', 'carga'])
                        ->orderBy('numero')
                        ->get();

                    return response()->streamDownload(function () use ($vendas) {
                        $saida = fopen('php://output', 'w');
                        fwrite($saida, "\xEF\xBB\xBF"); // BOM para acentos no Excel

                        fputcsv($saida, ['Nº', 'Data', 'Estabelecimento', 'Vendedor', 'Carga', 'Forma de pagamento', 'Total', 'Recebido', 'Em aberto', 'Situação', 'Vencimento'], ';');

                        foreach ($vendas as $v) {
                            fputcsv($saida, [
                                $v->numero,
                                $v->data_hora->format('d/m/Y H:i'),
                                $v->cliente->nome,
                                $v->vendedor?->name ?? '',
                                $v->carga->numero,
                                $v->forma_pagamento->getLabel(),
                                number_format($v->valor_total, 2, ',', ''),
                                number_format($v->valor_recebido, 2, ',', ''),
                                number_format($v->valor_em_aberto, 2, ',', ''),
                                $v->status_pagamento->getLabel(),
                                $v->data_vencimento?->format('d/m/Y') ?? '',
                            ], ';');
                        }

                        fclose($saida);
                    }, sprintf('vendas-%04d-%02d.csv', $data['ano'], $data['mes']), ['Content-Type' => 'text/csv; charset=UTF-8']);
                }),
        ];
    }
}
