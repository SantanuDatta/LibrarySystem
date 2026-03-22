<?php

use App\Filament\Admin\Pages\ManageGeneral;
use App\Models\Role;
use App\Settings\GeneralSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    asRole(Role::IS_ADMIN);

    Storage::fake('public');
});

it('uses the bundled logo as the default site logo', function (): void {
    $bundledLogoPath = 'sites/library-system-wordmark.png';

    Storage::disk('public')->put(
        $bundledLogoPath,
        file_get_contents(public_path('images/library-system-wordmark.png')),
    );

    $settings = app(GeneralSettings::class)->refresh();

    expect($settings->site_logo)->toBe($bundledLogoPath)
        ->and(Storage::disk('public')->exists($settings->site_logo))->toBeTrue();
});

it('stores uploaded site logos on the public disk', function (): void {
    $logo = UploadedFile::fake()->image('site-logo.png');

    livewire(ManageGeneral::class, ['panel' => 'admin'])
        ->fillForm([
            'site_name' => 'Library Management System',
            'site_active' => true,
            'site_logoWidth' => '16rem',
            'site_logoHeight' => '3rem',
            'site_logo' => $logo,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = app(GeneralSettings::class)->refresh();

    expect($settings->site_logo)->toStartWith('sites/')
        ->and(Storage::disk('public')->exists($settings->site_logo))->toBeTrue();
});
