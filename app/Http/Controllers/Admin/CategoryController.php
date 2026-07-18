<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $category = Category::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'category' => $category,
                'message' => 'Category created successfully',
            ]);
        }

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $category->update($data);

        return back()->with('success', 'Category renamed.');
    }

    public function destroy(Category $category)
    {
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            $category->products()->detach();
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
