<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $site_name;

    public ?string $site_logo;

    public ?string $site_logo_dark;

    public string $site_logoHeight;

    public string $site_logoWidth;

    public ?string $site_favicon;

    public bool $site_active;

    public static function group(): string
    {
        return 'general';
    }
}
