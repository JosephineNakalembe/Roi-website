<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ReturnStatusMail;
use App\Models\OrderReturn;
use App\Models\OrderReturnUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category'); // pending, approved, rejected, refunded

        $returns = OrderReturn::with('order', 'user', 'items.orderItem');

        switch ($category) {
            case 'pending':
                $returns->where('status', 'pending');
                break;
            case 'approved':
                $returns->where('status', 'approved');
                break;
            case 'rejected':
                $returns->where('status', 'rejected');
                break;
            case 'refunded':
                $returns->where('status', 'refunded');
                break;
        }

        $returns = $returns->latest()->paginate(20);

        $pendingCount = OrderReturn::where('status', 'pending')->count();
        $approvedCount = OrderReturn::where('status', 'approved')->count();
        $rejectedCount = OrderReturn::where('status', 'rejected')->count();
        $refundedCount = OrderReturn::where('status', 'refunded')->count();

        return view('admin.returns.index', compact('returns', 'category', 'pendingCount', 'approvedCount', 'rejectedCount', 'refundedCount'));
    }

    public function show(OrderReturn $orderReturn)
    {
        $orderReturn->load('order', 'user', 'items.orderItem.product', 'statusUpdates');
        return view('admin.returns.show', compact('orderReturn'));
    }

    public function update(Request $request, OrderReturn $orderReturn)
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected,refunded'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $orderReturn->status;
        $orderReturn->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ? ($orderReturn->admin_notes ? $orderReturn->admin_notes . "\n---\n" . $data['admin_notes'] : $data['admin_notes']) : $orderReturn->admin_notes,
        ]);

        // Create status update
        $note = '';
        switch ($data['status']) {
            case 'approved':
                $note = 'Return request has been approved. Items will be inspected upon pickup.';
                break;
            case 'rejected':
                $note = $data['admin_notes'] ?? 'Return request has been rejected.';
                break;
            case 'refunded':
                $note = 'Refund has been processed successfully.';
                break;
        }

        $orderReturn->statusUpdates()->create([
            'status' => $data['status'],
            'note' => $note . ($data['admin_notes'] && $data['status'] !== 'rejected' ? ' Admin note: ' . $data['admin_notes'] : ''),
        ]);

        // Send return status update email to customer
        try {
            $orderReturn->load('user', 'order');
            Mail::to($orderReturn->user->email)->send(new ReturnStatusMail($orderReturn));
        } catch (\Exception $e) {
            Log::error('Failed to send return status email: ' . $e->getMessage());
        }

        return back()->with('success', "Return {$orderReturn->return_number} has been updated to " . ucfirst($data['status']) . ".");
    }
}