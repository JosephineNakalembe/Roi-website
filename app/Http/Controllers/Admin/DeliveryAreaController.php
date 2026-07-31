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

        $deliveryAreas = $query->latest()->paginate(20);
        $cities = DeliveryArea::distinct()->pluck('city');

        return view('admin.delivery-areas.index', compact('deliveryAreas', 'cities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'city' => ['required', 'string', 'max:100'],
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
