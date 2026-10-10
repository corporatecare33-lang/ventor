<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Shared reporting queries for the dashboard widgets and the Reports page. */
class SalesQuery
{
    /** Statuses that do not count as revenue. */
    public const EXCLUDED = [OrderStatus::Cancelled, OrderStatus::Returned];

    public static function orders(?CarbonInterface $from = null, ?CarbonInterface $to = null): Builder
    {
        return Order::query()
            ->whereNotIn('status', self::EXCLUDED)
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to->copy()->endOfDay()));
    }

    public static function revenue(?CarbonInterface $from = null, ?CarbonInterface $to = null): float
    {
        return (float) self::orders($from, $to)->sum('total');
    }

    /** @return Collection<string, float> date => revenue, every day filled */
    public static function dailyRevenue(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rows = self::orders($from, $to)
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue, COUNT(*) as orders')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = collect();
        for ($date = $from->copy()->startOfDay(); $date->lte($to); $date->addDay()) {
            $key = $date->toDateString();
            $days[$key] = ['revenue' => (float) ($rows[$key]->revenue ?? 0), 'orders' => (int) ($rows[$key]->orders ?? 0)];
        }

        return $days;
    }

    /** @return Collection<int, object{product_id:int, product_name:string, quantity:int, revenue:float}> */
    public static function bestSellers(?CarbonInterface $from = null, ?CarbonInterface $to = null, int $limit = 10): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', array_map(fn ($s) => $s->value, self::EXCLUDED))
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where('orders.created_at', '<=', $to->copy()->endOfDay()))
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->select('order_items.product_id', 'order_items.product_name', DB::raw('SUM(order_items.quantity) as quantity'), DB::raw('SUM(order_items.line_total) as revenue'))
            ->orderByDesc('quantity')
            ->limit($limit)
            ->get();
    }
}
