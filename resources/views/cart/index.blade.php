@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:900px;margin:0 auto;">
        <h1>Shopping Cart</h1>
        @if($availableItems->isEmpty() && $outOfStockItems->isEmpty())
            <p>Your cart is empty. <a href="{{ route('shop.index') }}">Continue shopping</a>.</p>
        @else
            @php
                $allSelected = $availableItems->every(fn($item) => $item['selected']);
            @endphp
            <!-- Select All Controls -->
            @if($availableItems->isNotEmpty())
                <div style="display:flex;align-items:center;gap:16px;padding:12px 14px;background:#f9fafb;border-radius:12px;margin-bottom:16px;border:1px solid #e5e7eb;">
                    <form method="POST" action="{{ route('cart.toggle-select-all') }}" id="selectAllForm" style="display:flex;align-items:center;gap:12px;">
                        @csrf
                        <input type="hidden" name="selected" value="{{ $allSelected ? '0' : '1' }}">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.95rem;">
                            <input type="checkbox" onchange="this.closest('form').submit();" {{ $allSelected ? 'checked' : '' }} style="width:18px;height:18px;accent-color:#1a1a2e;">
                            <strong>Select All ({{ $availableItems->count() }} items)</strong>
                        </label>
                    </form>
                </div>
            @endif

            @if($availableItems->isNotEmpty())
                <div style="display:grid;gap:14px;">
                    @foreach($availableItems as $item)
                        @php
                            $lowStock = $item['product']->stock <= 2;
                        @endphp
                        <div style="display:flex;align-items:center;gap:14px;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;position:relative;">
                            <!-- Checkbox -->
                            <form method="POST" action="{{ route('cart.toggle-select') }}" style="flex-shrink:0;">
                                @csrf
                                <input type="hidden" name="cart_item_id" value="{{ $item['cart_key'] }}">
                                <input type="hidden" name="selected" value="{{ $item['selected'] ? '0' : '1' }}">
                                <input type="checkbox" onchange="this.closest('form').submit();" {{ $item['selected'] ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;accent-color:#1a1a2e;">
                            </form>
                            <!-- Product Image -->
                            <div onclick="openEditModal('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}')" style="width:80px;height:80px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f3f4f6;cursor:pointer;">
                                <img src="{{ optional($item['product']->primaryImage)->path ? asset('storage/' . $item['product']->primaryImage->path) : 'https://via.placeholder.com/200x200' }}" alt="{{ $item['product']->name }}" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <!-- Details -->
                            <div onclick="openEditModal('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}')" style="flex:1;min-width:0;cursor:pointer;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                                    <div>
                                        <h3 style="font-size:1rem;margin:0;font-weight:600;">{{ $item['product']->name }}</h3>
                                        @if($lowStock)
                                            <p style="font-size:0.8rem;color:#dc2626;font-weight:600;margin:2px 0 0;">
                                                Only {{ $item['product']->stock }} left
                                            </p>
                                        @endif
                                    </div>
                                    <strong style="white-space:nowrap;font-size:1rem;">UGX{{ number_format($item['total'], 0) }}</strong>
                                </div>
                                <p class="text-muted" style="font-size:0.85rem;margin:2px 0 0;">UGX{{ number_format($item['unit_price'] ?? $item['product']->price, 0) }} each</p>
                                @if($item['color'] || $item['size'])
                                    <p style="font-size:0.85rem;color:#6b7280;margin:2px 0 0;">
                                        @if($item['color'])<span>Color: {{ $item['color'] }}</span>@endif
                                        @if($item['size'])<span> | Size: {{ $item['size'] }}</span>@endif
                                    </p>
                                @endif
                                <p style="font-size:0.85rem;color:#6b7280;margin:4px 0 0;">Qty: {{ $item['quantity'] }} <span style="color:#9ca3af;font-size:0.75rem;">(click to edit)</span></p>
                            </div>
                            <!-- Remove Button -->
                            <form method="POST" action="{{ route('cart.remove', $item['product']) }}" style="flex-shrink:0;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="color" value="{{ $item['color'] ?? '' }}">
                                <input type="hidden" name="size" value="{{ $item['size'] ?? '' }}">
                                <button class="btn" style="background:#ef4444;padding:6px 10px;font-size:0.8rem;">&times;</button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <!-- Subtotal & Checkout -->
                <div style="margin-top:20px;padding:16px;background:#f9fafb;border-radius:14px;border:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                    <div>
                        <p class="text-muted" style="font-size:0.9rem;margin:0;">Subtotal ({{ $availableItems->filter(fn($i) => $i['selected'])->count() }} selected items)</p>
                        <h2 style="margin:4px 0 0;">UGX{{ number_format($subtotal, 0) }}</h2>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <a class="btn btn-secondary" href="{{ route('shop.index') }}">Continue Shopping</a>
                        <a class="btn" href="{{ route('checkout.show') }}" style="{{ $subtotal > 0 ? '' : 'opacity:0.5;pointer-events:none;' }}">Checkout</a>
                    </div>
                </div>
            @endif

            @if($outOfStockItems->isNotEmpty())
                <div style="margin-top:30px;padding:18px;border:1px solid #fde2e2;border-radius:16px;background:#fff1f1;">
                    <h2 style="margin-bottom:14px;color:#b91c1c;">Out of stock</h2>
                    <div style="display:grid;gap:14px;">
                        @foreach($outOfStockItems as $item)
                            <div style="padding:12px;border:1px solid #f5c2c7;border-radius:12px;background:#fff7f7;">
                                <h3>{{ $item['product']->name }}</h3>
                                <p class="text-muted">This item is no longer available.</p>
                                <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="color" value="{{ $item['color'] ?? '' }}">
                                    <input type="hidden" name="size" value="{{ $item['size'] ?? '' }}">
                                    <button class="btn" style="background:#ef4444;padding:8px;font-size:0.95rem;">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($suggestions->isNotEmpty())
                <div style="margin-top:32px;">
                    <h2>You might also like</h2>
                    <div class="grid-3">
                        @foreach($suggestions as $product)
                            <div class="card">
                                <img src="{{ optional($product->primaryImage)->path ? asset('storage/' . $product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" class="product-image">
                                <h3>{{ $product->name }}</h3>
                                <p class="text-muted">UGX{{ number_format($product->price, 0) }}</p>
                                <a class="btn btn-secondary" href="{{ route('shop.show', $product->slug) }}">View</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </div>

    <!-- Edit Modal -->
    <div id="editModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div class="card" style="max-width:500px;width:90%;background:#fff;">
            <h2>Edit Item</h2>
            <form id="editForm" method="POST" style="display:grid;gap:12px;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="old_color" id="old_color">
                <input type="hidden" name="old_size" id="old_size">
                <div id="editFormContent"></div>
                <div style="display:flex;gap:10px;">
                    <button class="btn" type="submit">Update Item</button>
                    <button class="btn btn-secondary" type="button" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    const cartItemsData = @json($availableItems->concat($outOfStockItems)->values());
    
    function openEditModal(productId, color, size) {
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        form.action = `{{ route('cart.update', '__PRODUCT__') }}`.replace('__PRODUCT__', productId);
        
        const item = cartItemsData.find(i => i.product.id == productId && i.color === (color || null) && i.size === (size || null));
        
        if (!item) {
            console.error('Item not found');
            return;
        }
        
        document.getElementById('old_color').value = color || '';
        document.getElementById('old_size').value = size || '';
        
        let formContent = `
            <div>
                <label>Quantity</label>
                <input class="input" type="number" name="quantity" value="${item.quantity}" min="1" max="${item.product.stock}" required>
                <p style="font-size:0.85rem;color:#6b7280;margin-top:4px;">Max: ${item.product.stock}</p>
            </div>
        `;
        
        if (item.product.colors && item.product.colors.length > 0) {
            formContent += `
                <div>
                    <label>Color</label>
                    <select class="input" name="color">
                        <option value="">None</option>
                        ${item.product.colors.map(c => `<option value="${c}" ${item.color === c ? 'selected' : ''}>${c}</option>`).join('')}
                    </select>
                </div>
            `;
        }
        
        if (item.product.sizes && item.product.sizes.length > 0) {
            formContent += `
                <div>
                    <label>Size</label>
                    <select class="input" name="size">
                        <option value="">None</option>
                        ${item.product.sizes.map(s => `<option value="${s}" ${item.size === s ? 'selected' : ''}>${s}</option>`).join('')}
                    </select>
                </div>
            `;
        }
        
        document.getElementById('editFormContent').innerHTML = formContent;
        modal.style.display = 'flex';
    }
    
    function closeEditModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
    </script>
@endsection