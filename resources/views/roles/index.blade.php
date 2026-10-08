@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="py-6 space-y-5">
    <a href="{{ route('users.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Users</a>
    <h1 class="text-lg font-semibold">Roles &amp; Permissions</h1>
    <p class="text-sm text-secondary -mt-3">Read-only reference — these are the three fixed roles from setup, enforced on every route on the backend, not just hidden in the menu.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach ($roles as $role)
            <div class="bg-card border border-border rounded-2xl p-5">
                <h2 class="font-semibold mb-3">{{ $role->name }}</h2>
                <ul class="space-y-1.5 text-sm">
                    @forelse ($role->permissions as $permission)
                        <li class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-success"></span>
                            {{ $permission->name }}
                        </li>
                    @empty
                        <li class="text-secondary text-xs">No permissions assigned.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
</div>
@endsection
