@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('users.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Users</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Add User</h2>

        @if ($errors->any())
            <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm mb-4">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role_id" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                    <option value="">Select role...</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                <p class="text-xs text-secondary mt-1">At least 8 characters.</p>
            </div>
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Create User</button>
        </form>
    </div>
</div>
@endsection
