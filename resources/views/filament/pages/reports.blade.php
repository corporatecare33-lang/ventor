<x-filament-panels::page>
    <x-filament::section>
        {{ $this->filtersForm }}
    </x-filament::section>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($summary as $label => $value)
            <x-filament::section>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                <p class="mt-1 text-xl font-semibold text-gray-950 dark:text-white">{{ $value }}</p>
            </x-filament::section>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-filament::section heading="Orders by status">
            <table class="w-full text-sm">
                <tbody>
                    @forelse ($byStatus as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/10">
                            <td class="py-2">{{ $this->statusLabel($row['status']) }}</td>
                            <td class="py-2 text-right">{{ $row['total'] }}</td>
                            <td class="py-2 text-right text-gray-500">{{ money($row['amount']) }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-gray-500">No orders in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section heading="Best-selling products">
            <table class="w-full text-sm">
                <tbody>
                    @forelse ($bestSellers as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/10">
                            <td class="py-2">{{ $row->product_name }}</td>
                            <td class="py-2 text-right">{{ (int) $row->quantity }} pcs</td>
                            <td class="py-2 text-right text-gray-500">{{ money($row->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-gray-500">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section heading="Top customers">
            <table class="w-full text-sm">
                <tbody>
                    @forelse ($topCustomers as $row)
                        <tr class="border-b border-gray-100 last:border-0 dark:border-white/10">
                            <td class="py-2">{{ $row->name }} <span class="text-gray-500">· {{ $row->phone }}</span></td>
                            <td class="py-2 text-right">{{ $row->orders }} orders</td>
                            <td class="py-2 text-right text-gray-500">{{ money($row->spent) }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-2 text-gray-500">No customers in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-filament::section>

        <x-filament::section heading="Stock report" :description="number_format($stock['units']).' units in stock · retail value '.money($stock['value'])">
            <p class="mb-2 text-xs font-semibold uppercase text-danger-600">Out of stock ({{ $stock['out']->count() }})</p>
            <ul class="mb-4 space-y-1 text-sm">
                @forelse ($stock['out'] as $p)
                    <li>{{ $p->name }} <span class="text-gray-500">· {{ $p->sku }}</span></li>
                @empty
                    <li class="text-gray-500">None</li>
                @endforelse
            </ul>
            <p class="mb-2 text-xs font-semibold uppercase text-warning-600">Low stock ({{ $stock['low']->count() }})</p>
            <ul class="space-y-1 text-sm">
                @forelse ($stock['low'] as $p)
                    <li>{{ $p->name }} <span class="text-gray-500">· {{ $p->stock_quantity }} left</span></li>
                @empty
                    <li class="text-gray-500">None</li>
                @endforelse
            </ul>
        </x-filament::section>
    </div>

    <x-filament::section heading="Daily sales">
        <div class="max-h-96 overflow-y-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-gray-500"><th class="py-2">Date</th><th class="py-2 text-right">Orders</th><th class="py-2 text-right">Revenue</th></tr></thead>
                <tbody>
                    @foreach ($daily->reverse() as $day => $row)
                        <tr class="border-t border-gray-100 dark:border-white/10">
                            <td class="py-1.5">{{ \Carbon\Carbon::parse($day)->format('D, d M Y') }}</td>
                            <td class="py-1.5 text-right">{{ $row['orders'] }}</td>
                            <td class="py-1.5 text-right">{{ money($row['revenue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
