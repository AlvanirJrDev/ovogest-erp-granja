<?php

namespace App\Filament\Resources;

use App\Enums\MotivoRetorno;
use App\Enums\StatusCarga;
use App\Enums\StatusRetorno;
use App\Filament\Resources\RetornoCaminhaoResource\Pages;
use App\Models\CargaCaminhao;
use App\Models\Produto;
use App\Models\RetornoCaminhao;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class RetornoCaminhaoResource extends Resource
{
    protected static ?string $model = RetornoCaminhao::class;

    protected static ?string $slug = 'retornos';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'retorno';

    protected static ?string $pluralModelLabel = 'retornos (notas de entrada)';

    protected static ?string $navigationLabel = 'Retornos (entrada)';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados do retorno')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Nº da nota de entrada')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerado automaticamente'),
                        Forms\Components\Select::make('carga_caminhao_id')
                            ->label('Carga (em rota)')
                            ->options(
                                fn () => CargaCaminhao::where('status', StatusCarga::Fechada)
                                    ->whereDoesntHave('retorno')
                                    ->with('rota')
                                    ->orderByDesc('numero')
                                    ->get()
                                    ->mapWithKeys(fn (CargaCaminhao $carga) => [
                                        $carga->id => "Carga #{$carga->numero} — {$carga->rota->nome}",
                                    ])
                            )
                            ->required()
                            ->live()
                            ->disabledOn('edit'),
                        Forms\Components\DateTimePicker::make('data_hora_retorno')
                            ->label('Data/hora do retorno')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Itens retornados')
                    ->description('Registre o que voltou no caminhão, separado por motivo: sobra, quebra ou devolução de cliente.')
                    ->schema([
                        Forms\Components\Repeater::make('itens')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('produto_id')
                                    ->label('Produto')
                                    ->options(function (Forms\Get $get) {
                                        $cargaId = $get('../../carga_caminhao_id');
                                        $carga = CargaCaminhao::find($cargaId);

                                        if ($carga === null) {
                                            return [];
                                        }

                                        return Produto::whereIn(
                                            'id',
                                            $carga->itens()->pluck('produto_id'),
                                        )->get()->mapWithKeys(fn (Produto $p) => [$p->id => $p->nome_completo]);
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('quantidade')
                                    ->label('Quantidade (bandejas)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(1),
                                Forms\Components\Select::make('motivo')
                                    ->options(MotivoRetorno::class)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->addActionLabel('Adicionar item')
                            ->defaultItems(1),
                    ]),
            ])
            ->disabled(
                fn (?RetornoCaminhao $record) => $record !== null && $record->status === StatusRetorno::Fechado
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
                Tables\Columns\TextColumn::make('carga.numero')
                    ->label('Carga')
                    ->prefix('#'),
                Tables\Columns\TextColumn::make('carga.rota.nome')
                    ->label('Rota'),
                Tables\Columns\TextColumn::make('data_hora_retorno')
                    ->label('Retorno')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('itens_sum_quantidade')
                    ->label('Bandejas retornadas')
                    ->sum('itens', 'quantidade'),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
            ])
            ->defaultSort('numero', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusRetorno::class),
            ])
            ->actions([
                Tables\Actions\Action::make('fechar')
                    ->label('Fechar retorno')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Fechar retorno')
                    ->modalDescription('Ao fechar, a nota de entrada fica imutável e a conciliação da carga é calculada automaticamente. Confirmar?')
                    ->visible(fn (RetornoCaminhao $record) => $record->status === StatusRetorno::Aberto)
                    ->action(function (RetornoCaminhao $record) {
                        try {
                            $record->fechar();

                            $conciliacao = $record->carga->conciliacao;

                            Notification::make()
                                ->title("Retorno #{$record->numero} fechado")
                                ->body(sprintf(
                                    'Conciliação: saiu %d, vendido %d, retornou %d — %s.',
                                    $conciliacao->total_saiu,
                                    $conciliacao->total_vendido,
                                    $conciliacao->total_retornou,
                                    $conciliacao->status->getLabel(),
                                ))
                                ->status($conciliacao->status->value === 'ok' ? 'success' : 'danger')
                                ->persistent()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Não foi possível fechar o retorno')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => ! auth()->user()->can('update', $record)),
                Tables\Actions\EditAction::make()
                    ->label(fn (RetornoCaminhao $record) => $record->status === StatusRetorno::Aberto ? 'Editar' : 'Ver'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (RetornoCaminhao $record) => $record->status === StatusRetorno::Aberto),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetornos::route('/'),
            'create' => Pages\CreateRetorno::route('/create'),
            'edit' => Pages\EditRetorno::route('/{record}/edit'),
        ];
    }
}
