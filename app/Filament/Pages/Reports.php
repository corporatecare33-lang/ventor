<?php

namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Filament\Concerns\ChecksPagePermission;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\SalesQuery;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class Reports extends Page
{
    use ChecksPagePermission;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.reports';

    public ?string $from = null;

    public ?string $until = null;

    protected static function permissionArea(): string
    {
        return 'reports';
    }

    public function mount(): void
    {
        $this->from = now()->startOfMonth()->toDateString();
        $this->until = now()->toDateString();
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                DatePicker::make('from')->live()->maxDate(fn () => $this->until),
                DatePicker::make('until')->live()->minDate(fn () => $this->from),
            ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('Export orders (CSV)')->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => $this->exportCsv()),
        ];
    }

    protected function range(): array
    {
        return [Carbon::parse($this->from ?: now()->startOfMonth()), Carbon::parse($this->until ?: now())];
    }

    protected function getViewData(): array
    {
        [$from, $to] = $this->range();

        $orders = SalesQuery::orders($from, $to);
        $count = (clone $orders)->count();
        $revenue = (float) (clone $orders)->sum('total');

        $byStatus = Order::query()
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total) as amount'))
            ->groupBy('status')->get()
            ->map(fn ($row) => ['status' => $row->status, 'total' => $row->total, 'amount' => (float) $row->amount]);

        $topCustomers = (clone $orders)
            ->select('phone', DB::raw('MAX(customer_name) as name'), DB::raw('COUNT(*) as orders'), DB::raw('SUM(total) as spent'))
            ->groupBy('phone')->orderByDesc('spent')->limit(10)->get();

        return [
            'summary' => [
                'Orders' => number_format($count),
                'Revenue' => money($revenue),
                'Avg. order value' => money($count ? $revenue / $count : 0),
                'Items sold' => number_format((int) DB::table('order_items')->whereIn('order_id', (clone $orders)->select('id'))->sum('quantity')),
                'Discounts given' => money((clone $orders)->sum('discount')),
                'New customers' => number_format(User::role('customer')->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->count()),
            ],
            'byStatus' => $byStatus,
            'daily' => SalesQuery::dailyRevenue($from, $to),
            'bestSellers' => SalesQuery::bestSellers($from, $to, 15),
            'topCustomers' => $topCustomers,
            'stock' => [
                'out' => Product::where('status', '!=', ProductStatus::Draft)->where('stock_quantity', '<=', 0)->orderBy('name')->get(['id', 'name', 'sku', 'stock_quantity']),
                'low' => Product::where('status', '!=', ProductStatus::Draft)->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity')->get(['id', 'name', 'sku', 'stock_quantity']),
                'value' => (float) Product::where('status', '!=', ProductStatus::Draft)->selectRaw('SUM(stock_quantity * COALESCE(sale_price, regular_price)) as v')->value('v'),
                'units' => (int) Product::where('status', '!=', ProductStatus::Draft)->sum('stock_quantity'),
            ],
        ];
    }

    public function exportCsv(): StreamedResponse
    {
        abort_unless(auth()->user()->can('view orders'), 403);
        [$from, $to] = $this->range();

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Date', 'Customer', 'Phone', 'City', 'Status', 'Payment', 'Payment status', 'Subtotal', 'Discount', 'Shipping', 'Total']);
            Order::whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
                ->orderBy('id')
                ->chunk(500, function ($orders) use ($out) {
                    foreach ($orders as $o) {
                        fputcsv($out, [$o->order_number, $o->created_at->format('Y-m-d H:i'), $o->customer_name, $o->phone, $o->city,
                            $o->status->getLabel(), $o->payment_method, $o->payment_status->getLabel(), $o->subtotal, $o->discount, $o->shipping_charge, $o->total]);
                    }
                });
            fclose($out);
        }, "leatherx-orders-{$from->toDateString()}-to-{$to->toDateString()}.csv");
    }

    public function statusLabel(string|OrderStatus $status): string
    {
        return ($status instanceof OrderStatus ? $status : OrderStatus::from($status))->getLabel();
    }
}
