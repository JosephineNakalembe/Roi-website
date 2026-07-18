@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Return {{ $orderReturn->return_number }}</h1>
        </div>
    </div>
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
            <div>
                <p class="text-muted" style="margin:2px 0 0;">
                    Order {{ $orderReturn->order->order_number }} • 
                    @if($orderReturn->status === 'pending')
                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.9rem;font-weight:600;background:#fef3c7;color:#92400e;">Pending Review</span>
                    @elseif($orderReturn->status === 'approved')
                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.9rem;font-weight:600;background:#dbeafe;color:#1e40af;">Approved</span>
                    @elseif($orderReturn->status === 'rejected')
                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.9rem;font-weight:600;background:#fee2e2;color:#991b1b;">Rejected</span>
                    @elseif($orderReturn->status === 'refunded')
                        <span style="display:inline-block;padding:4px 12px;border-radius:999px;font-size:0.9rem;font-weight:600;background:#d1fae5;color:#065f46;">Refunded</span>
                    @endif
                </p>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('returns.index') }}" class="btn btn-secondary">All Returns</a>
                <a href="{{ route('orders.show', $orderReturn->order) }}" class="btn btn-secondary">View Order</a>
            </div>
        </div>

        <div style="display:grid;gap:18px;">
            <!-- Status Timeline -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>📋 Return Status</h2>
                
                @php
                    $statusFlow = ['pending', 'approved', 'picked_up', 'inspected', 'refunded'];
                    $currentStatusIndex = array_search($orderReturn->status, $statusFlow);
                    if ($currentStatusIndex === false) $currentStatusIndex = -1;
                    
                    $statusLabels = [
                        'pending' => 'Return Requested',
                        'approved' => 'Return Approved',
                        'picked_up' => 'Item Picked Up',
                        'inspected' => 'Item Inspected',
                        'refunded' => 'Refund Issued',
                    ];
                    
                    $statusIcons = [
                        'pending' => '📝',
                        'approved' => '✅',
                        'picked_up' => '📦',
                        'inspected' => '🔍',
                        'refunded' => '💰',
                    ];
                @endphp

                <div style="position:relative;padding:20px 0;">
                    @foreach($statusFlow as $i => $status)
                        @php
                            $isComplete = $i <= $currentStatusIndex;
                            $isCurrent = $i === $currentStatusIndex;
                            $bgColor = $isComplete ? ($isCurrent ? '#dbeafe' : '#d1fae5') : '#f3f4f6';
                            $borderColor = $isComplete ? ($isCurrent ? '#2563eb' : '#059669') : '#d1d5db';
                            $textColor = $isComplete ? ($isCurrent ? '#1e40af' : '#065f46') : '#9ca3af';
                        @endphp
                        <div style="display:flex;align-items:flex-start;gap:14px;padding:12px 16px;margin-bottom:8px;background:{{ $bgColor }};border:1px solid {{ $borderColor }};border-radius:12px;{{ $isCurrent ? 'box-shadow:0 2px 8px rgba(37,99,235,0.15);' : '' }}">
                            <span style="font-size:1.5rem;flex-shrink:0;">{{ $statusIcons[$status] }}</span>
                            <div style="flex:1;">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                    <strong style="color:{{ $textColor }};">{{ $statusLabels[$status] }}</strong>
                                    @if($isComplete && !$isCurrent)
                                        <span style="color:#059669;font-size:0.9rem;">✓ Completed</span>
                                    @elseif($isCurrent)
                                        <span style="display:inline-block;padding:2px 8px;border-radius:999px;font-size:0.85rem;font-weight:600;background:#2563eb;color:#fff;">Current</span>
                                    @endif
                                </div>
                                @if($isCurrent)
                                    @php
                                        $latestUpdate = $orderReturn->statusUpdates->first();
                                    @endphp
                                    @if($latestUpdate && $latestUpdate->note)
                                        <p style="margin:4px 0 0;font-size:0.95rem;color:#374151;">{{ $latestUpdate->note }}</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($orderReturn->status === 'rejected')
                    <div style="padding:16px;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;margin-top:8px;">
                        <p style="margin:0;color:#991b1b;">
                            <strong>Return Rejected</strong>
                            @if($orderReturn->admin_notes)
                                <br>{{ $orderReturn->admin_notes }}
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Status Updates Timeline -->
            @if($orderReturn->statusUpdates->isNotEmpty())
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>🕐 Activity Log</h2>
                    <div style="margin-top:12px;">
                        @foreach($orderReturn->statusUpdates as $i => $update)
                            @php
                                $isLast = $i === count($orderReturn->statusUpdates) - 1;
                                $color = match($update->status) {
                                    'refunded' => '#059669',
                                    'approved' => '#2563eb',
                                    'rejected' => '#dc2626',
                                    'picked_up' => '#d97706',
                                    'inspected' => '#7c3aed',
                                    default => '#d1d5db'
                                };
                            @endphp
                            <div style="position:relative;padding-left:24px;padding-bottom:{{ $isLast ? '0' : '20px'}};">
                                <div style="position:absolute;left:0;top:6px;width:12px;height:12px;border-radius:50%;background:{{ $color }};border:2px solid #fff;box-shadow:0 0 0 2px {{ $color }};"></div>
                                @if(!$isLast)
                                    <div style="position:absolute;left:5px;top:18px;width:2px;height:calc(100% - 18px);background:#e5e7eb;"></div>
                                @endif
                                <div>
                                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                                        <strong style="font-size:1rem;color:{{ $color }};">
                                            {{ match($update->status) {
                                                'pending' => 'Return Requested',
                                                'approved' => 'Approved',
                                                'picked_up' => 'Picked Up',
                                                'inspected' => 'Inspected',
                                                'refunded' => 'Refunded',
                                                'rejected' => 'Rejected',
                                                default => ucfirst($update->status)
                                            } }}
                                        </strong>
                                        <span style="font-size:0.9rem;color:#9ca3af;">{{ $update->created_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    @if($update->note)
                                        <p style="margin:4px 0 0;font-size:0.95rem;color:#374151;">{{ $update->note }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Return Details Summary -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <!-- Refund Info -->
                <div style="padding:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;">
                    <h2 style="font-size:1.1rem;color:#166534;margin:0 0 10px 0;">💳 Refund Details</h2>
                    <div style="display:grid;gap:6px;font-size:1rem;">
                        <p style="margin:0;"><strong>Network:</strong> {{ $orderReturn->refund_network }}</p>
                        <p style="margin:0;"><strong>Name:</strong> {{ $orderReturn->refund_name }}</p>
                        <p style="margin:0;"><strong>Number:</strong> {{ $orderReturn->refund_number }}</p>
                        @if($orderReturn->status === 'refunded')
                            <p style="margin:8px 0 0;padding:8px;background:#d1fae5;border-radius:8px;color:#065f46;font-weight:600;">✓ Refund has been processed</p>
                        @endif
                    </div>
                </div>

                <!-- Pickup Info -->
                <div style="padding:16px;background:#fffbeb;border:1px solid #fde68a;border-radius:14px;">
                    <h2 style="font-size:1.1rem;color:#92400e;margin:0 0 10px 0;">🚚 Pickup Details</h2>
                    <div style="display:grid;gap:6px;font-size:1rem;">
                        <p style="margin:0;"><strong>Area:</strong> {{ $orderReturn->pickup_area }}</p>
                        <p style="margin:0;"><strong>Address:</strong> {{ $orderReturn->pickup_address }}</p>
                        <p style="margin:0;"><strong>Contact:</strong> {{ $orderReturn->pickup_contact }}</p>
                        <p style="margin:0;"><strong>Pickup Fee:</strong> UGX {{ number_format($orderReturn->pickup_fee, 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Returned Items -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>📦 Items Being Returned</h2>
                <div style="display:grid;gap:8px;margin-top:8px;">
                    @foreach($orderReturn->items as $returnItem)
                        @php $item = $returnItem->orderItem; @endphp
                        <div style="padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;">
                            <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
                                <div>
                                    <strong>{{ $item->product_name }}</strong>
                                    <p style="margin:2px 0 0;font-size:0.95rem;color:#6b7280;">
                                        Qty: {{ $item->quantity }} • UGX{{ number_format($item->total_price, 2) }}
                                        @if($item->color || $item->size)
                                            • {{ $item->color ? "Color: {$item->color}" : '' }}{{ $item->size ? " Size: {$item->size}" : '' }}
                                        @endif
                                    </p>
                                </div>
                                <span style="font-size:0.95rem;color:#6b7280;">{{ $orderReturn->reason }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Reason & Notes -->
            <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                <h2>📝 Return Reason</h2>
                <p><strong>{{ $orderReturn->reason }}</strong></p>
                @if($orderReturn->notes)
                    <div style="padding:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;margin-top:8px;">
                        {{ $orderReturn->notes }}
                    </div>
                @endif
            </div>

            <!-- Images -->
            @if($orderReturn->images)
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>📸 Attached Images</h2>
                    <div style="display:grid;gap:8px;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));margin-top:8px;">
                        @foreach(explode(',', $orderReturn->images) as $image)
                            <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                                <img src="{{ media_url($image) }}" style="width:100%;height:150px;object-fit:cover;display:block;">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Important Info -->
            <div style="padding:16px;background:#f0fdf4;border:2px solid #bbf7d0;border-radius:14px;">
                <h2 style="color:#166534;font-size:1.1rem;margin:0 0 8px 0;">ℹ️ How Returns Work</h2>
                <ul style="margin:0;padding-left:18px;font-size:1rem;color:#065f46;display:grid;gap:4px;">
                    <li>Your return request will be reviewed by our team within <strong>24-48 hours</strong>.</li>
                    <li>Once approved, a rider will be sent to pick up the items from your address.</li>
                    <li>The <strong>pickup fee (UGX {{ number_format($orderReturn->pickup_fee, 0) }})</strong> will be deducted from your refund or paid to the rider.</li>
                    <li>Items will be <strong>inspected</strong> upon pickup before the refund is processed.</li>
                    <li>Refunds are sent within <strong>24-72 hours</strong> after inspection via mobile money.</li>
                    <li>You will receive <strong>updates</strong> at every step of this process.</li>
                </ul>
            </div>
        </div>
    </div>
@endsection