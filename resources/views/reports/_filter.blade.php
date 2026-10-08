<form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-secondary mb-1">From</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-lg border border-border px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-secondary mb-1">To</label>
        <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-lg border border-border px-3 py-2 text-sm">
    </div>
    <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
    <a href="{{ url()->current() }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">This Month</a>
    @isset($exportRoute)
        <a href="{{ route($exportRoute, request()->query()) }}" class="ml-auto px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold hover:opacity-90 transition">Export CSV</a>
    @endisset
</form>
