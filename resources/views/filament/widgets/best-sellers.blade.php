<x-filament-widgets::widget>
    <x-filament::section heading="Best sellers (30 days)">
        @forelse ($rows as $row)
            <div class="flex items-center justify-between gap-4 py-2 text-sm @if (! $loop->last) border-b border-gray-100 dark:border-white/10 @endif">
                <span class="font-medium text-gray-950 dark:text-white">{{ $loop->iteration }}. {{ $row->product_name }}</span>
                <span class="text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ (int) $row->quantity }} sold · {{ money($row->revenue) }}</span>
            </div>
        @empty
            <p class="text-sm text-gray-500">No sales yet.</p>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
