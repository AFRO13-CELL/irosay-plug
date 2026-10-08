@extends('layouts.app')

@section('title', 'Users & Roles')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex items-center justify-between gap-3">
        <div class="flex gap-4">
            <a href="{{ route('roles.index') }}" class="text-sm text-primary hover:underline">View Roles &amp; Permissions</a>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-primary hover:underline">View Audit Log</a>
        </div>
        <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-lg brand-gradient text-white text-sm font-semibold shadow-sm hover:opacity-90 transition">+ Add User</a>
    </div>

    <div class="bg-card border border-border rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-bg text-secondary text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Name</th><th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3">Role</th><th class="text-left px-4 py-3">Status</th><th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach ($users as $user)
                    <tr class="hover:bg-bg/60">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-secondary">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-accent/10 text-primarydark">{{ $user->role->name ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                {{ $user->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('users.edit', $user) }}" class="text-primary hover:underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
