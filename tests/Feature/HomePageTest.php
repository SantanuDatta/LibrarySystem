<?php

use App\Settings\GeneralSettings;

it('renders the home landing page', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewIs('home')
        ->assertViewHas('settings', fn ($settings): bool => $settings === null || $settings instanceof GeneralSettings)
        ->assertSee('Library operations, built for modern campuses.', false)
        ->assertSee('Open Admin Panel', false)
        ->assertSee('Continue as Staff', false);
});

it('registers the home route name', function (): void {
    expect(route('home', absolute: false))->toBe('/');
});
