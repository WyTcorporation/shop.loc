<?php

namespace App\Filament\Mine\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 12;
    }
}
