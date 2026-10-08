<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('sales')
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search');
                $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $customer = new Customer();

        return view('customers.create', compact('customer'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $customer = Customer::create($data);

        AuditLog::record($request->user(), 'customer.created', $customer, "Created customer \"{$customer->name}\"");

        return redirect()->route('customers.show', $customer)->with('success', "Customer \"{$customer->name}\" created.");
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()->with('items')->latest('sold_at')->paginate(15);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->validated($request);

        $customer->update($data);

        AuditLog::record($request->user(), 'customer.updated', $customer, "Updated customer \"{$customer->name}\"");

        return redirect()->route('customers.show', $customer)->with('success', "Customer \"{$customer->name}\" updated.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
