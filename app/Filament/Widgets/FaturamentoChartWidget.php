<?php

namespace App\Filament\Widgets;

use App\Models\VendaItem;
use Filament\Widgets\ChartWidget;

class FaturamentoChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Faturamento por semana (últimas 8 semanas)';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'dono', 'financeiro']) ?? false;
    }

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $labels = [];
        $valores = [];

        for ($i = 7; $i >= 0; $i--) {
            $inicio = now()->subWeeks($i)->startOfWeek();
            $fim = now()->subWeeks($i)->endOfWeek();

            $labels[] = $inicio->format('d/m');

            $valores[] = (float) VendaItem::query()
                ->whereHas('venda', fn ($query) => $query->whereNull('cancelada_em')->whereBetween('data_hora', [$inicio, $fim]))
                ->selectRaw('coalesce(sum(quantidade * valor_unitario), 0) as total')
                ->value('total');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $valores,
                    'fill' => true,
                    'tension' => 0.35,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.12)',
                    'pointBackgroundColor' => '#f59e0b',
                    'pointRadius' => 3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
