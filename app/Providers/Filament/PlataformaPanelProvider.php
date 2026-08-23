<?php

namespace App\Providers\Filament;

use App\Filament\Plataforma\Pages\PlataformaDashboard;
use App\Filament\Plataforma\Widgets\PlataformaStatsWidget;
use App\Filament\Plataforma\Widgets\SaudeWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Painel do dono da plataforma (super admin): gestão das granjas
 * clientes e de todos os usuários. A operação de cada granja fica
 * no painel "app", com prefixo do slug da granja na URL.
 */
class PlataformaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('plataforma')
            ->path('plataforma')
            ->login()
            ->databaseTransactions()
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('OvoGest · Plataforma')
            ->brandLogo(asset('images/ovogest-logo.svg'))
            ->darkModeBrandLogo(asset('images/ovogest-logo-dark.svg'))
            ->brandLogoHeight('2.4rem')
            ->favicon(asset('images/ovogest-icon.svg'))
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->discoverResources(in: app_path('Filament/Plataforma/Resources'), for: 'App\\Filament\\Plataforma\\Resources')
            ->pages([
                PlataformaDashboard::class,
            ])
            ->widgets([
                PlataformaStatsWidget::class,
                SaudeWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
