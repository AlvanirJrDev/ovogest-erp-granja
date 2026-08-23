<?php

namespace App\Filament\Resources;

use App\Enums\StatusConciliacao;
use App\Filament\Resources\ConciliacaoResource\Pages;
use App\Models\Conciliacao;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConciliacaoResource extends Resource
{
    protected static ?string $model = Conciliacao::class;

    protected static ?string $slug = 'conciliacoes';

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'conciliação';

    protected static ?string $pluralModelLabel = 'conciliações';

    /** Badge: conciliações divergentes que precisam de atenção. */
    public static function getNavigationBadge(): ?string
    {
        $divergentes = static::getEloquentQuery()
            ->where('status', StatusConciliacao::Divergente)
            ->count();

        return $divergentes > 0 ? (string) $divergentes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('carga.numero')
                    ->label('Carga')
                    ->prefix('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('carga.rota.nome')
                    ->label('Rota'),
                Tables\Columns\TextColumn::make('total_saiu')
                    ->label('Saiu')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_vendido')
                    ->label('Vendido')
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_retornou')
                    ->label('Retornou')
                    ->numeric(),
                Tables\Columns\TextColumn::make('diferenca')
                    ->label('Diferença')
                    ->numeric()
                    ->color(fn (int $state) => $state === 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Calculada em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusConciliacao::class),
                Tables\Filters\Filter::make('periodo')
                    ->form([
                        Forms\Components\DatePicker::make('de')
                            ->label('De'),
                        Forms\Components\DatePicker::make('ate')
                            ->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['de'], fn (Builder $q, $de) => $q->whereDate('created_at', '>=', $de))
                            ->when($data['ate'], fn (Builder $q, $ate) => $q->whereDate('created_at', '<=', $ate));
                    }),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConciliacoes::route('/'),
        ];
    }
}
