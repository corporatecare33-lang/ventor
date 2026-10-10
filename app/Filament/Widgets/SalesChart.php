<?php

namespace App\Filament\Widgets;

use App\Support\SalesQuery;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Revenue';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view orders');
    }

    protected function getFilters(): ?array
    {
        return ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days'];
    }

    protected function getData(): array
    {
        $days = SalesQuery::dailyRevenue(now()->subDays((int) $this->filter - 1), now());

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (৳)',
                    'data' => $days->pluck('revenue')->values()->all(),
                    'borderColor' => '#b8863b',
                    'backgroundColor' => 'rgba(184,134,59,0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->keys()->map(fn ($d) => Carbon::parse($d)->format('d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
