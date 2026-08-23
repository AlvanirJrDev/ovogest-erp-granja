<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditoriaResource\Pages;
use App\Models\Auditoria;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditoriaResource extends Resource
{
    protected static ?string $model = Auditoria::class;

    protected static ?string $slug = 'auditoria';

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'registro de auditoria';

    protected static ?string $pluralModelLabel = 'auditoria';

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
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
                    ->limit(60)
                    ->tooltip(fn (Auditoria $r) => $r->alteracoes
                        ? collect($r->alteracoes)->map(fn ($v, $c) => "{$c}: {$v['de']} → {$v['para']}")->implode("\n")
                        : null)
                    ->placeholder('—'),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('acao')
                    ->options(['criou' => 'Criou', 'atualizou' => 'Atualizou', 'excluiu' => 'Excluiu']),
                Tables\Filters\SelectFilter::make('modelo')
                    ->options(fn () => Auditoria::query()->distinct()->pluck('modelo', 'modelo')->all()),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListAuditoria::route('/')];
    }
}
