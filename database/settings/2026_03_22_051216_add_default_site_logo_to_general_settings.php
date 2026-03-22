<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->update('general.site_logo', function ($value) {
            if (filled($value)) {
                return $value;
            }

            return 'sites/library-system-wordmark.png';
        });
    }
};
