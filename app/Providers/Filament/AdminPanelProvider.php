<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
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
            ->brandName('KI für Küchenabgleich')                              
            ->brandLogo(fn () => view('filament.logo'))            // optional: custom SVG/logo
            ->favicon(asset('images/favicon.svg'))
            ->collapsibleNavigationGroups()
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make(__('filament.order_processing'))
                    ->collapsible()
                    ->collapsed(false),
                \Filament\Navigation\NavigationGroup::make(__('filament.invoice_processing')),
                \Filament\Navigation\NavigationGroup::make(__('filament.knowledge_base')),
                \Filament\Navigation\NavigationGroup::make(__('filament.administration')),
            ])
            ->renderHook('panels::topbar.start', fn () => view('filament.hooks.topbar-search'))
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce('400ms')
            ->sidebarFullyCollapsibleOnDesktop()
            ->registration()
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                Dashboard::class,
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
