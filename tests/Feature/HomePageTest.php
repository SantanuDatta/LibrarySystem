<?php

use App\Settings\GeneralSettings;

it('renders the home landing page', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewIs('home')
        ->assertViewHas('settings', fn ($settings): bool => $settings === null || $settings instanceof GeneralSettings)
        ->assertSee('<html lang="en">', false)
        ->assertSee('Manage your library from catalog to circulation.', false)
        ->assertSee('Go to Admin Panel', false)
        ->assertSee('Go to Staff Panel', false);
});

it('registers the home route name', function (): void {
    expect(route('home', absolute: false))->toBe('/');
});
