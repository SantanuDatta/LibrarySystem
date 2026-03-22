<?php

it('renders the home landing page', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertViewIs('home')
        ->assertSee('Library operations, built for modern campuses.', false)
        ->assertSee('Open Admin Panel', false)
        ->assertSee('Continue as Staff', false);
});
