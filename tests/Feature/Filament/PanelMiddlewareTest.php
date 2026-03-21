<?php

use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\StaffPanelProvider;
use Filament\Panel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

it('uses request forgery protection middleware for the admin panel', function (): void {
    $panel = (new AdminPanelProvider(app()))->panel(Panel::make());

    expect($panel->getMiddleware())
        ->toContain(PreventRequestForgery::class);
});

it('uses request forgery protection middleware for the staff panel', function (): void {
    $panel = (new StaffPanelProvider(app()))->panel(Panel::make());

    expect($panel->getMiddleware())
        ->toContain(PreventRequestForgery::class);
});
