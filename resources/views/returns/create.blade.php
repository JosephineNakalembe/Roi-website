@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Return Items - {{ $order->order_number }}</h1>
        </div>
    </div>
    <div class="card" style="max-width:900px;margin:0 auto;">
        
        <!-- Important Notices Box -->
        <div style="padding:16px;background:#fef2f2;border:2px solid #fecaca;border-radius:14px;margin-bottom:18px;">
            <h2 style="color:#b91c1c;font-size:1.1rem;margin:0 0 8px 0;">⚠️ Important: Please Read Before Proceeding</h2>
            <ul style="margin:0;padding-left:20px;font-size:1rem;color:#991b1b;display:grid;gap:6px;">
                <li>You have <strong>7 days from delivery</strong> to initiate a return.</li>
                <li>You are responsible for the <strong>pickup transport fee</strong> of UGX 2,000 - 15,000 depending on your area.</li>
                <li>Returned items will be <strong>inspected</strong> upon pickup before processing your refund.</li>
                <li>Refunds are processed via <strong>MTN Mobile Money or Airtel Money</strong> within 24-72 hours after inspection.</li>
                <li>Items must be in their <strong>original condition</strong> with tags attached.</li>
            </ul>
        </div>

        <form method="POST" action="{{ route('orders.return.store', $order) }}" enctype="multipart/form-data">
            @csrf

            <div style="display:grid;gap:18px;">
                <!-- Select Items to Return -->
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Select Items to Return</h2>
                    @foreach($order->items as $item)
                        <label style="display:flex;align-items:center;gap:12px;padding:12px;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:8px;background:#fff;cursor:pointer;">
                            <input type="checkbox" name="items[]" value="{{ $item->id }}" style="width:18px;height:18px;">
                            <div>
                                <span style="font-weight:600;">{{ $item->product_name }}</span>
                                <p style="margin:2px 0 0;font-size:0.95rem;color:#6b7280;">
                                    Qty: {{ $item->quantity }} • UGX{{ number_format($item->total_price, 2) }}
                                    @if($item->color || $item->size)
                                        • {{ $item->color ? "Color: {$item->color}" : '' }}{{ $item->size ? " Size: {$item->size}" : '' }}
                                    @endif
                                </p>
                            </div>
                        </label>
                    @endforeach
                    @error('items')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                </div>

                <!-- Reason for Return -->
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Reason for Return</h2>
                    <p class="text-muted" style="font-size:0.95rem;margin-bottom:8px;">Why are you returning these items?</p>
                    <select class="input" name="reason" required>
                        <option value="">Select a reason...</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason }}" {{ old('reason') === $reason ? 'selected' : '' }}>{{ $reason }}</option>
                        @endforeach
                    </select>
                    @error('reason')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                </div>

                <!-- Images -->
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Attach Images (Optional, max 5)</h2>
                    <p class="text-muted" style="font-size:0.95rem;">Show photos of the issue to help us process your return faster.</p>
                    <input class="input" type="file" name="images[]" multiple accept="image/*" style="padding:8px;">
                    @error('images.*')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                    @error('images')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                </div>

                <!-- Written Explanation -->
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>Written Explanation</h2>
                    <p class="text-muted" style="font-size:0.95rem;">Please explain in detail why you want to return these items.</p>
                    <textarea class="input" name="notes" rows="4" required>{{ old('notes') }}</textarea>
                    @error('notes')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                </div>

                <!-- Refund Details -->
                <div style="padding:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;">
                    <h2 style="color:#166534;">💳 Refund Details</h2>
                    <p class="text-muted" style="font-size:0.95rem;">Your refund will be sent to:</p>
                    <div style="display:grid;gap:12px;">
                        <div>
                            <label>Network <span style="color:#dc2626;">*</span></label>
                            <select class="input" name="refund_network" required>
                                <option value="">Select network...</option>
                                <option value="Airtel Money" {{ old('refund_network') === 'Airtel Money' ? 'selected' : '' }}>Airtel Money</option>
                                <option value="MTN Mobile Money" {{ old('refund_network') === 'MTN Mobile Money' ? 'selected' : '' }}>MTN Mobile Money</option>
                            </select>
                            @error('refund_network')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label>Registered Name <span style="color:#dc2626;">*</span></label>
                            <input class="input" name="refund_name" value="{{ old('refund_name') }}" placeholder="Full name as registered on mobile money" required>
                            @error('refund_name')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label>Phone Number <span style="color:#dc2626;">*</span></label>
                            <input class="input" name="refund_number" value="{{ old('refund_number') }}" placeholder="e.g. 0777123456" required>
                            @error('refund_number')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <!-- Pickup Details -->
                <div style="padding:16px;background:#f9fafb;border-radius:14px;">
                    <h2>🚚 Pickup Details</h2>
                    <p class="text-muted" style="font-size:0.95rem;">We will arrange pickup of the items from your location. <strong>You will pay the pickup fee.</strong> The fee is deducted from your refund or paid to the rider.</p>
                    
                    <div style="padding:12px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;margin-bottom:16px;">
                        <p style="margin:0;font-size:1rem;color:#92400e;">
                            <strong>💰 Pickup fee:</strong> The cost depends on your area (UGX 2,000 - UGX 15,000). 
                            This will be <strong>deducted from your refund</strong> or paid directly to the rider upon pickup.
                        </p>
                    </div>

                    <div style="display:grid;gap:12px;">
                        <div>
                            <label>Pickup Area / Delivery Zone <span style="color:#dc2626;">*</span></label>
                            <select class="input" name="pickup_area" required>
                                <option value="">Select your area...</option>
                                @foreach($deliveryAreas as $area => $fee)
                                    <option value="{{ $area }}" {{ old('pickup_area') === $area ? 'selected' : '' }}>{{ $area }} — UGX{{ number_format($fee, 0) }}</option>
                                @endforeach
                            </select>
                            @error('pickup_area')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label>Full Pickup Address <span style="color:#dc2626;">*</span></label>
                            <input class="input" name="pickup_address" value="{{ old('pickup_address') }}" placeholder="Street, building, landmark..." required>
                            @error('pickup_address')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label>Contact Phone Number <span style="color:#dc2626;">*</span></label>
                            <input class="input" name="pickup_contact" value="{{ old('pickup_contact') }}" placeholder="Phone number for pickup coordination" required>
                            @error('pickup_contact')<p style="color:#dc2626;font-size:0.95rem;">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end;">
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
                    <button class="btn" type="submit">Submit Return Request</button>
                </div>
            </div>
        </form>
    </div>
@endsection