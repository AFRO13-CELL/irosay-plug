@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <form method="GET" class="flex-1 max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone..."
                   class="w-full rounded-lg border border-border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
        </form>
        <a href="{{ route('suppliers.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add Supplier</a>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Phone</th>
                    <th class="text-left px-4 py-3">Location</th>
                    <th class="text-right px-4 py-3">Purchases</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 font-medium">{{ $supplier->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $supplier->phone ?? '—' }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $supplier->location ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $supplier->purchases_count }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="text-primary hover:underline">View</a>
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-secondary">No suppliers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $suppliers->links() }}
</div>
@endsection
