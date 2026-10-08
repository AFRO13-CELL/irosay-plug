@if ($errors->any())
    <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Name</label>
        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-accent/40">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Phone</label>
        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Location</label>
        <input type="text" name="location" value="{{ old('location', $supplier->location) }}"
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Notes</label>
        <textarea name="notes" rows="3" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('notes', $supplier->notes) }}</textarea>
    </div>
</div>
