<?php

use Illuminate\Support\Facades\File;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $sourcePath = public_path('images/BookHive.png');
        $targetPath = storage_path('app/public/sites/BookHive.png');

        if (File::exists($sourcePath) && ! File::exists($targetPath)) {
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($sourcePath, $targetPath);
        }

        $this->migrator->update('general.site_logo', function ($value) {
            if (filled($value)) {
                return $value;
            }

            return 'sites/BookHive.png';
        });
    }
};
