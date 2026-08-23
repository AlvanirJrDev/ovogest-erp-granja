<?php

namespace App\Filament\Resources;

use App\Enums\FormaPagamento;
use App\Enums\StatusCarga;
use App\Filament\Resources\VendaResource\Pages;
use App\Mail\NotaVendaMail;
use App\Models\CargaCaminhao;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Recebimento;
use App\Models\Venda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class VendaResource extends Resource
{
    protected static ?string $model = Venda::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Operação';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'venda';

    protected static ?string $pluralModelLabel = 'vendas';

    /** Vendedor enxerga apenas as próprias vendas. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(
                auth()->user()?->hasRole('vendedor'),
                fn (Builder $query) => $query->where('vendedor_id', auth()->id()),
            );
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da venda')
                    ->schema([
                        Forms\Components\TextInput::make('numero')
                            ->label('Nº da nota de venda')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Gerado automaticamente'),
                        Forms\Components\Select::make('carga_caminhao_id')
                            ->label('Carga (em rota)')
                            ->options(
                                fn () => CargaCaminhao::where('status', StatusCarga::Fechada)
                                    ->when(
                                        auth()->user()?->hasRole('vendedor'),
                                        fn ($query) => $query->whereHas(
                                            'rota',
                                            fn ($q) => $q->where('responsavel_id', auth()->id()),
                                        ),
                                    )
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
                        Forms\Components\DateTimePicker::make('data_hora')
                            ->label('Data/hora')
                            ->default(now())
                            ->required(),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Estabelecimento (cliente)')
                            ->options(function (Forms\Get $get) {
                                $carga = CargaCaminhao::with('rota.clientes')->find($get('carga_caminhao_id'));

                                // Prioriza os clientes vinculados à rota da carga;
                                // se a rota não tiver clientes cadastrados, mostra todos.
                                if ($carga !== null && $carga->rota->clientes->isNotEmpty()) {
                                    return $carga->rota->clientes->pluck('nome', 'id');
                                }

                                return Cliente::orderBy('nome')->pluck('nome', 'id');
                            })
                            ->helperText('Busque um estabelecimento da rota ou cadastre um novo no botão +.')
                            ->required()
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('documento')
                                    ->label('CPF/CNPJ')
                                    ->mask(RawJs::make(<<<'JS'
                                        $input.length > 14 ? '99.999.999/9999-99' : '999.999.999-99'
                                    JS))
                                    ->placeholder('000.000.000-00 ou 00.000.000/0000-00')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('email')
                                    ->label('E-mail (recebe a nota em PDF)')
                                    ->placeholder('contato@estabelecimento.com.br')
                                    ->email(),
                                Forms\Components\TextInput::make('telefone')
                                    ->tel()
                                    ->mask(RawJs::make(<<<'JS'
                                        $input.length >= 15 ? '(99) 99999-9999' : '(99) 9999-9999'
                                    JS))
                                    ->placeholder('(00) 00000-0000')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('endereco')
                                    ->label('Endereço')
                                    ->maxLength(255),
                            ])
                            ->createOptionModalHeading('Cadastrar novo estabelecimento')
                            ->createOptionUsing(function (array $data, Forms\Get $get): int {
                                $cliente = Cliente::create($data);

                                // Vincula o novo estabelecimento à rota da carga selecionada
                                CargaCaminhao::find($get('carga_caminhao_id'))
                                    ?->rota->clientes()
                                    ->syncWithoutDetaching($cliente->getKey());

                                return $cliente->getKey();
                            }),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Itens vendidos')
                    ->schema([
                        Forms\Components\Repeater::make('itens')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('produto_id')
                                    ->label('Produto')
                                    ->options(function (Forms\Get $get) {
                                        $carga = CargaCaminhao::find($get('../../carga_caminhao_id'));

                                        if ($carga === null) {
                                            return [];
                                        }

                                        return Produto::whereIn(
                                            'id',
                                            $carga->itens()->pluck('produto_id'),
                                        )->get()->mapWithKeys(fn (Produto $p) => [$p->id => $p->nome_completo]);
                                    })
                                    ->required()
                                    ->distinct()
                                    ->live()
                                    ->afterStateUpdated(
                                        fn ($state, Forms\Set $set) => $set(
                                            'valor_unitario',
                                            Produto::find($state)?->preco_venda,
                                        )
                                    )
                                    ->helperText(function (Forms\Get $get, $state) {
                                        $carga = CargaCaminhao::find($get('../../carga_caminhao_id'));

                                        if ($carga === null || $state === null) {
                                            return null;
                                        }

                                        return 'Saldo disponível na carga: '
                                            .$carga->saldoDisponivel((int) $state).' bandeja(s)';
                                    }),
                                Forms\Components\TextInput::make('quantidade')
                                    ->label('Quantidade (bandejas)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->minValue(1),
                                Forms\Components\TextInput::make('valor_unitario')
                                    ->label('Valor unitário')
                                    ->helperText('Preço registrado na venda fica imutável no histórico.')
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
                Forms\Components\Section::make('Pagamento')
                    ->schema([
                        Forms\Components\Select::make('forma_pagamento')
                            ->label('Forma de pagamento')
                            ->options(FormaPagamento::class)
                            ->required()
                            ->live(),
                        Forms\Components\TextInput::make('valor_pago')
                            ->label('Valor pago no ato')
                            ->helperText('O restante fica registrado como valor em aberto.')
                            ->numeric()
                            ->prefix('R$')
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\DatePicker::make('data_vencimento')
                            ->label('Vencimento do valor em aberto')
                            ->visible(
                                fn (Forms\Get $get) => $get('forma_pagamento') === FormaPagamento::Prazo->value
                                    || $get('forma_pagamento') === FormaPagamento::Prazo
                            ),
                    ])
                    ->columns(3),
            ]);
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
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->label('Estabelecimento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('vendedor.name')
                    ->label('Vendedor'),
                Tables\Columns\TextColumn::make('data_hora')
                    ->label('Data/hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('forma_pagamento')
                    ->label('Pagamento')
                    ->badge(),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Total')
                    ->money('BRL', locale: 'pt_BR'),
                Tables\Columns\TextColumn::make('valor_recebido')
                    ->label('Recebido')
                    ->money('BRL', locale: 'pt_BR'),
                Tables\Columns\TextColumn::make('valor_em_aberto')
                    ->label('Em aberto')
                    ->money('BRL', locale: 'pt_BR'),
                Tables\Columns\TextColumn::make('status_pagamento')
                    ->label('Situação')
                    ->badge()
                    ->tooltip(fn (Venda $record) => $record->motivo_cancelamento),
            ])
            ->defaultSort('numero', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('forma_pagamento')
                    ->label('Forma de pagamento')
                    ->options(FormaPagamento::class),
                Tables\Filters\SelectFilter::make('cliente_id')
                    ->label('Estabelecimento')
                    ->relationship('cliente', 'nome')
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('vendedor_id')
                    ->label('Vendedor')
                    ->relationship('vendedor', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('receber')
                    ->label('Receber')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->visible(
                        fn (Venda $record) => $record->cancelada_em === null
                            && $record->valor_em_aberto > 0
                            && auth()->user()->hasAnyRole(['admin', 'financeiro'])
                    )
                    ->modalHeading(fn (Venda $record) => "Registrar recebimento — venda #{$record->numero}")
                    ->form([
                        Forms\Components\TextInput::make('valor')
                            ->label('Valor recebido')
                            ->helperText(fn (Venda $record) => 'Em aberto: R$ '.number_format($record->valor_em_aberto, 2, ',', '.'))
                            ->numeric()
                            ->prefix('R$')
                            ->required()
                            ->minValue(0.01),
                        Forms\Components\Select::make('forma')
                            ->options(['dinheiro' => 'Dinheiro', 'pix' => 'Pix', 'outro' => 'Outro'])
                            ->required(),
                        Forms\Components\DatePicker::make('data')
                            ->default(today())
                            ->required(),
                        Forms\Components\TextInput::make('observacao')
                            ->label('Observação')
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Venda $record) {
                        Recebimento::create($data + ['venda_id' => $record->id]);

                        Notification::make()
                            ->title('Recebimento registrado')
                            ->body(sprintf(
                                'R$ %s recebidos. Em aberto agora: R$ %s.',
                                number_format((float) $data['valor'], 2, ',', '.'),
                                number_format($record->fresh()->valor_em_aberto, 2, ',', '.'),
                            ))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('cancelar')
                    ->label('Cancelar venda')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(
                        fn (Venda $record) => $record->cancelada_em === null
                            && auth()->user()->hasRole('admin')
                    )
                    ->requiresConfirmation()
                    ->modalHeading(fn (Venda $record) => "Cancelar venda #{$record->numero}")
                    ->modalDescription('A venda permanece no histórico com autor, data e motivo, mas sai dos saldos e relatórios. Só é possível antes da conciliação da carga.')
                    ->form([
                        Forms\Components\Textarea::make('motivo')
                            ->label('Motivo do cancelamento')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (array $data, Venda $record) {
                        try {
                            $record->cancelar($data['motivo']);

                            Notification::make()
                                ->title("Venda #{$record->numero} cancelada")
                                ->body('O saldo voltou para o caminhão e a venda saiu dos relatórios.')
                                ->success()
                                ->send();
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Não foi possível cancelar')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('pdf')
                    ->label('Nota PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->visible(fn (Venda $record) => $record->cancelada_em === null)
                    ->url(fn (Venda $record) => route('vendas.pdf', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('email')
                    ->label('Enviar por e-mail')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->visible(fn (Venda $record) => filled($record->cliente->email) && $record->cancelada_em === null)
                    ->requiresConfirmation()
                    ->modalHeading('Enviar nota por e-mail')
                    ->modalDescription(fn (Venda $record) => "A nota #{$record->numero} em PDF será enviada para {$record->cliente->email}.")
                    ->action(function (Venda $record) {
                        Mail::to($record->cliente->email)->queue(new NotaVendaMail($record));

                        Notification::make()
                            ->title('Nota enviada para a fila de e-mails')
                            ->body("Destinatário: {$record->cliente->email}")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->label('Ver'),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Venda $record) => $record->cancelada_em === null),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            VendaResource\RelationManagers\RecebimentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendas::route('/'),
            'create' => Pages\CreateVenda::route('/create'),
            'edit' => Pages\EditVenda::route('/{record}/edit'),
        ];
    }
}
