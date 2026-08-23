<?php

namespace App\Filament\Plataforma\Resources;

use App\Filament\Plataforma\Resources\UsuarioResource\Pages;
use App\Models\Granja;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Gestão de usuários no painel da plataforma: o super admin cadastra
 * o dono de cada granja (e pode intervir em qualquer conta).
 */
class UsuarioResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'usuarios';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'usuário';

    protected static ?string $pluralModelLabel = 'usuários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation) => $operation === 'create')
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->maxLength(255),
                Forms\Components\Select::make('granja_id')
                    ->label('Granja')
                    ->options(Granja::pluck('nome', 'id'))
                    ->searchable()
                    ->helperText('Deixe vazio apenas para usuários da plataforma (super admin).'),
                Forms\Components\Select::make('roles')
                    ->label('Perfis')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('granja.nome')
                    ->label('Granja')
                    ->placeholder('Plataforma')
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Perfis')
                    ->badge(),
            ])
            ->striped()
            ->filters([
                Tables\Filters\SelectFilter::make('granja_id')
                    ->label('Granja')
                    ->options(Granja::pluck('nome', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUsuarios::route('/'),
        ];
    }
}
