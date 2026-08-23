<?php

namespace App\Providers;

use App\Models\Auditoria;
use App\Models\CargaCaminhao;
use App\Models\Cliente;
use App\Models\Conciliacao;
use App\Models\Granja;
use App\Models\MovimentoEstoque;
use App\Models\Produto;
use App\Models\RetornoCaminhao;
use App\Models\Rota;
use App\Models\User;
use App\Models\Veiculo;
use App\Models\Venda;
use App\Policies\AdminPolicy;
use App\Policies\AuditoriaPolicy;
use App\Policies\CargaCaminhaoPolicy;
use App\Policies\ClientePolicy;
use App\Policies\ConciliacaoPolicy;
use App\Policies\GranjaPolicy;
use App\Policies\MovimentoEstoquePolicy;
use App\Policies\UserPolicy;
use App\Policies\VendaPolicy;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Produto::class, AdminPolicy::class);
        Gate::policy(Veiculo::class, AdminPolicy::class);
        Gate::policy(Cliente::class, ClientePolicy::class);
        Gate::policy(Rota::class, AdminPolicy::class);
        Gate::policy(CargaCaminhao::class, CargaCaminhaoPolicy::class);
        Gate::policy(Venda::class, VendaPolicy::class);
        Gate::policy(RetornoCaminhao::class, AdminPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Granja::class, GranjaPolicy::class);
        Gate::policy(Conciliacao::class, ConciliacaoPolicy::class);
        Gate::policy(MovimentoEstoque::class, MovimentoEstoquePolicy::class);
        Gate::policy(Auditoria::class, AuditoriaPolicy::class);

        // Senhas novas exigem no mínimo 8 caracteres com letras e números
        // (vale para perfil, redefinição e cadastro de usuários)
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Trilha de auditoria: registra cada login com IP e navegador
        Event::listen(Login::class, function (Login $event) {
            DB::table('acessos')->insert([
                'user_id' => $event->user->getAuthIdentifier(),
                'ip' => request()->ip(),
                'user_agent' => (string) str(request()->userAgent() ?? '')->limit(250),
                'logado_em' => now(),
            ]);
        });

        // Tema visual do OvoGest (carregado em todos os painéis)
        FilamentAsset::register([
            Css::make('ovogest-theme', resource_path('css/ovogest-theme.css')),
        ]);

        // Rodapé institucional com o selo da AJR Software
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => view('filament.footer')->render(),
        );

        // Badge do perfil logado, ao lado do avatar (todos os painéis)
        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): string => auth()->check()
                ? '<span class="ovogest-perfil">'.e(auth()->user()->perfil_label).'</span>'
                : '',
        );
    }
}
