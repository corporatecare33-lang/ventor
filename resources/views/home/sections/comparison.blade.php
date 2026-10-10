@if ($compare->count() >= 2)
    @php
        $rows = [
            'Price' => fn ($p) => money($p->currentPrice()),
            'Leather' => fn ($p) => $p->leather_type ?? '—',
            'Cards' => fn ($p) => $p->card_capacity ? 'Up to '.$p->card_capacity : '—',
            'Notes' => fn ($p) => $p->cash_capacity ? 'Up to '.$p->cash_capacity : '—',
            'RFID-blocking lining' => fn ($p) => $p->rfid_protection,
            'NFC contact profile' => fn ($p) => $p->nfc_enabled,
            'Laser engraving' => fn ($p) => $p->engraving_available,
            'Warranty' => fn ($p) => $p->warranty_months ? $p->warranty_months.' months' : '—',
        ];
    @endphp
    <section class="section-y" aria-labelledby="compare-title">
        <div class="container-site">
            <x-ui.section-heading id="compare-title" :title="$section->title" :intro="$section->subtitle" />
            <div class="relative mt-10 overflow-x-auto border-y border-line-strong" role="region" aria-label="Wallet comparison table, scrolls horizontally on small screens" tabindex="0">
                <table class="w-full min-w-[40rem] text-left text-[15px]">
                    <caption class="sr-only">Comparison of {{ $compare->pluck('name')->implode(', ') }}</caption>
                    <thead>
                        <tr class="border-b border-line-strong">
                            <td class="w-[28%] py-5"></td>
                            @foreach ($compare as $product)
                                <th scope="col" class="px-4 py-5 align-bottom">
                                    <a href="{{ route('products.show', $product) }}" class="font-display text-xl leading-tight font-semibold text-ink-950 underline-offset-4 hover:underline">{{ $product->name }}</a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $label => $value)
                            <tr class="border-b border-line last:border-0">
                                <th scope="row" class="py-3.5 pr-4 text-sm font-normal text-ink-600">{{ $label }}</th>
                                @foreach ($compare as $product)
                                    @php $v = $value($product); @endphp
                                    <td class="px-4 py-3.5 text-ink-950">
                                        @if (is_bool($v))
                                            @if ($v)<x-lx-icon name="check" class="size-5 text-success" /><span class="sr-only">Yes</span>@else<span class="text-ink-500">No</span>@endif
                                        @else
                                            {{ $v }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
