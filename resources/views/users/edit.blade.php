@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="py-6 max-w-lg">
    <a href="{{ route('users.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Users</a>
    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Edit User</h2>

        @if ($errors->any())
            <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm mb-4">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role_id" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <select name="is_active" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                    <option value="1" @selected(old('is_active', (int) $user->is_active) == 1)>Active</option>
                    <option value="0" @selected(old('is_active', (int) $user->is_active) == 0)>Deactivated</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">New Password (optional)</label>
                <input type="password" name="password" minlength="8" placeholder="Leave blank to keep current password" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">Save Changes</button>
        </form>
    </div>
</div>
@endsection
