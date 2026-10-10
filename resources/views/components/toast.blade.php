<div x-data="{ show: false, message: '', action: null, actionLabel: null, timer: null }"
     x-on:toast.window="message = $event.detail.message; action = $event.detail.action ?? null; actionLabel = $event.detail.actionLabel ?? null; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 3500)"
     class="pointer-events-none fixed inset-x-0 bottom-20 z-50 flex justify-center px-4 sm:bottom-6" aria-live="polite">
    <div x-cloak x-show="show" x-transition.opacity.duration.200ms class="pointer-events-auto flex items-center gap-4 rounded-full bg-ink-900 py-3 pr-3 pl-5 text-sm text-white shadow-2xl shadow-black/30">
        <x-lx-icon name="check" class="size-4 text-gold-400" />
        <span x-text="message"></span>
        <template x-if="action">
            <a :href="action" class="rounded-full bg-gold-500 px-4 py-1.5 text-xs font-semibold text-ink-950" x-text="actionLabel"></a>
        </template>
    </div>
</div>
