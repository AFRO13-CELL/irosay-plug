<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    // Fixed category list per spec section 23. Kept here (not in a table)
    // since the spec treats these as a closed set, unlike Product categories.
    public const CATEGORIES = [
        'Rent', 'Electricity', 'Internet', 'Transport', 'Delivery',
        'Packaging', 'Staff', 'Marketing', 'Other',
    ];

    public function index(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->string('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->string('to')) : now()->endOfMonth();

        $expenses = Expense::with('user')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        $totalForRange = Expense::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->sum('amount');

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => self::CATEGORIES,
            'totalForRange' => $totalForRange,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    public function create()
    {
        $paymentMethods = explode(',', Setting::get('payment_methods', 'Cash,Mobile Money,Card,Other'));

        return view('expenses.create', ['categories' => self::CATEGORIES, 'paymentMethods' => $paymentMethods]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;

        $expense = Expense::create($data);

        AuditLog::record($request->user(), 'expense.created', $expense, "Recorded GH₵" . number_format($expense->amount, 2) . " expense ({$expense->category})");

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function edit(Expense $expense)
    {
        $paymentMethods = explode(',', Setting::get('payment_methods', 'Cash,Mobile Money,Card,Other'));

        return view('expenses.edit', ['expense' => $expense, 'categories' => self::CATEGORIES, 'paymentMethods' => $paymentMethods]);
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validated($request);

        $expense->update($data);

        AuditLog::record($request->user(), 'expense.updated', $expense, "Updated expense #{$expense->id}");

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Request $request, Expense $expense)
    {
        $description = "GH₵" . number_format($expense->amount, 2) . " {$expense->category} expense from " . $expense->date->format('d M Y');
        $expense->delete();

        AuditLog::record($request->user(), 'expense.deleted', null, "Deleted {$description}");

        return back()->with('success', 'Expense deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
