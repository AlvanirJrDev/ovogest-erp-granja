<?php

namespace App\Filament\Resources\VendaResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RecebimentosRelationManager extends RelationManager
{
    protected static string $relationship = 'recebimentos';

    protected static ?string $title = 'Histórico de recebimentos';

    public function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('data')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('valor')
                    ->money('BRL', locale: 'pt_BR'),
                Tables\Columns\TextColumn::make('forma')
                    ->badge(),
                Tables\Columns\TextColumn::make('recebedor.name')
                    ->label('Recebido por'),
                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação')
                    ->placeholder('—'),
            ])
            ->actions([
                // Recebimento errado é excluído (admin) e lançado de novo — nunca editado
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => auth()->user()->hasRole('admin')),
            ])
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
