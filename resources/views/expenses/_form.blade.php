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
    <div>
        <label class="block text-sm font-medium mb-1">Category</label>
        <select name="category" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            <option value="">Select category...</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}" @selected(old('category', $expense->category ?? '') === $cat)>{{ $cat }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Amount (GH₵)</label>
        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $expense->amount ?? '') }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Date</label>
        <input type="date" name="date" value="{{ old('date', isset($expense) ? $expense->date->format('Y-m-d') : now()->format('Y-m-d')) }}" required
               class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Payment Method</label>
        <select name="payment_method" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            @foreach ($paymentMethods as $method)
                <option value="{{ $method }}" @selected(old('payment_method', $expense->payment_method ?? '') === $method)>{{ $method }}</option>
            @endforeach
        </select>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium mb-1">Description (optional)</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('description', $expense->description ?? '') }}</textarea>
    </div>
</div>
