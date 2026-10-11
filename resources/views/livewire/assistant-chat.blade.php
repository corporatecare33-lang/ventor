{{--
    Shop assistant: a launcher button and a chat panel (full screen on phones, a floating panel from sm up).
    It is rendered inside the dark footer, so the panel sets its own text colour and focus ring.
    The conversation is fetched on first open ($wire.load()); on larger screens the panel reopens on the next
    page if it was left open. Messages the visitor sends appear at once (Alpine) while the reply is fetched.
--}}
@php
    $chip = 'inline-flex min-h-10 items-center gap-1.5 rounded-full border border-line-strong bg-white px-3.5 text-left text-[13px] font-medium leading-tight text-ink-950 transition-colors hover:border-ink-950 disabled:cursor-not-allowed disabled:opacity-50';
    $chipPrimary = 'inline-flex min-h-10 items-center gap-1.5 rounded-full bg-ink-950 px-4 text-[13px] font-medium leading-tight text-white transition-colors hover:bg-ink-700';
    $lastAssistant = collect($messages)->keys()->filter(fn ($i) => $messages[$i]['role'] === 'assistant')->last();
@endphp
<div class="print:hidden"
     x-data="{
        open: false,
        busy: false,
        pending: null,
        barShown: false,
        scrolled: false,
        wide: window.matchMedia('(min-width: 640px)'),
        init() {
            this.checkScroll()
            try { if (this.wide.matches && sessionStorage.getItem('lx-assistant') === 'open') this.show(false) } catch (e) {}
        },
        {{-- On phones the launcher waits for a little scrolling, so it never covers the hero's slide controls --}}
        checkScroll() {
            this.scrolled = this.wide.matches || window.scrollY > 160
                || document.documentElement.scrollHeight <= window.innerHeight + 160
        },
        show(focus = true) {
            this.open = true
            this.remember('open')
            const loaded = this.$wire.ready ? Promise.resolve() : this.$wire.load()
            loaded.then(() => this.$nextTick(() => this.scrollToEnd()))
            {{-- Phones get focus from the trap; focusing the field there would open the keyboard over the answer --}}
            if (focus && this.wide.matches) this.$nextTick(() => this.$refs.input.focus())
        },
        hide() {
            this.open = false
            this.remember('closed')
            this.$nextTick(() => this.$refs.launcher.focus())
        },
        remember(state) {
            try { sessionStorage.setItem('lx-assistant', state) } catch (e) {}
        },
        submit() {
            if (this.ask(this.$refs.input.value)) this.$refs.input.value = ''
        },
        ask(message) {
            message = (message ?? '').trim()
            if (! message || this.busy) return false
            this.run(message, () => this.$wire.send(message))
            return true
        },
        add(product, variant, quantity, label) {
            if (! this.busy) this.run(label, () => this.$wire.add(product, variant, quantity, label))
        },
        run(label, call) {
            this.busy = true
            this.pending = label
            this.$nextTick(() => this.scrollToEnd())
            call().finally(() => {
                this.busy = false
                this.pending = null
                this.$nextTick(() => this.scrollToLatest())
            })
        },
        restart() {
            if (this.busy) return
            this.$wire.restart().then(() => this.$nextTick(() => this.scrollToEnd()))
        },
        scrollToEnd() {
            const log = this.$refs.log
            if (log) log.scrollTop = log.scrollHeight
        },
        {{-- Show the visitor's last message at the top, so a long answer is read from its start --}}
        scrollToLatest() {
            const log = this.$refs.log
            const asked = log?.querySelectorAll('[data-role=user]')
            if (! asked?.length) return this.scrollToEnd()
            log.scrollTop = asked[asked.length - 1].offsetTop - 16
        },
     }"
     @keydown.escape.window="if (open) hide()"
     @scroll.window.passive.throttle.100ms="checkScroll()"
     @purchase-bar.window="barShown = $event.detail.shown">

    {{-- The white ring keeps the dark button visible over dark photography (the hero, the footer) --}}
    <button type="button" x-ref="launcher" x-show="! open && scrolled" x-transition.opacity.duration.200ms @click="show()"
            :class="barShown && 'max-lg:hidden'"
            aria-controls="assistant-panel" aria-expanded="false" :aria-expanded="open.toString()"
            class="fixed right-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-30 inline-flex size-12 items-center justify-center rounded-full bg-ink-950 text-white shadow-pop ring-2 ring-white transition-transform hover:scale-105 focus-visible:outline-leather-700 sm:bottom-4 lg:right-6 lg:bottom-6">
        <x-lx-icon name="assistant" class="size-6" />
        <span class="sr-only">Ask the {{ $name }} assistant</span>
    </button>

    <section id="assistant-panel" x-cloak x-show="open" role="dialog" aria-labelledby="assistant-title"
             :aria-modal="(! wide.matches).toString()" x-trap.noscroll="open && ! wide.matches"
             x-transition:enter="transition duration-200 ease-out-quart" x-transition:enter-start="translate-y-3 opacity-0"
             x-transition:leave="transition duration-150 ease-out-quart" x-transition:leave-end="translate-y-3 opacity-0"
             class="fixed inset-0 z-50 flex flex-col bg-white text-[15px] leading-normal text-ink-950 [&_:focus-visible]:outline-leather-700 sm:inset-auto sm:right-4 sm:bottom-4 sm:h-[min(42rem,calc(100dvh-2rem))] sm:w-[25rem] sm:overflow-hidden sm:rounded-card sm:shadow-pop sm:ring-1 sm:ring-line lg:right-6 lg:bottom-6">
        <header class="flex items-center gap-3 border-b border-line py-2.5 pr-2 pl-4">
            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-ink-950 text-brass-300" aria-hidden="true">
                <x-lx-icon name="sparkles" class="size-5" />
            </span>
            <div class="min-w-0 flex-1">
                <h2 id="assistant-title" class="truncate text-[15px] leading-tight font-semibold tracking-[-0.01em] text-ink-950">{{ $name }} Assistant</h2>
                <p class="truncate text-[12px] text-ink-500">Shopping &amp; order help</p>
            </div>
            @if ($ready)
                <livewire:cart-counter key="assistant-cart" />
            @endif
            <button type="button" @click="restart()" class="inline-flex size-11 items-center justify-center text-ink-700 hover:text-ink-950" title="Start a new conversation">
                <x-lx-icon name="refresh" class="size-5" /><span class="sr-only">Start a new conversation</span>
            </button>
            <button type="button" @click="hide()" class="inline-flex size-11 items-center justify-center text-ink-700 hover:text-ink-950">
                <x-lx-icon name="x" class="size-6" /><span class="sr-only">Close the assistant</span>
            </button>
        </header>

        <div x-ref="log" class="relative flex-1 overflow-y-auto overscroll-contain px-4 py-5" role="log" aria-live="polite" aria-label="Conversation">
            @if (! $ready)
                <div class="space-y-3" aria-hidden="true">
                    <div class="skeleton h-4 w-11/12"></div>
                    <div class="skeleton h-4 w-3/4"></div>
                    <div class="mt-4 flex gap-2"><div class="skeleton h-10 w-28 rounded-full"></div><div class="skeleton h-10 w-32 rounded-full"></div></div>
                </div>
            @else
                <div class="space-y-5">
                    @foreach ($messages as $i => $message)
                        @if ($message['role'] === 'user')
                            <div class="flex justify-end" data-role="user" wire:key="assistant-message-{{ $i }}">
                                <p class="max-w-[85%] rounded-card bg-mist-200 px-4 py-2.5 text-[14px] leading-snug text-ink-950 wrap-break-word">{{ $message['text'] }}</p>
                            </div>
                        @else
                            <div class="space-y-3" data-role="assistant" wire:key="assistant-message-{{ $i }}">
                                <div class="text-[14px] leading-relaxed text-ink-800 wrap-break-word">{{ \App\Livewire\AssistantChat::format($message['text']) }}</div>

                                @php($cards = collect($message['products'])->map(fn ($id) => $products->get($id))->filter())
                                @if ($cards->isNotEmpty())
                                    <ul class="space-y-2" aria-label="Products">
                                        @foreach ($cards as $product)
                                            @php($url = route('products.show', $product))
                                            <li class="flex items-center gap-3 rounded-card bg-mist p-2.5">
                                                <a href="{{ $url }}" class="block size-18 shrink-0 overflow-hidden rounded-sm bg-mist-200" tabindex="-1" aria-hidden="true">
                                                    <x-product.media :product="$product" sizes="4.5rem" />
                                                </a>
                                                <div class="min-w-0 flex-1">
                                                    <a href="{{ $url }}" class="line-clamp-2 text-[14px] leading-snug font-medium text-ink-950 underline-offset-4 hover:underline">{{ $product->name }}</a>
                                                    <x-product.price :current="$product->currentPrice()" :regular="$product->hasDiscount() ? $product->regular_price : null" size="text-[14px]" class="mt-0.5" />
                                                    @if ($line = $product->featureLine())
                                                        <p class="mt-0.5 truncate text-[12px] text-ink-500">{{ $line }}</p>
                                                    @endif
                                                </div>
                                                @if ($product->isPurchasable())
                                                    <button type="button" @click="add({{ $product->id }}, null, 1, @js('Add '.$product->name.' to cart'))" :disabled="busy"
                                                            class="grid size-10 shrink-0 place-items-center rounded-full bg-ink-950 text-white transition-colors hover:bg-ink-700 disabled:opacity-50">
                                                        <x-lx-icon name="plus" class="size-4" /><span class="sr-only">Add {{ $product->name }} to cart</span>
                                                    </button>
                                                @else
                                                    <span class="shrink-0 pr-1 text-[12px] font-medium text-ink-500">Sold out</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if ($message['actions'])
                                    {{-- Option chips (ask / add) only act from the latest answer; links always work --}}
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($message['actions'] as $action)
                                            @if ($action['type'] === 'link')
                                                @php($newTab = $action['external'] && str_starts_with($action['url'], 'http'))
                                                <a href="{{ $action['url'] }}" @if ($newTab) target="_blank" rel="noopener" @endif class="{{ $action['primary'] ? $chipPrimary : $chip }}">
                                                    {{ $action['label'] }}@if ($newTab)<span class="sr-only"> (opens in a new tab)</span>@endif
                                                    <x-lx-icon name="arrow-right" class="size-3.5" />
                                                </a>
                                            @elseif ($i === $lastAssistant)
                                                @if ($action['type'] === 'ask')
                                                    <button type="button" @click="ask(@js($action['message']))" :disabled="busy" class="{{ $chip }}">{{ $action['label'] }}</button>
                                                @elseif ($action['type'] === 'add')
                                                    <button type="button" @click="add({{ $action['product'] }}, {{ $action['variant'] ?? 'null' }}, {{ $action['quantity'] ?? 1 }}, @js($action['message']))" :disabled="busy" class="{{ $chip }}">
                                                        <x-lx-icon name="plus" class="size-3.5" />{{ $action['label'] }}
                                                    </button>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- The visitor's message and a typing indicator while the answer loads (Alpine-only, so Livewire leaves it alone) --}}
            <div wire:ignore>
                <template x-if="pending">
                    <div class="mt-5 space-y-4">
                        <div class="flex justify-end" data-role="user">
                            <p class="max-w-[85%] rounded-card bg-mist-200 px-4 py-2.5 text-[14px] leading-snug text-ink-950 wrap-break-word" x-text="pending"></p>
                        </div>
                        <div class="flex items-center gap-1.5 py-1">
                            <span class="size-1.5 animate-bounce rounded-full bg-ink-500 [animation-delay:-0.3s]"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-ink-500 [animation-delay:-0.15s]"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-ink-500"></span>
                            <span class="sr-only">The assistant is typing</span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <form class="border-t border-line p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]" @submit.prevent="submit()">
            <label for="assistant-input" class="sr-only">Message the assistant</label>
            <div class="flex items-center gap-2 rounded-xs border border-line-strong bg-white py-1 pr-1 pl-3.5 transition-colors focus-within:border-ink-950">
                <input id="assistant-input" x-ref="input" type="text" maxlength="300" autocomplete="off" enterkeyhint="send"
                       placeholder="Ask about products, orders, delivery…"
                       class="min-h-10 w-full min-w-0 bg-transparent text-[15px] text-ink-950 placeholder:text-ink-500 focus:outline-none focus-visible:outline-none">
                <button type="submit" :disabled="busy" class="grid size-10 shrink-0 place-items-center rounded-xs bg-ink-950 text-white transition-colors hover:bg-ink-700 disabled:opacity-50">
                    <x-lx-icon name="arrow-up" class="size-4" /><span class="sr-only">Send</span>
                </button>
            </div>
            <p class="mt-2 text-center text-[11px] text-ink-500">Answers come from our catalogue and store policies.</p>
        </form>
    </section>
</div>
