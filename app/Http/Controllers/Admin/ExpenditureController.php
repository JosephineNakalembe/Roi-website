<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expenditure;
use Illuminate\Http\Request;

class ExpenditureController extends Controller
{
    public function index(Request $request)
    {
        $query = Expenditure::query();

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($from = $request->input('from')) {
            $query->where('date', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('date', '<=', $to);
        }

        $expenditures = $query->latest('date')->paginate(20);

        $categories = Expenditure::whereNotNull('category')->distinct()->pluck('category');
        $totalAmount = (clone $query)->sum('amount');

        return view('admin.expenditures.index', compact('expenditures', 'categories', 'totalAmount'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Expenditure::create($data);

        return redirect()->route('admin.expenditures.index')->with('success', 'Expenditure added successfully.');
    }

    public function destroy(Expenditure $expenditure)
    {
        $expenditure->delete();
        return redirect()->route('admin.expenditures.index')->with('success', 'Expenditure deleted.');
    }
}
