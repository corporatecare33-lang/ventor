<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view orders');
    }

    protected function getStats(): array
    {
        $today = SalesQuery::revenue(today(), today());
        $month = SalesQuery::revenue(now()->startOfMonth(), now());
        $lastMonth = SalesQuery::revenue(now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow());
        $trend = $lastMonth > 0 ? round(($month - $lastMonth) / $lastMonth * 100) : null;
        $spark = SalesQuery::dailyRevenue(now()->subDays(13), now())->pluck('revenue')->values()->all();

        $pending = Order::where('status', OrderStatus::Pending)->count();
        $lowStock = Product::where('status', '!=', ProductStatus::Draft)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        return [
            Stat::make('Revenue today', money($today))
                ->description(SalesQuery::orders(today(), today())->count().' orders today'),
            Stat::make('Revenue this month', money($month))
                ->description($trend === null ? 'vs last month: —' : ($trend >= 0 ? "+{$trend}%" : "{$trend}%").' vs same period last month')
                ->descriptionIcon($trend !== null && $trend < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($trend !== null && $trend < 0 ? 'danger' : 'success')
                ->chart($spark),
            Stat::make('Pending orders', $pending)->color($pending ? 'warning' : 'gray')
                ->description('Need confirmation')
                ->url(route('filament.admin.resources.orders.index', ['tab' => 'pending'])),
            Stat::make('Low stock products', $lowStock)->color($lowStock ? 'danger' : 'gray')
                ->description('At or below threshold'),
            Stat::make('Customers', User::role('customer')->count())
                ->description(User::role('customer')->where('created_at', '>=', now()->startOfMonth())->count().' new this month'),
        ];
    }
}
