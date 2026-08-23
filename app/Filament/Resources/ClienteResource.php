<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'cliente';

    protected static ?string $pluralModelLabel = 'clientes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->label('E-mail')
                    ->helperText('Recebe automaticamente a nota de venda em PDF.')
                    ->email()
                    ->placeholder('contato@estabelecimento.com.br')
                    ->maxLength(255),
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('documento')
                    ->label('CPF/CNPJ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefone'),
                Tables\Columns\TextColumn::make('endereco')
                    ->label('Endereço')
                    ->limit(40),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn ($record) => ! auth()->user()->can('update', $record)),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClientes::route('/'),
        ];
    }
}
