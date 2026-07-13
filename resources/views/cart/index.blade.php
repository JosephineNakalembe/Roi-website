@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
    <div class="sticky-header">
        <div class="header-content">
            <h1 class="mb-0">Shopping Cart</h1>
        </div>
    </div>
    <div class="card card-centered">
        <style>
            .cart-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 14px;
                border: 1px solid #e5e7eb;
                border-radius: 12px;
                background: #fff;
                position: relative;
            }
            @media (max-width: 768px) {
                .cart-item {
                    gap: 8px;
                    padding: 10px;
                }
                .cart-item img {
                    width: 60px !important;
                    height: 60px !important;
                }
                .cart-item h3 {
                    font-size: 0.85rem !important;
                }
                .cart-item p {
                    font-size: 0.7rem !important;
                    margin: 2px 0 !important;
                }
                .cart-item strong {
                    font-size: 0.9rem !important;
                }
                .cart-item .btn-gray {
                    font-size: 0.8rem !important;
                    min-width: 40px !important;
                    padding: 3px 6px !important;
                }
                .cart-item svg {
                    width: 16px !important;
                    height: 16px !important;
                }
            }
        </style>
        @if($availableItems->isEmpty() && $outOfStockItems->isEmpty())
            <p>Your cart is empty. <a href="{{ route('shop.index') }}">Continue shopping</a>.</p>
        @else
            @php
                $allSelected = $availableItems->every(fn($item) => $item['selected']);
            @endphp
            <!-- Select All Controls -->
            @if($availableItems->isNotEmpty())
                <div class="flex-between flex-gap-large" style="padding:12px 14px;background:#f9fafb;border-radius:12px;margin-bottom:16px;border:1px solid #e5e7eb;">
                    <form method="POST" action="{{ route('cart.toggle-select-all') }}" id="selectAllForm" class="flex-between flex-gap-medium">
                        @csrf
                        <input type="hidden" name="selected" value="{{ $allSelected ? '0' : '1' }}">
                        <label class="flex-between flex-gap-small" style="cursor:pointer;font-size:0.95rem;">
                            <input type="checkbox" onchange="this.closest('form').submit();" {{ $allSelected ? 'checked' : '' }} class="checkbox" style="accent-color:#1a1a2e;">
                            <strong>Select All ({{ $availableItems->count() }} items)</strong>
                        </label>
                    </form>
                </div>
            @endif

            @if($availableItems->isNotEmpty())
                <div class="form-grid" style="gap:14px;">
                    @foreach($availableItems as $item)
                        @php
                            $lowStock = $item['product']->stock <= 2;
                            $colorParts = explode(':', $item['color'] ?? '');
                            $colorDisplayName = $colorParts[1] ?? $item['color'] ?? '';
                        @endphp
                        <div class="cart-item">
                            <!-- Checkbox -->
                            <form method="POST" action="{{ route('cart.toggle-select') }}" class="flex-shrink">
                                @csrf
                                <input type="hidden" name="cart_item_id" value="{{ $item['cart_key'] }}">
                                <input type="hidden" name="selected" value="{{ $item['selected'] ? '0' : '1' }}">
                                <input type="checkbox" onchange="this.closest('form').submit();" {{ $item['selected'] ? 'checked' : '' }} class="checkbox" style="accent-color:#1a1a2e;">
                            </form>
                            <!-- Product Image -->
                            <div onclick="openEditModal('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}')" style="width:80px;height:80px;border-radius:10px;overflow:hidden;background:#f3f4f6;cursor:pointer;flex-shrink:0;">
                                <img src="{{ optional($item['product']->primaryImage)->path ? asset('storage/' . $item['product']->primaryImage->path) : 'https://via.placeholder.com/200x200' }}" alt="{{ $item['product']->name }}" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <!-- Details -->
                            <div onclick="openEditModal('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}')" style="flex:1;min-width:0;cursor:pointer;">
                                <h3 style="font-size:1rem;margin:0 0 4px 0;font-weight:600;">{{ $item['product']->name }}</h3>
                                @if($colorDisplayName)
                                    <p style="font-size:0.85rem;color:#6b7280;margin:0 0 4px 0;">Color: {{ $colorDisplayName }}</p>
                                @endif
                                @if($item['size'])
                                    <p style="font-size:0.85rem;color:#6b7280;margin:0 0 4px 0;">Size: {{ $item['size'] }}</p>
                                @endif
                                <strong style="font-size:1rem;">UGX{{ number_format($item['unit_price'] ?? $item['product']->price, 0) }}</strong>
                                <p class="text-muted" style="font-size:0.75rem;margin:2px 0 0;">{{ $item['quantity'] }} × UGX{{ number_format($item['unit_price'] ?? $item['product']->price, 0) }}</p>
                                @if($lowStock)
                                    <p style="font-size:0.8rem;color:#dc2626;font-weight:600;margin:4px 0 0;">
                                        Only {{ $item['product']->stock }} left
                                    </p>
                                @endif
                            </div>
                            <!-- Right Side: Dustbin and Quantity -->
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px;flex-shrink:0;">
                                <!-- Dustbin Icon -->
                                <button type="button" onclick="confirmRemove('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}')" style="background:none;border:none;cursor:pointer;padding:4px;">
                                    <svg style="width:20px;height:20px;stroke:#dc2626;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <!-- Quantity -->
                                <div style="position:relative;">
                                    <button type="button" onclick="toggleQuantityDropdown('{{ $item['cart_key'] }}')" class="btn-gray" style="font-weight:700;font-size:0.9rem;min-width:50px;padding:4px 8px;">
                                        {{ $item['quantity'] }}
                                    </button>
                                    <div id="quantityDropdown_{{ $item['cart_key'] }}" style="display:none;position:absolute;top:100%;right:0;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:100;min-width:120px;max-height:250px;overflow-y:auto;margin-top:4px;padding:8px;">
                                        <div style="padding:4px 0 8px 0;border-bottom:1px solid #e5e7eb;margin-bottom:8px;">
                                            <input type="number" min="1" max="{{ $item['product']->stock }}" value="{{ $item['quantity'] }}" id="quantityInput_{{ $item['cart_key'] }}" class="input-small" style="width:100%;" placeholder="Enter qty">
                                            <button type="button" onclick="updateQuantityFromInput('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}', '{{ $item['cart_key'] }}')" class="btn-blue btn-full" style="margin-top:6px;font-weight:600;">Update</button>
                                        </div>
                                        <div style="font-size:0.75rem;color:#6b7280;margin-bottom:4px;">Quick select:</div>
                                        @for($i = 1; $i <= min($item['product']->stock, 10); $i++)
                                            <button type="button" onclick="updateQuantity('{{ $item['product']->id }}', '{{ $item['color'] ?? '' }}', '{{ $item['size'] ?? '' }}', {{ $i }})" class="btn-outline btn-full" style="text-align:left;font-size:0.9rem;transition:background 0.15s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'">
                                                {{ $i }}
                                            </button>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Subtotal & Checkout -->
                <div class="flex-between flex-gap-large flex-wrap" style="margin-top:20px;padding:16px;background:#f9fafb;border-radius:14px;border:1px solid #e5e7eb;">
                    <div>
                        <p class="text-muted" style="font-size:0.9rem;margin:0;">Total items ({{ $totalQuantity }})</p>
                        <h2 style="margin:4px 0 0;">UGX{{ number_format($subtotal, 0) }}</h2>
                    </div>
                    <div class="flex-row flex-gap-small">
                        <a class="btn btn-secondary" href="{{ route('shop.index') }}">Continue Shopping</a>
                        <a class="btn" href="{{ route('checkout.show') }}" style="{{ $subtotal > 0 ? '' : 'opacity:0.5;pointer-events:none;' }}">Checkout</a>
                    </div>
                </div>
            @endif

            @if($outOfStockItems->isNotEmpty())
                <div class="card-error" style="margin-top:30px;padding:18px;">
                    <h2 style="margin-bottom:14px;color:#b91c1c;">Out of stock</h2>
                    <div class="form-grid" style="gap:14px;">
                        @foreach($outOfStockItems as $item)
                            <div class="card-error" style="padding:12px;background:#fff7f7;">
                                <h3>{{ $item['product']->name }}</h3>
                                <p class="text-muted">This item is no longer available.</p>
                                <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="color" value="{{ $item['color'] ?? '' }}">
                                    <input type="hidden" name="size" value="{{ $item['size'] ?? '' }}">
                                    <button class="btn btn-red btn-small">Remove</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($suggestedCategories->isNotEmpty())
                <div class="card-success" style="margin-top:32px;padding:16px;">
                    <h2 style="font-size:1.1rem;margin-bottom:10px;color:#166534;">You may also like</h2>
                    <p style="font-size:0.85rem;color:#15803d;margin-bottom:10px;">Popular categories based on what customers are searching for</p>
                    <div class="flex-row flex-gap-small flex-wrap" style="margin-bottom:14px;">
                        @foreach($suggestedCategories as $sCat)
                            <a href="{{ route('shop.index', ['category' => $sCat->slug]) }}" class="btn-green btn-small" style="border-radius:999px;font-weight:500;transition:all 0.2s;">{{ $sCat->name }}</a>
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

    function confirmRemove(productId, color, size) {
        if (confirm('Are you sure you want to delete this item?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('cart.remove', '__PRODUCT__') }}`.replace('__PRODUCT__', productId);
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('input[name="_token"]').value;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const colorInput = document.createElement('input');
            colorInput.type = 'hidden';
            colorInput.name = 'color';
            colorInput.value = color || '';
            
            const sizeInput = document.createElement('input');
            sizeInput.type = 'hidden';
            sizeInput.name = 'size';
            sizeInput.value = size || '';
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            form.appendChild(colorInput);
            form.appendChild(sizeInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
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

    function toggleQuantityDropdown(cartKey) {
        const dropdown = document.getElementById('quantityDropdown_' + cartKey);
        const isVisible = dropdown.style.display === 'block';

        // Close all other dropdowns
        document.querySelectorAll('[id^="quantityDropdown_"]').forEach(dd => {
            dd.style.display = 'none';
        });

        // Toggle current dropdown
        dropdown.style.display = isVisible ? 'none' : 'block';
    }

    function updateQuantity(productId, color, size, quantity) {
        const form = document.getElementById('editForm');
        form.action = `{{ route('cart.update', '__PRODUCT__') }}`.replace('__PRODUCT__', productId);

        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('_method', 'PATCH');
        formData.append('quantity', quantity);
        formData.append('old_color', color || '');
        formData.append('old_size', size || '');
        formData.append('color', color || '');
        formData.append('size', size || '');

        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(() => {
            window.location.reload();
        });
    }

    function updateQuantityFromInput(productId, color, size, cartKey) {
        const input = document.getElementById('quantityInput_' + cartKey);
        const quantity = parseInt(input.value);

        if (quantity < 1 || isNaN(quantity)) {
            alert('Please enter a valid quantity (minimum 1)');
            return;
        }

        updateQuantity(productId, color, size, quantity);
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[onclick*="toggleQuantityDropdown"]') && !e.target.closest('[id^="quantityDropdown_"]')) {
            document.querySelectorAll('[id^="quantityDropdown_"]').forEach(dd => {
                dd.style.display = 'none';
            });
        }
    });
    </script>
@endsection