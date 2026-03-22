<?php

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;

it('uses the bundled logo as the default site logo', function (): void {
    $settings = app(GeneralSettings::class);

    expect($settings->site_logo)->toBe('sites/library-system-wordmark.png');

    expect(Storage::disk('public')->exists($settings->site_logo))->toBeTrue();
});
