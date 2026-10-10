@if ($compare->count() >= 2)
    @php
        $rows = [
            'Price' => fn ($p) => money($p->currentPrice()),
            'Leather' => fn ($p) => $p->leather_type ?? '—',
            'Card capacity' => fn ($p) => $p->card_capacity ? $p->card_capacity.' cards' : '—',
            'Cash capacity' => fn ($p) => $p->cash_capacity ? $p->cash_capacity.' notes' : '—',
            'RFID protection' => fn ($p) => $p->rfid_protection,
            'NFC digital profile' => fn ($p) => $p->nfc_enabled,
            'Laser engraving' => fn ($p) => $p->engraving_available,
            'Warranty' => fn ($p) => $p->warranty_months ? ($p->warranty_months % 12 === 0 ? ($p->warranty_months / 12).' year'.($p->warranty_months > 12 ? 's' : '') : $p->warranty_months.' months') : '—',
        ];
    @endphp
    <section class="py-20 sm:py-24">
        <div class="container-lx">
            <x-section-heading eyebrow="Compare" :title="$section->title" :subtitle="$section->subtitle" />
            <div class="mt-12 overflow-x-auto rounded-2xl border border-ink-900/10">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="bg-ink-900 text-white">
                        <tr>
                            <th scope="col" class="px-5 py-4 text-xs font-medium tracking-wider text-white/60 uppercase">Feature</th>
                            @foreach ($compare as $product)
                                <th scope="col" class="px-5 py-4">
                                    <a href="{{ route('products.show', $product) }}" class="font-display text-lg font-semibold hover:text-gold-300">{{ $product->name }}</a>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $label => $value)
                            <tr class="border-t border-ink-900/8 odd:bg-cream-50">
                                <th scope="row" class="px-5 py-3.5 font-medium text-ink-700">{{ $label }}</th>
                                @foreach ($compare as $product)
                                    @php $v = $value($product); @endphp
                                    <td class="px-5 py-3.5 text-ink-900">
                                        @if (is_bool($v))
                                            @if ($v)
                                                <span class="inline-flex items-center gap-1 text-emerald-700"><x-lx-icon name="check" class="size-4" /> Yes</span>
                                            @else
                                                <span class="text-ink-600/50">—</span>
                                            @endif
                                        @else
                                            {{ $v }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr class="border-t border-ink-900/8">
                            <td class="px-5 py-4"></td>
                            @foreach ($compare as $product)
                                <td class="px-5 py-4"><a href="{{ route('products.show', $product) }}" class="btn-dark px-5 py-2 text-xs">View details</a></td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endif
