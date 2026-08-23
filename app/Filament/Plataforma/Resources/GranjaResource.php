<?php

namespace App\Filament\Plataforma\Resources;

use App\Filament\Plataforma\Resources\GranjaResource\Pages;
use App\Models\Granja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;

class GranjaResource extends Resource
{
    protected static ?string $model = Granja::class;

    protected static ?string $slug = 'granjas';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $modelLabel = 'granja';

    protected static ?string $pluralModelLabel = 'granjas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da granja')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome fantasia')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label('Endereço no sistema (URL)')
                            ->helperText('Gerado a partir do nome se ficar vazio. Ex.: granja-sao-jose → /app/granja-sao-jose')
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(60),
                        Forms\Components\TextInput::make('razao_social')
                            ->label('Razão social')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('documento')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->placeholder('00.000.000/0000-00')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail de contato')
                            ->placeholder('contato@granja.com.br')
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
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->helperText('Aparece no painel e nas notas em PDF da granja.')
                            ->image()
                            ->disk('public')
                            ->directory('granjas')
                            ->maxSize(2048),
                        Forms\Components\Toggle::make('ativo')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->circular(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('URL')
                    ->prefix('/app/')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('documento')
                    ->label('CNPJ'),
                Tables\Columns\TextColumn::make('usuarios_count')
                    ->label('Usuários')
                    ->counts('usuarios'),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean(),
            ])
            ->striped()
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageGranjas::route('/'),
        ];
    }
}
