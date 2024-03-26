<?php

namespace App\Filament\Staff\Widgets;

use Filament\Widgets\AccountWidget as BaseAccount;

class AccountWidget extends BaseAccount
{
    protected static ?int $sort = -3;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    /**
     * @var view-string
     */
    protected static string $view = 'filament-panels::widgets.account-widget';
}
