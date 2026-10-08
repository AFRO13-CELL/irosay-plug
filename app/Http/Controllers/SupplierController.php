<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::withCount('purchases')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $supplier = new Supplier();

        return view('suppliers.create', compact('supplier'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $supplier = Supplier::create($data);

        AuditLog::record($request->user(), 'supplier.created', $supplier, "Created supplier \"{$supplier->name}\"");

        return redirect()->route('suppliers.show', $supplier)->with('success', "Supplier \"{$supplier->name}\" created.");
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()->latest('purchase_date')->paginate(15);

        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $this->validated($request);

        $supplier->update($data);

        AuditLog::record($request->user(), 'supplier.updated', $supplier, "Updated supplier \"{$supplier->name}\"");

        return redirect()->route('suppliers.show', $supplier)->with('success', "Supplier \"{$supplier->name}\" updated.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
