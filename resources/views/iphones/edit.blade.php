@extends('layouts.app')

@section('title', 'Edit iPhone')

@section('content')
<div class="py-6 max-w-3xl">
    <a href="{{ route('iphones.show', $device) }}" class="text-sm text-secondary hover:text-maintext">&larr; Back to Device</a>

    <div class="bg-card border border-border rounded-2xl p-6 mt-4">
        <h2 class="font-semibold text-lg mb-4">Edit iPhone — IMEI {{ $device->imei1 }}</h2>

        <form method="POST" action="{{ route('iphones.update', $device) }}" class="space-y-5">
            @csrf
            @method('PUT')
            @include('iphones._form', ['device' => $device, 'products' => $products, 'suppliers' => $suppliers])
            <button type="submit" class="w-full brand-gradient text-white font-semibold rounded-lg py-2.5 text-sm hover:opacity-90 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>
@endsection
