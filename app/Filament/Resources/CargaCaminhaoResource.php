<?php

namespace App\Filament\Resources;

use App\Enums\StatusCarga;
use App\Filament\Resources\CargaCaminhaoResource\Pages;
use App\Models\CargaCaminhao;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class CargaCaminhaoResource extends Resource
{
    protected static ?string $model = CargaCaminhao::class;

    protected static ?string $slug = 'cargas';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'carga';

    protected static ?string $pluralModelLabel = 'cargas (notas de saída)';

    protected static ?string $navigationLabel = 'Cargas (saída)';

    /** Badge: cargas ainda em carregamento. */
    public static function getNavigationBadge(): ?string
    {
        $abertas = static::getEloquentQuery()->where('status', StatusCarga::Aberta)->count();

        return $abertas > 0 ? (string) $abertas : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /** Vendedor enxerga apenas as cargas das rotas em que é o responsável. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(
                auth()->user()?->hasRole('vendedor'),
                fn (Builder $query) => $query->whereHas(
                    'rota',
                    fn (Builder $q) => $q->where('responsavel_id', auth()->id()),
                ),
            );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da carga')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Nº da nota de saída')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerado automaticamente'),
                        Forms\Components\Select::make('rota_id')
                            ->label('Rota')
                            ->relationship(
                                'rota',
                                'nome',
                                fn ($query) => $query->whereIn('status', ['planejada', 'em_andamento']),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => "{$record->nome} — {$record->data->format('d/m/Y')} ({$record->veiculo->placa})"
                            )
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\DateTimePicker::make('data_hora_saida')
                            ->label('Data/hora de saída')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Itens carregados')
                    ->schema([
                        Forms\Components\Repeater::make('itens')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('produto_id')
                                    ->label('Produto')
                                    ->options(
                                        fn () => Produto::where('ativo', true)
                                            ->get()
                                            ->mapWithKeys(fn (Produto $p) => [$p->id => $p->nome_completo])
                                    )
                                    ->required()
                                    ->distinct()
                                    ->live()
                                    ->afterStateUpdated(
                                        fn ($state, Forms\Set $set) => $set(
                                            'valor_unitario',
                                            Produto::find($state)?->preco_venda,
                                        )
                                    )
                                    ->helperText(function ($state) {
                                        if ($state === null) {
                                            return null;
                                        }

                                        $estoque = Produto::find($state)?->estoque_atual ?? 0;

                                        return 'Em estoque: '.$estoque.' bandeja(s)';
                                    }),
                                Forms\Components\TextInput::make('quantidade')
                                    ->label('Quantidade (bandejas)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('valor_unitario')
                                    ->label('Valor unitário')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required()
                                    ->minValue(0),
                            ])
                            ->columns(3)
                            ->addActionLabel('Adicionar produto')
                            ->defaultItems(1)
                            ->minItems(1),
                    ]),
            ])
            ->disabled(
                fn (?CargaCaminhao $record) => $record !== null && $record->status !== StatusCarga::Aberta
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->prefix('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('rota.nome')
                    ->label('Rota')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rota.veiculo.placa')
                    ->label('Veículo'),
                Tables\Columns\TextColumn::make('data_hora_saida')
                    ->label('Saída')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('itens_sum_quantidade')
                    ->label('Bandejas')
                    ->sum('itens', 'quantidade'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('numero', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusCarga::class),
            ])
            ->actions([
                Tables\Actions\Action::make('pdf')
                    ->label('Gerar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (CargaCaminhao $record) => route('cargas.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('fechar')
                    ->label('Fechar carga')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Fechar carga')
                    ->modalDescription('Após o fechamento os itens não poderão mais ser alterados e a carga estará liberada para vendas. Confirmar?')
                    ->visible(
                        fn (CargaCaminhao $record) => $record->status === StatusCarga::Aberta
                            && auth()->user()->hasRole('admin')
                    )
                    ->action(function (CargaCaminhao $record) {
                        try {
                            $record->fechar();

                            Notification::make()
                                ->title("Carga #{$record->numero} fechada")
                                ->body('A nota de saída agora é imutável. Vendas liberadas para esta carga.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Não foi possível fechar a carga')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->visible(fn (CargaCaminhao $record) => $record->status === StatusCarga::Aberta),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (CargaCaminhao $record) => $record->status === StatusCarga::Aberta),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCargas::route('/'),
            'create' => Pages\CreateCarga::route('/create'),
            'edit' => Pages\EditCarga::route('/{record}/edit'),
        ];
    }
}
