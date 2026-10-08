@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
<div class="py-6 space-y-5">
    <h1 class="text-lg font-semibold">Audit Log</h1>

    <form method="GET" class="bg-card border border-border rounded-2xl p-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-secondary mb-1">User</label>
            <select name="user_id" class="rounded-lg border border-border px-3 py-2 text-sm">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-secondary mb-1">Action contains</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="e.g. sale, product, login" class="rounded-lg border border-border px-3 py-2 text-sm">
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primarydark transition">Filter</button>
        @if(request()->anyFilled(['user_id','action']))
            <a href="{{ route('audit-logs.index') }}" class="px-4 py-2 rounded-lg border border-border text-sm font-semibold hover:bg-bg transition">Reset</a>
        @endif
    </form>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr><th class="text-left px-4 py-3">Date</th><th class="text-left px-4 py-3">User</th><th class="text-left px-4 py-3">Action</th><th class="text-left px-4 py-3">Description</th></tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-secondary whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                        <td class="px-4 py-3">{{ $log->user->name ?? 'System' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-primarydark">{{ $log->action }}</span></td>
                        <td class="px-4 py-3 text-secondary">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-secondary">No audit entries match this filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>
@endsection
