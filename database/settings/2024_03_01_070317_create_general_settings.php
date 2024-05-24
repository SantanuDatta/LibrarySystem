<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Library Management System');
        $this->migrator->add('general.site_logo', null);
        $this->migrator->add('general.site_logo_dark', null);
        $this->migrator->add('general.site_logoHeight', '3rem');
        $this->migrator->add('general.site_logoWidth', '16rem');
        $this->migrator->add('general.site_favicon', null);
        $this->migrator->add('general.site_active', true);

        $this->migrator->update('general.site_logo', function ($value) {
            return $value;
        });

        $this->migrator->update('general.site_logo_dark', function ($value) {
            return $value;
        });

        $this->migrator->update('general.site_favicon', function ($value) {
            return $value;
        });
    }
};
