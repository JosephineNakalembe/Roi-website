@extends('layouts.app')

@section('content')
    @php
        // Build the media list with sorting by order
        $allMedia = $product->images->sortBy('order')->values();

        // Group images by color (only images, not videos)
        $colorImageMap = [];
        foreach ($allMedia as $media) {
            if ($media->color) {
                $colorImageMap[$media->color][] = [
                    'path' => media_url($media->path),
                    'media_type' => $media->media_type,
                ];
            }
        }

        // General media (no specific color) + videos act as a shared fallback gallery
        $generalMedia = [];
        foreach ($allMedia as $media) {
            if (!$media->color) {
                $generalMedia[] = [
                    'path' => media_url($media->path),
                    'media_type' => $media->media_type,
                ];
            }
        }

        $colors = $product->colors ?? [];
        $colorPrices = $product->color_prices ?? [];

        // Default selected color = first color that has its own images, else first color
        $defaultColor = null;
        if (!empty($colors)) {
            foreach ($colors as $c) {
                if (isset($colorImageMap[$c])) {
                    $defaultColor = $c;
                    break;
                }
            }
            if ($defaultColor === null) {
                $defaultColor = $colors[0];
            }
        }
    @endphp

    <!-- Sticky Search Bar -->
    <div class="sticky-header-high" style="margin-bottom:20px;">
        <div class="header-content">
            @include('partials.back-button')
            <form method="GET" action="{{ route('shop.index') }}" style="display:flex;gap:8px;flex:1;max-width:600px;margin:0 auto;">
                <div style="position:relative;flex:1;">
                    <input class="input" type="search" name="search" placeholder="Search products..." style="width:100%;padding-right:40px;">
                </div>
            </form>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <style>
                @media (max-width: 768px) {
                    .product-detail-container {
                        grid-template-columns: 1fr !important;
                    }
                    .header-content form {
                        max-width: 80%;
                        margin: 0 auto;
                    }
                    .header-content form input[type="search"] {
                        font-size: 0.75rem !important;
                        padding: 8px 32px 8px 12px !important;
                    }
                    .header-content form button {
                        width: 24px !important;
                        height: 24px !important;
                    }
                    .header-content form button svg {
                        width: 16px !important;
                        height: 16px !important;
                    }
                    .product-detail-container h1 {
                        font-size: 0.875rem !important;
                    }
                    .product-detail-container p {
                        font-size: 0.875rem !important;
                    }
                    .product-detail-container label {
                        font-size: 0.875rem !important;
                    }
                    .product-detail-container .size-pill {
                        font-size: 0.875rem !important;
                    }
                    #productPrice {
                        font-size: 1.1rem !important;
                        font-weight: 700 !important;
                    }
                    .product-detail-container h3 {
                        font-size: 0.875rem !important;
                    }
                }
            </style>
            <div class="product-detail-container" style="display:grid;grid-template-columns:1.1fr 0.9fr;gap:24px;align-items:stretch;">
                <div>
                    <!-- Product Media Slideshow -->
                    @if($allMedia->count() > 0)
                        <div style="position:relative;">
                            <div id="mediaSlideshow" style="position:relative;width:100%;aspect-ratio:1;overflow:hidden;border-radius:8px;background:#f3f4f6;">
                                <!-- Slide Number Indicator -->
                                <div id="slideIndicator" style="position:absolute;bottom:8px;left:8px;background:rgba(0,0,0,0.6);color:#fff;padding:4px 8px;border-radius:4px;font-size:0.85rem;z-index:10;">1/{{ $allMedia->count() }}</div>
                            </div>

                            <!-- Navigation Arrows -->
                            <button type="button" onclick="changeSlide(-1)" id="prevBtn" style="position:absolute;top:50%;left:12px;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;font-size:1.3rem;display:none;align-items:center;justify-content:center;z-index:10;">&lt;</button>
                            <button type="button" onclick="changeSlide(1)" id="nextBtn" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;font-size:1.3rem;display:none;align-items:center;justify-content:center;z-index:10;">&gt;</button>
                        </div>
                    @else
                        <img src="https://via.placeholder.com/720x720" alt="{{ $product->name }}" class="product-image">
                    @endif
                </div>
                <div>
                    <h1 style="font-size:0.975rem;">{{ $product->name }}</h1>
                    <p class="text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                    <p id="productPrice" style="font-size:1.2rem;font-weight:700;">UGX{{ number_format($product->priceForColor($defaultColor), 0) }}</p>
                    <p style="font-size:0.975rem;">{{ $product->description }}</p>

                    @if($product->stock > 0 && $product->stock <= 2)
                        <p style="color:#dc2626;font-weight:600;margin-top:4px;font-size:0.975rem;">
                            Only {{ $product->stock }} left in stock!
                        </p>
                    @endif

                    @if($product->stock > 0)
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <p style="margin-top:16px;color:#4b5563;font-size:0.975rem;">Admin users cannot add products to the cart. Use the admin dashboard for inventory and order management.</p>
                        @else
                            <form method="POST" action="{{ route('cart.add', $product) }}" style="margin-top:16px;display:grid;gap:12px;">
                                @csrf
                                @if(!empty($colors))
                                    <div>
                                        <label style="display:block;margin-bottom:8px;font-size:0.975rem;">Color</label>
                                        <div id="colorButtons" style="display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-start;">
                                            @foreach($colors as $color)
                                                @php
                                                    // Parse color to get hex code and name
                                                    $colorParts = explode(':', $color);
                                                    $hexCode = $colorParts[0] ?? '#000000';
                                                    $colorName = $colorParts[1] ?? $color;
                                                @endphp
                                                <button type="button"
                                                    class="color-pill"
                                                    data-color="{{ $color }}"
                                                    data-hex="{{ $hexCode }}"
                                                    onclick="selectColor('{{ addslashes($color) }}')"
                                                    style="cursor:pointer;border:none;background:transparent;padding:0;min-width:40px;height:40px;transition:all 0.15s;display:flex;align-items:center;justify-content:center;"
                                                    title="{{ $colorName }}">
                                                    <span style="width:32px;height:32px;background:{{ $hexCode }};border-radius:6px;display:block;"></span>
                                                </button>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="color" id="selectedColor" value="{{ $defaultColor }}" required>
                                    </div>
                                @endif
                                @if($product->sizes && count($product->sizes) > 0)
                                    <div>
                                        <label style="font-size:0.975rem;">Size</label>
                                        <div id="sizeButtons" style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @foreach($product->sizes as $size)
                                                <button type="button"
                                                    class="size-pill"
                                                    data-size="{{ $size }}"
                                                    onclick="selectSize('{{ $size }}')"
                                                    style="cursor:pointer;border:2px solid #d1d5db;background:#f1f3f5;color:#000;border-radius:8px;padding:8px 16px;font-size:0.975rem;font-weight:600;transition:all 0.15s;">
                                                    {{ $size }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="size" id="selectedSize" value="{{ $product->sizes[0] ?? '' }}" required>
                                    </div>
                                @endif
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                                    <div style="flex:1;min-width:140px;">
                                        <label style="font-size:0.975rem;">Quantity</label>
                                        <input class="input" type="number" min="1" max="{{ $product->stock }}" name="quantity" value="1" required>
                                    </div>
                                    <button class="btn" type="submit">Add to Cart</button>
                                </div>
                            </form>
                            <form method="POST" action="{{ route('wishlist.toggle', $product) }}" style="margin-top:12px;">
                                @csrf
                                <button class="btn btn-secondary" type="submit">{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}</button>
                            </form>
                        @endif
                    @else
                        <p style="color:#dc2626;font-weight:600;margin-top:16px;font-size:0.975rem;">Out of Stock</p>
                    @endif

                    <!-- Reviews Section -->
                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e9ecef;">
                        <button type="button" onclick="toggleReviews()" class="btn btn-outline" style="width:100%;display:flex;justify-content:space-between;align-items:center;">
                            <span>Customer Reviews</span>
                            @if($reviews->isNotEmpty())
                                <span>⭐ {{ number_format($avgRating, 1) }} ({{ $reviewCount }})</span>
                            @else
                                <span>No reviews yet</span>
                            @endif
                        </button>
                        <div id="reviewsContent" style="display:none;margin-top:16px;">
                            @if($reviews->isNotEmpty())
                                <div style="display:grid;gap:12px;">
                                    @foreach($reviews as $review)
                                        <div style="padding:12px;background:#f8f9fa;border-radius:10px;border:1px solid #e9ecef;">
                                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                                                <strong style="font-size:0.975rem;">{{ $review->user?->name ?? 'Anonymous' }}</strong>
                                                <span style="font-size:0.975rem;color:#f57f17;">{{ str_repeat('⭐', $review->rating) }}</span>
                                            </div>
                                            @if($review->comment)
                                                <p style="margin:6px 0 0;font-size:0.975rem;color:#495057;">{{ $review->comment }}</p>
                                            @endif
                                            <p style="margin:4px 0 0;font-size:0.975rem;color:#adb5bd;">{{ $review->created_at->format('M d, Y') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted" style="font-size:0.975rem;">No reviews yet for this product.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- You Might Like Section -->
            @if($suggestedProducts->isNotEmpty())
                <div style="margin-top:24px;padding-top:24px;border-top:1px solid #e5e7eb;">
                    <h2 style="margin-bottom:16px;font-size:0.975rem;">You Might Like</h2>
                    <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:16px;">
                        @foreach($suggestedProducts->take(4) as $sProduct)
                            <a href="{{ route('shop.show', $sProduct->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
                                <img src="{{ optional($sProduct->primaryImage)->path ? media_url($sProduct->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $sProduct->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;" loading="lazy">
                                <div style="padding:12px 14px 14px;">
                                    <h3 style="font-size:0.975rem;font-weight:600;margin-bottom:2px;">{{ $sProduct->name }}</h3>
                                    <p style="font-weight:700;font-size:1.2rem;">UGX{{ number_format($sProduct->price, 0) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

                        @if($product->size_guide)
                <div style="margin-top:24px;padding-top:24px;border-top:1px solid #e5e7eb;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                        <h3 style="margin:0;">Size Guide</h3>
                        <button id="unitToggle" onclick="toggleUnit()" style="padding:6px 14px;border-radius:999px;font-size:0.9rem;border:2px solid #1a1a2e;background:#fff;color:#1a1a2e;cursor:pointer;font-weight:600;transition:all 0.2s;">Switch to cm</button>
                    </div>
                    @php
                        $sizeGuide = null;
                        if (is_string($product->size_guide)) {
                            $decoded = json_decode($product->size_guide, true);
                            $sizeGuide = $decoded ? $decoded : null;
                        }
                        $guideType = $product->size_guide_type ?? 'clothing';
                        
                        // Clothing measurements
                        $measurementLabels = [
                            'waist' => 'Waist',
                            'hip' => 'Hip',
                            'length' => 'Length',
                            'inseam' => 'Inseam',
                            'thigh' => 'Thigh',
                            'burst' => 'Burst',
                            'shoulder' => 'Shoulder',
                        ];
                        $allSizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];
                        
                        // Shoe column labels
                        $shoeLabels = [
                            'baby' => 'US Baby',
                            'toddler' => 'US Toddler',
                            'kids' => 'US Kids',
                            'youth' => 'US Youth',
                            'mens' => "US Men's",
                            'womens' => "US Women's",
                            'uk' => 'UK',
                            'eu' => 'EU',
                            'cm' => 'CM',
                        ];
                    @endphp

                    @if($guideType === 'clothing' && is_array($sizeGuide))
                        @php
                            $sizesWithData = [];
                            foreach ($allSizes as $size) {
                                foreach (array_keys($measurementLabels) as $measurement) {
                                    if (isset($sizeGuide[$measurement][$size]) && $sizeGuide[$measurement][$size] !== '') {
                                        $sizesWithData[] = $size;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if(!empty($sizesWithData))
                            <div style="overflow-x:auto;">
                                <table id="clothingSizeTable" style="width:100%;border-collapse:collapse;font-size:1rem;margin-top:12px;">
                                    <thead>
                                        <tr style="background:#f3f4f6;">
                                            <th style="padding:12px;border:1px solid #e5e7eb;text-align:left;font-weight:600;">Measurement</th>
                                            @foreach($sizesWithData as $size)
                                                <th style="padding:12px;border:1px solid #e5e7eb;text-align:center;font-weight:600;">{{ $size }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($measurementLabels as $key => $label)
                                            @php
                                                $hasData = false;
                                                foreach ($sizesWithData as $size) {
                                                    if (isset($sizeGuide[$key][$size]) && $sizeGuide[$key][$size] !== '') {
                                                        $hasData = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if($hasData)
                                                <tr class="size-row" data-measurement="{{ $key }}">
                                                    <td style="padding:12px;border:1px solid #e5e7eb;font-weight:500;" class="measurement-label">{{ $label }} (<span class="unit-label">inches</span>)</td>
                                                    @foreach($sizesWithData as $size)
                                                        <td style="padding:12px;border:1px solid #e5e7eb;text-align:center;" class="size-value" data-value="{{ $sizeGuide[$key][$size] ?? '' }}">{{ $sizeGuide[$key][$size] ?? '-' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p style="white-space:pre-line;line-height:1.8;margin-top:12px;">{{ is_string($sizeGuide) ? $sizeGuide : '' }}</p>
                        @endif
                    @elseif($guideType === 'shoes' && is_array($sizeGuide) && isset($sizeGuide['shoes']))
                        @php
                            $shoeData = $sizeGuide['shoes'];
                            $colsWithData = [];
                            foreach ($shoeLabels as $col => $label) {
                                foreach ($shoeData as $row) {
                                    if (isset($row[$col]) && $row[$col] !== '') {
                                        $colsWithData[] = $col;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        @if(!empty($shoeData) && !empty($colsWithData))
                            <div style="overflow-x:auto;">
                                <table id="shoeSizeTable" style="width:100%;border-collapse:collapse;font-size:0.95rem;margin-top:12px;">
                                    <thead>
                                        <tr style="background:#f3f4f6;">
                                            @foreach($colsWithData as $col)
                                                <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;font-weight:600;">{{ $shoeLabels[$col] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($shoeData as $row)
                                            @php
                                                $hasAny = false;
                                                foreach ($colsWithData as $col) {
                                                    if (isset($row[$col]) && $row[$col] !== '') { $hasAny = true; break; }
                                                }
                                            @endphp
                                            @if($hasAny)
                                                <tr>
                                                    @foreach($colsWithData as $col)
                                                        <td style="padding:6px;border:1px solid #e5e7eb;text-align:center;" class="{{ $col === 'cm' ? 'size-value' : '' }}" data-value="{{ $row[$col] ?? '' }}">{{ $row[$col] ?? '-' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p style="white-space:pre-line;line-height:1.8;margin-top:12px;">{{ is_string($sizeGuide) ? $sizeGuide : '' }}</p>
                        @endif
                    @else
                        <p style="white-space:pre-line;line-height:1.8;margin-top:12px;">{{ is_string($sizeGuide) ? $sizeGuide : '' }}</p>
                    @endif
                </div>
            @endif

            <script>
            let usingInches = true;
            function toggleUnit() {
                const btn = document.getElementById('unitToggle');
                usingInches = !usingInches;
                btn.textContent = usingInches ? 'Switch to cm' : 'Switch to inches';
                
                // Update clothing table
                document.querySelectorAll('#clothingSizeTable .size-row').forEach(row => {
                    const unitLabel = row.querySelector('.unit-label');
                    if (unitLabel) unitLabel.textContent = usingInches ? 'inches' : 'cm';
                    
                    row.querySelectorAll('.size-value').forEach(td => {
                        const val = td.dataset.value;
                        if (val && !isNaN(parseFloat(val))) {
                            td.textContent = usingInches ? val : (parseFloat(val) * 2.54).toFixed(1);
                        }
                    });
                });
                
                // Update shoe table (CM column toggles to inches)
                document.querySelectorAll('#shoeSizeTable .size-value').forEach(td => {
                    const val = td.dataset.value;
                    if (val && !isNaN(parseFloat(val))) {
                        td.textContent = usingInches ? val : (parseFloat(val) * 2.54).toFixed(1);
                    }
                });
                
                // Update measurement labels unit
                document.querySelectorAll('.measurement-label .unit-label').forEach(el => {
                    el.textContent = usingInches ? 'inches' : 'cm';
                });
            }
            </script>
        </div>

    </div>

    <script>
        // Data passed from server
        const colorImageMap = @json($colorImageMap);
        const generalMedia = @json($generalMedia);
        const colorPrices = @json($colorPrices);
        const basePrice = {{ (float) $product->price }};
        let selectedColor = @json($defaultColor);

        let currentSlide = 0;
        let currentMedia = [];

        function formatPrice(value) {
            return 'UGX' + Math.round(value).toLocaleString('en-US');
        }

        // Determine which media to show for a given color
        function mediaForColor(color) {
            if (color && colorImageMap[color] && colorImageMap[color].length > 0) {
                // Color-specific images, plus any general videos appended
                const videos = generalMedia.filter(m => m.media_type === 'video');
                return colorImageMap[color].concat(videos);
            }
            // Fallback: all general media (or everything if none)
            if (generalMedia.length > 0) return generalMedia;
            // Last resort: flatten all color images
            let all = [];
            Object.keys(colorImageMap).forEach(c => { all = all.concat(colorImageMap[c]); });
            return all.concat(generalMedia);
        }

        function renderSlides() {
            const slideshow = document.getElementById('mediaSlideshow');
            const slideIndicator = document.getElementById('slideIndicator');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!slideshow) return;

            slideshow.innerHTML = '';
            currentSlide = 0;

            currentMedia.forEach((media, index) => {
                const slide = document.createElement('div');
                slide.className = 'slide';
                slide.style.display = index === 0 ? 'block' : 'none';
                slide.style.width = '100%';
                slide.style.height = '100%';

                if (media.media_type === 'video') {
                    slide.innerHTML = `<video controls style="width:100%;height:100%;object-fit:cover;"><source src="${media.path}" type="video/mp4">Your browser does not support the video tag.</video>`;
                } else {
                    slide.innerHTML = `<img src="${media.path}" alt="Product image" style="width:100%;height:100%;object-fit:cover;">`;
                }
                slideshow.appendChild(slide);
            });

            // Update slide indicator
            if (slideIndicator) {
                slideIndicator.textContent = `1/${currentMedia.length}`;
            }

            // Arrows only when more than one
            if (currentMedia.length > 1) {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
            } else {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }

        function showSlide(n) {
            const slides = document.querySelectorAll('#mediaSlideshow .slide');
            const slideIndicator = document.getElementById('slideIndicator');
            const total = slides.length;
            if (total === 0) return;

            if (n >= total) currentSlide = 0;
            else if (n < 0) currentSlide = total - 1;
            else currentSlide = n;

            slides.forEach(s => s.style.display = 'none');
            slides[currentSlide].style.display = 'block';

            // Update slide indicator
            if (slideIndicator) {
                slideIndicator.textContent = `${currentSlide + 1}/${total}`;
            }
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        function toggleReviews() {
            const reviewsContent = document.getElementById('reviewsContent');
            if (reviewsContent) {
                if (reviewsContent.style.display === 'none') {
                    reviewsContent.style.display = 'block';
                } else {
                    reviewsContent.style.display = 'none';
                }
            }
        }

        function selectColor(color) {
            selectedColor = color;

            // Update hidden field
            const hidden = document.getElementById('selectedColor');
            if (hidden) hidden.value = color;

            // Parse hex code from color string
            const colorParts = color.split(':');
            const hexCode = colorParts[0] || color;

            // Update pill styles
            document.querySelectorAll('.color-pill').forEach(btn => {
                if (btn.dataset.color === color) {
                    btn.querySelector('span').style.boxShadow = '0 0 0 2px #111';
                } else {
                    btn.querySelector('span').style.boxShadow = 'none';
                }
            });

            // Update price
            const priceEl = document.getElementById('productPrice');
            if (priceEl) {
                const price = (colorPrices[color] != null && colorPrices[color] !== '') ? parseFloat(colorPrices[color]) : basePrice;
                priceEl.textContent = formatPrice(price);
            }

            // Update slideshow for this color
            currentMedia = mediaForColor(color);
            renderSlides();
        }

        function selectSize(size) {
            // Update hidden field
            const hidden = document.getElementById('selectedSize');
            if (hidden) hidden.value = size;

            // Update pill styles
            document.querySelectorAll('.size-pill').forEach(btn => {
                if (btn.dataset.size === size) {
                    btn.style.background = '#f1f3f5';
                    btn.style.color = '#000';
                    btn.style.borderColor = '#111';
                } else {
                    btn.style.background = '#f1f3f5';
                    btn.style.color = '#000';
                    btn.style.borderColor = '#d1d5db';
                }
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            if (selectedColor) {
                selectColor(selectedColor);
            } else {
                currentMedia = mediaForColor(null);
                renderSlides();
            }

            // Initialize first size selection
            const firstSizeBtn = document.querySelector('.size-pill');
            if (firstSizeBtn) {
                selectSize(firstSizeBtn.dataset.size);
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
        });
    </script>
@endsection
