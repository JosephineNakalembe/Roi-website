@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Checkout</h1>
        </div>
    </div>
    <div class="card" style="max-width:900px;margin:0 auto;">
        <style>
            .checkout-container {
                grid-template-columns: 1fr !important;
            }
            
            #orderSummarySlider {
                scrollbar-width: thin;
                scrollbar-color: #000 #f3f4f6;
            }
            
            #orderSummarySlider::-webkit-scrollbar {
                height: 6px;
            }
            
            #orderSummarySlider::-webkit-scrollbar-track {
                background: #f3f4f6;
                border-radius: 3px;
            }
            
            #orderSummarySlider::-webkit-scrollbar-thumb {
                background: #000;
                border-radius: 3px;
            }
            
            #orderSummarySlider::-webkit-scrollbar-thumb:hover {
                background: #374426;
            }
            
            /* Desktop: show 6 cards at a time */
            .carousel-card {
                flex: 0 0 calc((100% - 60px) / 6) !important;
                max-width: calc((100% - 60px) / 6) !important;
            }
            
            @media (max-width: 768px) {
                /* Mobile: show 3 cards at a time - narrower width */

                header {
                     width: 100%;
                     padding-left: 0;
                     padding-right: 0;
                }
                .carousel-card {
                    flex: 0 0 calc((100% - 16px) / 3) !important;
                    max-width: calc((100% - 16px) / 3) !important;
                    padding: 6px !important;
                }
                
                #orderSummarySlider {
                    padding-bottom: 6px;
                    max-height: 150px;
                    margin-bottom: 10px;
                    gap:6px;
                }
                
                /* Make cards more compact on mobile */
                .carousel-card img {
                    height: 80px !important;
                    margin-bottom: 2px !important;
                    border-radius: 4px !important;
                }
                
                .carousel-card p {
                    margin: 1px 0 !important;
                    padding: 6px;
                }
                
                .carousel-card p[style*="font-size:0.75rem"] {
                    font-size: 0.55rem !important;
                    font-weight: 600 !important;
                    margin: 1px 0 !important;
                }
                
                .carousel-card p[style*="font-size:0.7rem"] {
                    font-size: 0.55rem !important;
                    margin: 1px 0 !important;
                }
                
                .carousel-card p[style*="font-size:0.65rem"] {
                    font-size: 0.5rem !important;
                    margin: 1px 0 !important;
                }
                
                .carousel-card p[style*="font-size:0.85rem"] {
                    font-size: 0.6rem !important;
                    margin: 2px 0 0 0 !important;
                    font-weight: 700 !important;
                }
                
                /* Show smaller navigation buttons on mobile */
                .carousel-btn {
                    display: flex !important;
                    width: 26px !important;
                    height: 26px !important;
                    font-size: 0.9rem !important;
                }
                
                /* Adjust button position for smaller screens */
                .carousel-btn:first-child {
                    left: -5px !important;
                }
                
                .carousel-btn:last-child {
                    right: -5px !important;
                }
                
                /* Make shipping form font bigger on mobile */
                /*.checkout-container label {
                    font-size: 0.9rem !important;
                    font-weight: 600 !important;
                    margin-bottom: 4px !important;
                }
                
                .checkout-container input,
                .checkout-container textarea {
                    font-size: 0.9rem !important;
                    padding: 8px 10px !important;
                }
                
                .checkout-container h2 {
                    font-size: 1rem !important;
                    font-weight: 700 !important;
                }*/
                
                /* Ensure mobile nav is full width from edge to edge */
                header {
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }
                
                /* Add padding to nav content so icons don't touch screen edges */
                .md\\:hidden {
                    padding-left: 12px !important;
                    padding-right: 12px !important;
                }
            }
            
            @media (max-width: 400px) {
                /* Very small mobile: show 3 cards at a time */
                .carousel-card {
                    flex: 0 0 calc((100% - 24px) / 3) !important;
                    max-width: calc((100% - 24px) / 3) !important;
                }
                
                .carousel-btn {
                    width: 32px !important;
                    height: 32px !important;
                    font-size: 1.1rem !important;
                }
                
                .carousel-btn:first-child {
                    left: 2px !important;
                }
                
                .carousel-btn:last-child {
                    right: 2px !important;
                }
            }
        </style>
        <div class="checkout-container" style="display:grid;gap:24px;">
            <div style="background:#f9fafb;border-radius:16px;padding:18px;">
                <h2>Order summary</h2>
                <div style="margin-top:16px;position:relative;">
                    <div style="display:flex;gap:12px;overflow-x:auto;padding-bottom:12px;margin-bottom:16px;scrollbar-width:thin;scroll-behavior:smooth;" id="orderSummarySlider">
                        @foreach($items as $item)
                            @php
                                $colorParts = explode(':', $item['color'] ?? '');
                                $colorDisplayName = $colorParts[1] ?? ($item['color'] ?? '');
                            @endphp
                            <div class="carousel-card" style="flex:0 0 auto;width:100px;text-align:center;background:#fff;padding:10px;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                                <img src="{{ optional($item['product']->primaryImage)->path ? asset('storage/' . $item['product']->primaryImage->path) : 'https://via.placeholder.com/200x200' }}"
                                     alt="{{ $item['product']->name }}"
                                     style="width:100%;height:110px;object-fit:cover;border-radius:8px;margin-bottom:8px;">
                                <p style="font-size:0.75rem;font-weight:600;margin:4px 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['product']->name }}</p>
                                <p style="font-size:0.7rem;color:#6b7280;margin:2px 0;">Qty: {{ $item['quantity'] }}</p>
                                @if($colorDisplayName || $item['size'])
                                    <p style="font-size:0.65rem;color:#6b7280;margin:2px 0;">
                                        @if($colorDisplayName)<span>{{ $colorDisplayName }}</span>@endif
                                        @if($item['size'])<span> • {{ $item['size'] }}</span>@endif
                                    </p>
                                @endif
                                <p style="font-size:0.85rem;font-weight:700;color:#1a1a2e;margin:6px 0 0;">UGX{{ number_format($item['total'], 0) }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if($items->count() > 1)
                        <button type="button" class="carousel-btn" onclick="scrollCarousel(-1)" style="position:absolute;left:-10px;top:50%;transform:translateY(-50%);background:#000;color:#fff;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.2);">‹</button>
                        <button type="button" class="carousel-btn" onclick="scrollCarousel(1)" style="position:absolute;right:-10px;top:50%;transform:translateY(-50%);background:#000;color:#fff;border:none;border-radius:50%;width:36px;height:36px;cursor:pointer;font-size:1.2rem;display:flex;align-items:center;justify-content:center;z-index:10;box-shadow:0 2px 8px rgba(0,0,0,0.2);">›</button>
                    @endif
                </div>

                <hr style="margin:18px 0;">
                <div style="display:flex;justify-content:space-between;">
                    <span>Subtotal</span><strong>UGX{{ number_format($subtotal, 0) }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span>Shipping</span><strong id="shippingDisplay">—</strong>
                </div>
                <hr style="margin:18px 0;">
                <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;">
                    <span>Total</span><strong id="totalDisplay">UGX{{ number_format($subtotal, 0) }}</strong>
                </div>
            </div>
            <div>
                <form method="POST" action="{{ route('checkout.process') }}">
                    @csrf

                    <h2>Shipping Details</h2>

                    <label>Full Name</label>
                    <input class="input" name="shipping_name" value="{{ old('shipping_name', Auth::user()->name) }}" required placeholder="Enter your full name" style="margin-bottom:12px;">

                    <label>Phone Number</label>
                    <input class="input" name="shipping_phone" value="{{ old('shipping_phone', Auth::user()->phone) }}" required placeholder="e.g. 0772123456" style="margin-bottom:12px;">

                    <div style="position:relative;margin-bottom:12px;">
                        <label for="deliveryAreaInput">Delivery Area</label>
                        <input type="text" id="deliveryAreaInput" class="input" placeholder="Type delivery area..." autocomplete="off" value="{{ old('delivery_area') }}">
                        <input type="hidden" name="delivery_area" id="deliveryAreaHidden" value="{{ old('delivery_area') }}">
                        <div id="deliveryAreaDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #d1d5db;border-radius:10px;max-height:220px;overflow-y:auto;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                        </div>
                        <p id="deliveryAreaError" style="display:none;color:#dc2626;font-size:0.85rem;font-weight:600;margin-top:4px;">Area Out of Delivery Scope</p>
                    </div>

                    <label>Address Line</label>
                    <input class="input" name="address_line" value="{{ old('address_line') }}" required placeholder="e.g. Plot 7, Lumumba Avenue" style="margin-bottom:12px;">

                    <div style="margin-bottom:16px;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 0;">
                            <div onclick="event.preventDefault();" style="position:relative;">
                                <input type="checkbox" name="save_default" value="1" id="saveDefaultToggle" style="display:none;">
                                <div id="toggleSwitch" style="width:44px;height:24px;border-radius:12px;background:#d1d5db;position:relative;cursor:pointer;transition:background 0.2s;" onclick="document.getElementById('saveDefaultToggle').checked = !document.getElementById('saveDefaultToggle').checked; this.style.background = document.getElementById('saveDefaultToggle').checked ? '#111' : '#d1d5db'; this.querySelector('div').style.transform = document.getElementById('saveDefaultToggle').checked ? 'translateX(20px)' : 'translateX(2px)';">
                                    <div style="width:20px;height:20px;border-radius:50%;background:#fff;position:absolute;top:2px;left:2px;transition:transform 0.2s;box-shadow:0 1px 3px rgba(0,0,0,0.2);"></div>
                                </div>
                            </div>
                            <span style="font-size:0.9rem;color:#374151;user-select:none;">Save as default for future orders</span>
                        </label>
                    </div>

                    <h2>Payment Method</h2>
                    <div style="padding:14px;border:1px solid #e5e7eb;border-radius:12px;background:#f9fafb;margin-bottom:16px;display:flex;align-items:center;gap:12px;">
                        <span style="font-size:1.2rem;">💵</span>
                        <div>
                            <strong style="display:block;">Cash on Delivery (COD)</strong>
                            <span style="font-size:0.85rem;color:#6b7280;">Pay when you receive your order (Pay with Mobile Money using Airtel or MTN)</span>
                        </div>
                    </div>

                    <label>Order Notes (optional)</label>
                    <textarea class="input" name="notes" rows="3" placeholder="Any special instructions" style="margin-bottom:16px;">{{ old('notes') }}</textarea>

                    <button class="btn" type="submit" style="width:100%;padding:14px;font-size:1rem;font-weight:600;">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const deliveryAreas = @json($deliveryAreas);
        const subtotal = {{ $subtotal }};
        const deliveryAreaInput = document.getElementById('deliveryAreaInput');
        const deliveryAreaHidden = document.getElementById('deliveryAreaHidden');
        const deliveryAreaDropdown = document.getElementById('deliveryAreaDropdown');
        const deliveryAreaError = document.getElementById('deliveryAreaError');
        const shippingDisplay = document.getElementById('shippingDisplay');
        const totalDisplay = document.getElementById('totalDisplay');

        function updateShippingAndTotal(area) {
            const price = deliveryAreas[area];
            if (price) {
                shippingDisplay.textContent = 'UGX' + price.toLocaleString('en-US');
                const total = subtotal + price;
                totalDisplay.textContent = 'UGX' + Math.round(total).toLocaleString('en-US');
                deliveryAreaError.style.display = 'none';
                deliveryAreaInput.style.borderColor = '#d1d5db';
            }
        }

        function showDropdown(filterText) {
            const lowerFilter = filterText.toLowerCase();
            const entries = Object.entries(deliveryAreas);
            const filtered = lowerFilter ? entries.filter(([area]) => area.toLowerCase().includes(lowerFilter)) : entries;

            if (filtered.length === 0) {
                deliveryAreaDropdown.style.display = 'none';
                if (filterText.length > 0 && !deliveryAreaHidden.value) {
                    deliveryAreaError.style.display = 'block';
                    deliveryAreaInput.style.borderColor = '#dc2626';
                }
                return;
            }

            deliveryAreaError.style.display = 'none';
            deliveryAreaDropdown.style.display = 'block';
            deliveryAreaInput.style.borderColor = '#d1d5db';

            deliveryAreaDropdown.innerHTML = filtered.map(([area, price]) =>
                `<div style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;background:#fff;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='#fff'" onclick="selectArea('${area.replace(/'/g, "\\'")}')">
                    <span>${area}</span>
                    <span style="color:#6b7280;font-size:0.85rem;">UGX${price.toLocaleString('en-US')}</span>
                </div>`
            ).join('');
        }

        function selectArea(area) {
            deliveryAreaInput.value = area;
            deliveryAreaHidden.value = area;
            deliveryAreaDropdown.style.display = 'none';
            deliveryAreaError.style.display = 'none';
            deliveryAreaInput.style.borderColor = '#d1d5db';
            updateShippingAndTotal(area);
        }

        deliveryAreaInput.addEventListener('input', function() {
            deliveryAreaHidden.value = '';
            showDropdown(this.value);
        });

        deliveryAreaInput.addEventListener('focus', function() {
            if (!deliveryAreaHidden.value) {
                showDropdown(this.value);
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#deliveryAreaInput') && !e.target.closest('#deliveryAreaDropdown') && !e.target.closest('#toggleSwitch')) {
                deliveryAreaDropdown.style.display = 'none';
                if (deliveryAreaInput.value && !deliveryAreaHidden.value) {
                    deliveryAreaError.style.display = 'block';
                    deliveryAreaInput.style.borderColor = '#dc2626';
                }
            }
        });

        // Preserve old value if validation failed
        const oldArea = '{{ old('delivery_area') }}';
        if (oldArea && deliveryAreas[oldArea]) {
            deliveryAreaInput.value = oldArea;
            deliveryAreaHidden.value = oldArea;
            updateShippingAndTotal(oldArea);
        }

        // Carousel scroll function
        function scrollCarousel(direction) {
            const slider = document.getElementById('orderSummarySlider');
            if (!slider) return;
            
            const cardWidth = 142; // card width (130px) + gap (12px)
            const scrollAmount = cardWidth * 2; // scroll 2 cards at a time
            
            slider.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }

        // Hide carousel buttons when at edges
        function updateCarouselButtons() {
            const slider = document.getElementById('orderSummarySlider');
            if (!slider || slider.children.length <= 1) return;
            
            // Buttons visibility is handled by CSS and the conditional in Blade template
            // This function can be extended for edge detection if needed
        }

        // Update carousel on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('orderSummarySlider');
            if (slider) {
                slider.addEventListener('scroll', updateCarouselButtons);
                updateCarouselButtons();
            }
        });
    </script>
@endsection