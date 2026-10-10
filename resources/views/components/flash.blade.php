@if (session('success'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-700/15 bg-emerald-50 px-4 py-3 text-sm text-emerald-900" role="status">
        <x-lx-icon name="check" class="mt-0.5 size-4 shrink-0" /> <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div class="flex items-start gap-3 rounded-xl border border-red-700/15 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
        <x-lx-icon name="x" class="mt-0.5 size-4 shrink-0" /> <span>{{ session('error') }}</span>
    </div>
@endif
