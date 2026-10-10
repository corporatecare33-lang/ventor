<div x-data="{ show: false, message: '', action: null, actionLabel: null, timer: null }"
     x-on:toast.window="message = $event.detail.message; action = $event.detail.action ?? null; actionLabel = $event.detail.actionLabel ?? null; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 4000)"
     class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" role="status" aria-live="polite">
    <div x-cloak x-show="show" x-transition.opacity.duration.200ms class="pointer-events-auto flex max-w-md items-center gap-4 rounded-sm bg-ink-950 py-3 pr-3 pl-4 text-sm text-white shadow-pop">
        <x-lx-icon name="check" class="size-4 shrink-0 text-brass-300" />
        <span x-text="message"></span>
        <template x-if="action">
            <a :href="action" class="btn-inverse btn-sm ml-auto min-h-9" x-text="actionLabel"></a>
        </template>
    </div>
</div>
