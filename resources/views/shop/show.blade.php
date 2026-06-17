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
                    'path' => asset('storage/' . $media->path),
                    'media_type' => $media->media_type,
                ];
            }
        }

        // General media (no specific color) + videos act as a shared fallback gallery
        $generalMedia = [];
        foreach ($allMedia as $media) {
            if (!$media->color) {
                $generalMedia[] = [
                    'path' => asset('storage/' . $media->path),
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

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div style="display:grid;grid-template-columns:1.1fr 0.9fr;gap:24px;align-items:start;">
                <div>
                    <!-- Product Media Slideshow -->
                    @if($allMedia->count() > 0)
                        <div style="position:relative;">
                            <div id="mediaSlideshow" style="position:relative;width:100%;aspect-ratio:1;overflow:hidden;border-radius:8px;background:#f3f4f6;"></div>

                            <!-- Navigation Arrows -->
                            <button type="button" onclick="changeSlide(-1)" id="prevBtn" style="position:absolute;top:50%;left:12px;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;font-size:1.2rem;display:none;align-items:center;justify-content:center;z-index:10;">&lt;</button>
                            <button type="button" onclick="changeSlide(1)" id="nextBtn" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:40px;height:40px;cursor:pointer;font-size:1.2rem;display:none;align-items:center;justify-content:center;z-index:10;">&gt;</button>

                            <!-- Thumbnail Navigation -->
                            <div id="thumbnailStrip" style="display:flex;gap:8px;margin-top:12px;overflow-x:auto;padding-bottom:8px;"></div>
                        </div>
                    @else
                        <img src="https://via.placeholder.com/720x720" alt="{{ $product->name }}" class="product-image">
                    @endif
                </div>
                <div>
                    <h1>{{ $product->name }}</h1>
                    <p class="text-muted">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                    <p id="productPrice" style="font-size:1.6rem;font-weight:700;">UGX{{ number_format($product->priceForColor($defaultColor), 0) }}</p>
                    <p>{{ $product->description }}</p>
                    <p class="text-muted">Stock available: <strong>{{ $product->stock }}</strong></p>

                    @if($product->stock > 0 && $product->stock <= 2)
                        <p style="color:#dc2626;font-weight:600;margin-top:4px;font-size:0.9rem;">
                            Only {{ $product->stock }} left in stock
                        </p>
                    @endif

                    @if($product->stock > 0)
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <p style="margin-top:16px;color:#4b5563;">Admin users cannot add products to the cart. Use the admin dashboard for inventory and order management.</p>
                        @else
                            <form method="POST" action="{{ route('cart.add', $product) }}" style="margin-top:16px;display:grid;gap:12px;">
                                @csrf
                                @if(!empty($colors))
                                    <div>
                                        <label style="display:block;margin-bottom:8px;">Color</label>
                                        <div id="colorButtons" style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @foreach($colors as $color)
                                                <button type="button"
                                                    class="color-pill"
                                                    data-color="{{ $color }}"
                                                    onclick="selectColor('{{ addslashes($color) }}')"
                                                    style="cursor:pointer;border:2px solid #d1d5db;background:#fff;color:#111;border-radius:999px;padding:8px 18px;font-size:0.95rem;font-weight:600;white-space:nowrap;transition:all 0.15s;">
                                                    {{ $color }}
                                                </button>
                                            @endforeach
                                        </div>
                                        <input type="hidden" name="color" id="selectedColor" value="{{ $defaultColor }}" required>
                                    </div>
                                @endif
                                @if($product->sizes && count($product->sizes) > 0)
                                    <div>
                                        <label>Size</label>
                                        <select class="input" name="size" required>
                                            <option value="">Select size</option>
                                            @foreach($product->sizes as $size)
                                                <option value="{{ $size }}">{{ $size }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
                                    <div style="flex:1;min-width:140px;">
                                        <label>Quantity</label>
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
                        <p style="color:#dc2626;font-weight:600;margin-top:16px;">Out of Stock</p>
                    @endif

                    <!-- Reviews Section -->
                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e9ecef;">
                        <h3>Customer Reviews</h3>
                        @if($reviews->isNotEmpty())
                            <p class="text-muted" style="margin-bottom:12px;">
                                ⭐ {{ number_format($avgRating, 1) }} — {{ $reviewCount }} review{{ $reviewCount !== 1 ? 's' : '' }}
                            </p>
                            <div style="display:grid;gap:12px;">
                                @foreach($reviews as $review)
                                    <div style="padding:12px;background:#f8f9fa;border-radius:10px;border:1px solid #e9ecef;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;">
                                            <strong style="font-size:0.9rem;">{{ $review->user?->name ?? 'Anonymous' }}</strong>
                                            <span style="font-size:0.85rem;color:#f57f17;">{{ str_repeat('⭐', $review->rating) }}</span>
                                        </div>
                                        @if($review->comment)
                                            <p style="margin:6px 0 0;font-size:0.9rem;color:#495057;">{{ $review->comment }}</p>
                                        @endif
                                        <p style="margin:4px 0 0;font-size:0.75rem;color:#adb5bd;">{{ $review->created_at->format('M d, Y') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted" style="font-size:0.9rem;">No reviews yet for this product.</p>
                        @endif
                    </div>
                </div>
            </div>
                        @if($product->size_guide)
                <div style="margin-top:24px;padding-top:24px;border-top:1px solid #e5e7eb;">
                    <h3>Size Guide</h3>
                    @php
                        $sizeGuide = null;
                        if (is_string($product->size_guide)) {
                            $decoded = json_decode($product->size_guide, true);
                            $sizeGuide = $decoded ? $decoded : null;
                        }
                    @endphp
                    @if($sizeGuide && isset($sizeGuide['chest']) || isset($sizeGuide['waist']))
                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:0.9rem;margin-top:12px;">
                                <thead>
                                    <tr style="background:#f3f4f6;">
                                        <th style="padding:12px;border:1px solid #e5e7eb;text-align:left;font-weight:600;">Measurement</th>
                                        @foreach(['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'] as $size)
                                            @php
                                                $hasData = false;
                                                foreach(['chest', 'waist'] as $measurement) {
                                                    if (isset($sizeGuide[$measurement][$size])) {
                                                        $hasData = true;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            @if($hasData)
                                                <th style="padding:12px;border:1px solid #e5e7eb;text-align:center;font-weight:600;">{{ $size }}</th>
                                            @endif
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($sizeGuide['chest']) && count(array_filter($sizeGuide['chest'])) > 0)
                                        <tr>
                                            <td style="padding:12px;border:1px solid #e5e7eb;font-weight:500;">Chest (inches)</td>
                                            @foreach(['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'] as $size)
                                                @php
                                                    $hasData = false;
                                                    foreach(['chest', 'waist'] as $measurement) {
                                                        if (isset($sizeGuide[$measurement][$size])) {
                                                            $hasData = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @if($hasData)
                                                    <td style="padding:12px;border:1px solid #e5e7eb;text-align:center;">{{ $sizeGuide['chest'][$size] ?? '-' }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endif
                                    @if(isset($sizeGuide['waist']) && count(array_filter($sizeGuide['waist'])) > 0)
                                        <tr>
                                            <td style="padding:12px;border:1px solid #e5e7eb;font-weight:500;">Waist (inches)</td>
                                            @foreach(['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'] as $size)
                                                @php
                                                    $hasData = false;
                                                    foreach(['chest', 'waist'] as $measurement) {
                                                        if (isset($sizeGuide[$measurement][$size])) {
                                                            $hasData = true;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                @if($hasData)
                                                    <td style="padding:12px;border:1px solid #e5e7eb;text-align:center;">{{ $sizeGuide['waist'][$size] ?? '-' }}</td>
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p style="white-space:pre-line;line-height:1.8;margin-top:12px;">{{ $product->size_guide }}</p>
                    @endif
                </div>
            @endif
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
            const thumbStrip = document.getElementById('thumbnailStrip');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            if (!slideshow) return;

            slideshow.innerHTML = '';
            thumbStrip.innerHTML = '';
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

            // Thumbnails + arrows only when more than one
            if (currentMedia.length > 1) {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                currentMedia.forEach((media, index) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'thumbnail';
                    thumb.style.cssText = `cursor:pointer;flex-shrink:0;width:80px;height:80px;border-radius:6px;overflow:hidden;border:2px solid ${index === 0 ? '#000' : '#e5e7eb'};`;
                    thumb.onclick = () => goToSlide(index);
                    if (media.media_type === 'video') {
                        thumb.innerHTML = `<div style="width:100%;height:100%;background:#000;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;">&#9654;</div>`;
                    } else {
                        thumb.innerHTML = `<img src="${media.path}" alt="Thumbnail" style="width:100%;height:100%;object-fit:cover;">`;
                    }
                    thumbStrip.appendChild(thumb);
                });
            } else {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            }
        }

        function showSlide(n) {
            const slides = document.querySelectorAll('#mediaSlideshow .slide');
            const thumbnails = document.querySelectorAll('#thumbnailStrip .thumbnail');
            const total = slides.length;
            if (total === 0) return;

            if (n >= total) currentSlide = 0;
            else if (n < 0) currentSlide = total - 1;
            else currentSlide = n;

            slides.forEach(s => s.style.display = 'none');
            slides[currentSlide].style.display = 'block';
            thumbnails.forEach((thumb, index) => {
                thumb.style.border = index === currentSlide ? '2px solid #000' : '2px solid #e5e7eb';
            });
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
        }

        function goToSlide(n) {
            showSlide(n);
        }

        function selectColor(color) {
            selectedColor = color;

            // Update hidden field
            const hidden = document.getElementById('selectedColor');
            if (hidden) hidden.value = color;

            // Update pill styles
            document.querySelectorAll('.color-pill').forEach(btn => {
                if (btn.dataset.color === color) {
                    btn.style.background = '#111';
                    btn.style.color = '#fff';
                    btn.style.borderColor = '#111';
                } else {
                    btn.style.background = '#fff';
                    btn.style.color = '#111';
                    btn.style.borderColor = '#d1d5db';
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            if (selectedColor) {
                selectColor(selectedColor);
            } else {
                currentMedia = mediaForColor(null);
                renderSlides();
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
        });
    </script>
@endsection
