<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesOwnership;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnUpdate;
use App\Support\DeliveryAreas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrderReturnController extends Controller
{
    use AuthorizesOwnership;

    public function myReturns()
    {
        $returns = OrderReturn::with('order', 'statusUpdates')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('returns.index', compact('returns'));
    }

    public function track(OrderReturn $orderReturn)
    {
        $this->authorizeOwnership($orderReturn);

        $orderReturn->load('order', 'items.orderItem.product', 'statusUpdates');
        return view('returns.track', compact('orderReturn'));
    }

    public function create(Order $order)
    {
        $this->authorizeOwnership($order);

        if ($order->status !== 'delivered') {
            return back()->withErrors(['You can only return items from delivered orders.']);
        }

        if (!$order->delivered_at || $order->delivered_at->addDays(7)->isPast()) {
            return back()->withErrors(['The return period of 7 days after delivery has expired.']);
        }

        $order->load('items.product');
        $deliveryAreas = DeliveryAreas::all();
        $reasons = [
            'Wrong items received',
            'Item Arrived Damaged',
            'Defective/Faulty',
            'Wrong Size',
            'Not as described',
            'Changed my mind',
            'Quality not satisfactory',
            'Color/style different from picture',
        ];
        
        return view('returns.create', compact('order', 'deliveryAreas', 'reasons'));
    }

    public function store(Request $request, Order $order)
    {
        $this->authorizeOwnership($order);

        if ($order->status !== 'delivered') {
            return back()->withErrors(['You can only return items from delivered orders.']);
        }

        if (!$order->delivered_at || $order->delivered_at->addDays(7)->isPast()) {
            return back()->withErrors(['The return period of 7 days after delivery has expired.']);
        }

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['exists:order_items,id'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:2000'],
            'refund_number' => ['required', 'string', 'max:20'],
            'refund_network' => ['required', 'in:Airtel Money,MTN Mobile Money'],
            'refund_name' => ['required', 'string', 'max:255'],
            'pickup_address' => ['required', 'string', 'max:500'],
            'pickup_contact' => ['required', 'string', 'max:20'],
            'pickup_area' => ['required', 'string'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if (!DeliveryAreas::has($data['pickup_area'])) {
            return back()->withErrors(['pickup_area' => 'Invalid pickup area selected.'])->withInput();
        }

        // Verify selected items belong to this order
        $orderItems = $order->items()->whereIn('id', $data['items'])->pluck('id')->toArray();
        if (count($orderItems) !== count($data['items'])) {
            return back()->withErrors(['items' => 'Invalid items selected.'])->withInput();
        }

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('returns', 'public');
                $imagePaths[] = $path;
            }
        }

        // Generate return number
        $lastReturn = OrderReturn::latest('id')->first();
        $nextId = $lastReturn ? $lastReturn->id + 1 : 1;
        $returnNumber = 'RET' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $pickupFee = DeliveryAreas::fee($data['pickup_area']);

        $return = OrderReturn::create([
            'return_number' => $returnNumber,
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'reason' => $data['reason'],
            'notes' => $data['notes'],
            'refund_number' => $data['refund_number'],
            'refund_network' => $data['refund_network'],
            'refund_name' => $data['refund_name'],
            'pickup_address' => $data['pickup_address'],
            'pickup_contact' => $data['pickup_contact'],
            'pickup_area' => $data['pickup_area'],
            'pickup_fee' => $pickupFee,
            'images' => implode(',', $imagePaths),
            'status' => 'pending',
        ]);

        // Attach selected items
        foreach ($data['items'] as $itemId) {
            $return->items()->create(['order_item_id' => $itemId]);
        }

        // Create initial status update
        $return->statusUpdates()->create([
            'status' => 'pending',
            'note' => 'Return request submitted. Awaiting admin review. You will be notified once your request is processed.',
        ]);

        return redirect()->route('returns.track', $return)
            ->with('success', "Return request {$returnNumber} has been submitted successfully. You can track the progress below.");
    }
}