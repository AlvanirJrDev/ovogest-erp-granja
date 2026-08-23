<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\EstoqueProducaoWidget;
use App\Filament\Widgets\FaturamentoChartWidget;
use App\Filament\Widgets\FinanceiroStatsWidget;
use App\Filament\Widgets\RankingClientesWidget;
use App\Filament\Widgets\RankingRotasQuebraWidget;
use App\Filament\Widgets\UltimasVendasWidget;
use App\Filament\Widgets\VendedorStatsWidget;
use App\Models\Granja;
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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->databaseTransactions()
            ->passwordReset()
            ->profile(isSimple: false)
            ->tenant(Granja::class, slugAttribute: 'slug')
            ->brandName('OvoGest')
            ->brandLogo(asset('images/ovogest-logo.svg'))
            ->darkModeBrandLogo(asset('images/ovogest-logo-dark.svg'))
            ->brandLogoHeight('2.4rem')
            ->favicon(asset('images/ovogest-icon.svg'))
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Rose,
            ])
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                VendedorStatsWidget::class,
                FinanceiroStatsWidget::class,
                EstoqueProducaoWidget::class,
                FaturamentoChartWidget::class,
                RankingRotasQuebraWidget::class,
                RankingClientesWidget::class,
                UltimasVendasWidget::class,
            ])
            ->navigationGroups([
                'Operação',
                'Cadastros',
                'Sistema',
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
