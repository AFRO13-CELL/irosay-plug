@extends('layouts.app')

@section('title', 'Add iPhone')

@section('content')
<div class="py-6 max-w-3xl">
    <a href="{{ route('iphones.index') }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to iPhones</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-1">Add iPhone</h2>
        <p class="text-sm text-secondary mb-4">Registers one individual device with its own IMEI. Duplicate IMEIs (in either slot) are rejected automatically.</p>

        <form method="POST" action="{{ route('iphones.store') }}" class="space-y-5">
            @csrf
            @include('iphones._form', ['device' => $device, 'products' => $products, 'suppliers' => $suppliers])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                Add iPhone
            </button>
        </form>
    </div>
</div>
@endsection
