<?php

namespace App\Filament\Plataforma\Resources;

use App\Filament\Plataforma\Resources\AuditoriaGeralResource\Pages;
use App\Models\Auditoria;
use App\Models\Granja;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Visão da plataforma: auditoria de TODAS as granjas, filtrável por granja. */
class AuditoriaGeralResource extends Resource
{
    protected static ?string $model = Auditoria::class;

    protected static ?string $slug = 'auditoria';

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $modelLabel = 'registro de auditoria';

    protected static ?string $pluralModelLabel = 'auditoria das granjas';

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('granja.nome')
                    ->label('Granja')
                    ->placeholder('Plataforma')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Quem')
                    ->placeholder('Sistema'),
                Tables\Columns\TextColumn::make('acao')
                    ->label('Ação')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'criou' => 'success', 'atualizou' => 'warning', 'excluiu' => 'danger', default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('modelo')
                    ->label('O quê'),
                Tables\Columns\TextColumn::make('registro_rotulo')
                    ->label('Registro')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('alteracoes')
                    ->label('Alterações')
                    ->state(fn (Auditoria $r) => $r->alteracoes
                        ? collect($r->alteracoes)->map(fn ($v, $c) => "{$c}: {$v['de']} → {$v['para']}")->implode(' · ')
                        : null)
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('granja_id')
                    ->label('Granja')
                    ->options(Granja::pluck('nome', 'id')),
                Tables\Filters\SelectFilter::make('acao')
                    ->options(['criou' => 'Criou', 'atualizou' => 'Atualizou', 'excluiu' => 'Excluiu']),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditoriaGeral::route('/')];
    }
}
