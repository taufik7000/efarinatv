<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class MarketingPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('marketing')
            ->path('marketing')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Marketing/Resources'), for: 'App\\Filament\\Marketing\\Resources')
            ->discoverPages(in: app_path('Filament/Marketing/Pages'), for: 'App\\Filament\\Marketing\\Pages')
            ->discoverWidgets(in: app_path('Filament/Marketing/Widgets'), for: 'App\\Filament\\Marketing\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                'role:marketing',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
            // Blok ->actions(...) telah dihapus dari sini untuk memperbaiki error.
            // Logika aksi tersebut sekarang berada di app/Filament/Marketing/Pages/Dashboard.php
    }
}
