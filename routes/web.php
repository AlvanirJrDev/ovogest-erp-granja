<?php

use App\Http\Controllers\CargaPdfController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\VendaPdfController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

// Alias para o middleware "auth" redirecionar convidados ao login do painel
Route::redirect('/login', '/app/login')->name('login');

Route::get('/cargas/{carga}/pdf', CargaPdfController::class)
    ->middleware('auth')
    ->name('cargas.pdf');

Route::get('/vendas/{venda}/pdf', VendaPdfController::class)
    ->middleware('auth')
    ->name('vendas.pdf');

Route::middleware('auth')->group(function () {
    Route::get('/relatorios/fechamento/{ano}/{mes}', [RelatorioController::class, 'fechamento'])
        ->whereNumber(['ano', 'mes'])
        ->name('relatorios.fechamento');

    Route::get('/relatorios/extrato/{cliente}', [RelatorioController::class, 'extrato'])
        ->name('relatorios.extrato');
});
