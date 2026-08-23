<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// Cobra retornos pendentes de cargas montadas há mais de 24h (roda a cada hora)
Schedule::command('ovogest:alertar-retornos')->hourly();

// Resumo diário de vendas a prazo vencidas para admin e financeiro
Schedule::command('ovogest:alertar-vencidas')->dailyAt('08:00');
