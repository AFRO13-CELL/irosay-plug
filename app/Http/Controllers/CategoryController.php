<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return view('inventory.categories.index', compact('categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(); // for parent-category select
        $category = new Category();

        return view('inventory.categories.create', compact('categories', 'category'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $category = Category::create($data);

        AuditLog::record($request->user(), 'category.created', $category, "Created category \"{$category->name}\"");

        return redirect()->route('inventory.categories.index')->with('success', "Category \"{$category->name}\" created.");
    }

    public function edit(Category $category)
    {
        $categories = Category::where('id', '!=', $category->id)->orderBy('name')->get();

        return view('inventory.categories.edit', compact('category', 'categories'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $this->validated($request, $category->id);

        $category->update($data);

        AuditLog::record($request->user(), 'category.updated', $category, "Updated category \"{$category->name}\"");

        return redirect()->route('inventory.categories.index')->with('success', "Category \"{$category->name}\" updated.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tracking_type' => ['required', Rule::in(['serialized', 'quantity'])],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        return $validated;
    }
}
