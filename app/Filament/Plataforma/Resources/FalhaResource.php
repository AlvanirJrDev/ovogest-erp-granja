<?php

namespace App\Filament\Plataforma\Resources;

use App\Filament\Plataforma\Resources\FalhaResource\Pages;
use App\Models\Falha;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FalhaResource extends Resource
{
    protected static ?string $model = Falha::class;

    protected static ?string $slug = 'falhas';

    protected static ?string $navigationIcon = 'heroicon-o-bug-ant';

    protected static ?string $modelLabel = 'falha';

    protected static ?string $pluralModelLabel = 'falhas';

    public static function getNavigationBadge(): ?string
    {
        $n = Falha::where('ultima_em', '>=', now()->subDay())->count();

        return $n > 0 ? (string) $n : null;
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
                Tables\Columns\TextColumn::make('excecao')
                    ->label('Exceção')
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('mensagem')
                    ->limit(70)
                    ->tooltip(fn (Falha $r) => $r->mensagem)
                    ->searchable(),
                Tables\Columns\TextColumn::make('arquivo')
                    ->limit(45),
                Tables\Columns\TextColumn::make('ocorrencias')
                    ->label('Vezes')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('ultima_em')
                    ->label('Última vez')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('ultima_em', 'desc')
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Resolvida'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageFalhas::route('/'),
        ];
    }
}
