<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryArea;
use Illuminate\Http\Request;

class DeliveryAreaController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryArea::query();

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($division = $request->input('division')) {
            $query->where('division', $division);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('division', 'like', "%{$search}%");
            });
        }

        $deliveryAreas = $query->latest()->paginate(20);
        $cities = DeliveryArea::distinct()->pluck('city');
        $divisions = DeliveryArea::whereNotNull('division')->distinct()->orderBy('division')->pluck('division');

        return view('admin.delivery-areas.index', compact('deliveryAreas', 'cities', 'divisions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'numeric', 'min:0'],
        ]);

        DeliveryArea::create($data);

        return redirect()->route('admin.delivery-areas.index')->with('success', 'Delivery area added successfully.');
    }

    public function update(Request $request, DeliveryArea $deliveryArea)
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:100'],
            'division' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'fee' => ['required', 'numeric', 'min:0'],
        ]);

        $deliveryArea->update($data);

        return redirect()->route('admin.delivery-areas.index')->with('success', 'Delivery area updated successfully.');
    }

    public function destroy(DeliveryArea $deliveryArea)
    {
        $deliveryArea->delete();
        return redirect()->route('admin.delivery-areas.index')->with('success', 'Delivery area deleted.');
    }
}
