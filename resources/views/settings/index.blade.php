@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="py-6 max-w-2xl space-y-5">
    <h1 class="text-lg font-semibold">Settings</h1>

    @if ($errors->any())
        <div class="rounded-xl bg-danger/10 border border-danger/30 text-danger px-4 py-3 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold">Business Information</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Business Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name', $settings['business_name']) }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Business Type</label>
                    <input type="text" name="business_type" value="{{ old('business_type', $settings['business_type']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="business_phone" value="{{ old('business_phone', $settings['business_phone']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">WhatsApp</label>
                    <input type="text" name="business_whatsapp" value="{{ old('business_whatsapp', $settings['business_whatsapp']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="business_email" value="{{ old('business_email', $settings['business_email']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Currency Symbol</label>
                    <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium mb-1">Address</label>
                    <textarea name="business_address" rows="2" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('business_address', $settings['business_address']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold">Receipts &amp; Policies</h2>
            <div>
                <label class="block text-sm font-medium mb-1">Receipt Footer</label>
                <input type="text" name="receipt_footer" value="{{ old('receipt_footer', $settings['receipt_footer']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Warranty Message</label>
                <input type="text" name="warranty_message" value="{{ old('warranty_message', $settings['warranty_message']) }}" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Return Policy</label>
                <textarea name="return_policy" rows="3" class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">{{ old('return_policy', $settings['return_policy']) }}</textarea>
            </div>
        </div>

        <div class="bg-card border border-border rounded-2xl p-6 space-y-4">
            <h2 class="font-semibold">Payments</h2>
            <div>
                <label class="block text-sm font-medium mb-1">Payment Methods (comma-separated)</label>
                <input type="text" name="payment_methods" value="{{ old('payment_methods', $settings['payment_methods']) }}" required class="w-full rounded-lg border border-border px-3 py-2.5 text-sm">
                <p class="text-xs text-secondary mt-1">Used on the Expenses form. POS currently uses a fixed Cash/Mobile Money/Card/Other list — updating this won't change POS's options without a small code change.</p>
            </div>
        </div>

        <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-3 text-sm hover:opacity-90 transition">Save Settings</button>
    </form>
</div>
@endsection
