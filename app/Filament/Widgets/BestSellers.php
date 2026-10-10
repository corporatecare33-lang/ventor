<?php

namespace App\Filament\Widgets;

use App\Support\SalesQuery;
use Filament\Widgets\Widget;

class BestSellers extends Widget
{
    protected static ?int $sort = 5;

    protected string $view = 'filament.widgets.best-sellers';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view orders');
    }

    protected function getViewData(): array
    {
        return ['rows' => SalesQuery::bestSellers(now()->subDays(30), now(), 5)];
    }
}
