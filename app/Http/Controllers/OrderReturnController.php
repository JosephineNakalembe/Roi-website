<?php

namespace App\Http\Controllers;

use App\Mail\ReturnRequestedMail;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\OrderReturnUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderReturnController extends Controller
{
    const DELIVERY_AREAS = [
        'Kampala Road' => 3500,
        'Nakasero' => 4000,
        'Old Kampala' => 3000,
        'Kisenyi' => 3500,
        'Wandegeya' => 3000,
        'Makerere' => 2000,
        'Ntinda' => 6000,
        'Naguru' => 5000,
        'Bugolobi' => 7000,
        'Nakawa' => 6500,
        'Kyambogo' => 7000,
        'Banda' => 10000,
        'Kiwatule' => 7000,
        'Namugongo' => 14000,
        'Kololo' => 5000,
        'Bukoto' => 5000,
        'Kamwokya' => 4000,
        'Acacia Area' => 4500,
        'Kisementi' => 3500,
        'Muyenga' => 7000,
        'Makindye' => 13000,
        'Kansanga' => 7000,
        'Ggaba' => 12500,
        'Munyonyo' => 14000,
        'Buziga' => 12000,
        'Zana' => 8000,
        'Bunamwaya' => 10000,
        'Najjanankumbi' => 7000,
        'Lubowa' => 7000,
        'Seguku' => 9000,
        'Kajjansi' => 14000,
        'Rubaga' => 4400,
        'Mengo' => 4000,
        'Namirembe' => 5000,
        'Kawempe' => 6000,
        'Bwaise' => 5000,
        'Kazo' => 5000,
        'Kanyanya' => 5000,
        'Maganjo' => 5500,
        'Kyaliwajjala' => 13000,
        'Kira' => 12500,
        'Najjera' => 10000,
        'Bulindo' => 15000,
    ];

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
        if ($orderReturn->user_id !== Auth::id()) {
            abort(403);
        }

        $orderReturn->load('order', 'items.orderItem.product', 'statusUpdates');
        return view('returns.track', compact('orderReturn'));
    }

    public function create(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->status !== 'delivered') {
            return back()->withErrors(['You can only return items from delivered orders.']);
        }

        if (!$order->delivered_at || $order->delivered_at->addDays(7)->isPast()) {
            return back()->withErrors(['The return period of 7 days after delivery has expired.']);
        }

        $order->load('items.product');
        $deliveryAreas = self::DELIVERY_AREAS;
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
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

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

        if (!isset(self::DELIVERY_AREAS[$data['pickup_area']])) {
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
                $path = $image->store('returns', 'r2');
                $imagePaths[] = $path;
            }
        }

        // Generate return number
        $lastReturn = OrderReturn::latest('id')->first();
        $nextId = $lastReturn ? $lastReturn->id + 1 : 1;
        $returnNumber = 'RET' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        $pickupFee = self::DELIVERY_AREAS[$data['pickup_area']];

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

        // Send return requested email
        try {
            $order->load('user');
            Mail::to($order->user->email)->send(new ReturnRequestedMail($return));
        } catch (\Exception $e) {
            Log::error('Failed to send return request email: ' . $e->getMessage());
        }

        return redirect()->route('returns.track', $return)
            ->with('success', "Return request {$returnNumber} has been submitted successfully. You can track the progress below.");
    }
}