@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:700px;margin:0 auto;">
        <h1>Edit Product</h1>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" style="display:grid;gap:12px;">
            @csrf
                        @method('PATCH')
            <label>Product ID</label>
            <input class="input" name="product_id" value="{{ old('product_id', $product->product_id) }}" placeholder="e.g., SKU-001, PROD-123" required>
            
            <label>Name</label>
            <input class="input" name="name" value="{{ old('name', $product->name) }}" required>
            
            <label>Category</label>
            <select class="input" name="category_id" required>
                <option value="">Select a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            
            <label>Description</label>
            <textarea class="input" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
            <label>Base Price (UGX)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Used as a fallback when a color has no specific price.</p>
            <input class="input" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required>
            
            <label>Cost Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— for profit reports, not shown to customers</span></label>
            <input class="input" name="cost_price" type="number" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '0.00') }}" placeholder="What you paid per unit">
            
            <label style="font-weight:700;">Color, Size, Quantity, Price & Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Each color can have its own price and its own set of images.</p>
            <div id="colorQuantityContainer" style="display:grid;gap:10px;margin-bottom:10px;"></div>
            <button type="button" class="btn btn-secondary" onclick="addColorQuantityRow()">+ Add Color</button>

            
            <label style="font-weight:700;margin-top:12px;">Size Guide (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Enter measurements for each size</p>
            <div style="overflow-x:auto;">
                <table id="sizeGuideTable" style="width:100%;border-collapse:collapse;font-size:0.9rem;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Measurement</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">XXS</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">XS</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">S</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">M</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">L</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">XL</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">2XL</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">3XL</th>
                            <th style="padding:8px;border:1px solid #e5e7eb;text-align:center;">4XL</th>
                        </tr>
                    </thead>
                    <tbody id="sizeGuideBody">
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Chest (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_xxs" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_xs" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_s" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_m" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_l" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_2xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_3xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_chest_4xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Waist (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xxs" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xs" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_s" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_m" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_l" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_2xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_3xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_4xl" class="input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="size_guide" id="sizeGuideHidden">
            
                        <input type="hidden" name="colors" id="colorsHidden">
            <input type="hidden" name="stock" value="0">
            <label style="display:none;"><input type="checkbox" name="is_active" value="1"{{ $product->is_active ? ' checked' : '' }}> Published</label>
            
            <label style="font-weight:700;">Add More Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Upload additional images (JPG, PNG, max 5MB each)</p>
            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" onchange="previewImages(event)">
            <div id="imagePreview" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:12px;margin-top:12px;"></div>
            
            <label style="font-weight:700;margin-top:12px;">Add Video (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Upload a product video (MP4, MOV, max 50MB)</p>
            <input type="file" name="video" id="videoInput" accept="video/*" onchange="previewVideo(event)">
            <div id="videoPreview" style="margin-top:12px;"></div>
            
            <button class="btn">Update Product</button>
        </form>
                @if($product->images->isNotEmpty())
            <div style="margin-top:24px;">
                <h2>Existing Media</h2>
                <div class="grid-3">
                    @foreach($product->images->sortBy('order') as $media)
                        @if($media->media_type === 'video')
                            <div style="position:relative;">
                                <video controls style="width:100%;border-radius:12px;object-fit:cover;height:150px;background:#000;">
                                    <source src="{{ asset('storage/' . $media->path) }}" type="video/mp4">
                                </video>
                                <span style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:0.75rem;">VIDEO</span>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $media->path) }}" alt="" style="width:100%;border-radius:12px;object-fit:cover;height:150px;">
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        let colorQuantityCount = 0;

                // All available sizes from One-Size to 4XL
                const allSizes = ['One-Size', 'XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL'];

        const existingColorPrices = @json($product->color_prices ?? []);
        const existingColorImages = @json(
            $product->images
                ->where('media_type', 'image')
                ->whereNotNull('color')
                ->groupBy('color')
                ->map(function ($imgs) {
                    return $imgs->map(fn($img) => [
                        'id' => $img->id,
                        'url' => asset('storage/' . $img->path),
                    ])->values();
                })
        );

        function addColorQuantityRow(color = '', size = '', quantity = '') {
            const container = document.getElementById('colorQuantityContainer');
            const index = colorQuantityCount++;
            const row = document.createElement('div');
            row.style.border = '1px solid #e5e7eb';
            row.style.borderRadius = '10px';
            row.style.padding = '12px';
            row.style.display = 'grid';
            row.style.gap = '10px';
            row.style.background = '#fafafa';

            let sizeOptions = allSizes.map(s => `<option value="${s}"${s === size ? ' selected' : ''}>${s}</option>`).join('');

            // Check if size is One-Size for selection
            const oneSizeSelected = size === 'One-Size' ? ' selected' : '';

            // Prefill price for this color if it exists
            const priceVal = (color && existingColorPrices[color] != null) ? existingColorPrices[color] : '';

            // Build existing-images preview for this color
            let existingImagesHtml = '';
            if (color && existingColorImages[color]) {
                existingImagesHtml = existingColorImages[color].map(img =>
                    `<img src="${img.url}" style="width:100%;height:80px;object-fit:cover;border-radius:6px;border:2px solid #e5e7eb;">`
                ).join('');
            }

            row.innerHTML = `
                <div style="display:grid;grid-template-columns:repeat(2, 1fr) 80px auto;gap:8px;align-items:center;">
                    <input type="text" class="input" name="color_${index}" placeholder="Color (e.g., Red)" value="${color}" style="padding:6px;font-size:0.9rem;">
                    <select class="input" name="size_${index}" style="padding:6px;font-size:0.9rem;">
                        <option value="">Select Size</option>
                        <option value="One-Size"${oneSizeSelected}>One-Size</option>
                        ${sizeOptions}
                    </select>
                    <input type="number" class="input" name="quantity_${index}" placeholder="Qty" min="1" value="${quantity}" style="padding:6px;font-size:0.9rem;">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=border]').remove(); updateColors();" style="padding:4px 8px;font-size:0.85rem;">Remove</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.85rem;font-weight:600;">Price for this color (UGX)</label>
                    <input type="number" class="input" name="price_${index}" placeholder="Leave blank to use base price" step="0.01" min="0" value="${priceVal}" style="padding:6px;font-size:0.9rem;">
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.85rem;font-weight:600;">Add images for this color (optional)</label>
                    ${existingImagesHtml ? `<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(80px, 1fr));gap:8px;">${existingImagesHtml}</div>` : ''}
                    <input type="file" name="color_images_${index}[]" multiple accept="image/*" onchange="previewColorImages(event, ${index})" style="font-size:0.85rem;">
                    <div id="colorImagePreview_${index}" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(80px, 1fr));gap:8px;margin-top:4px;"></div>
                </div>
            `;
            container.appendChild(row);
        }

        // Preview per-color images
        function previewColorImages(event, index) {
            const previewContainer = document.getElementById('colorImagePreview_' + index);
            previewContainer.innerHTML = '';
            const files = event.target.files;
            if (files.length > 0) {
                Array.from(files).forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.width = '100%';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '6px';
                        img.style.border = '2px solid #10b981';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }


        function updateColors() {
            const container = document.getElementById('colorQuantityContainer');
            const colors = Array.from(container.querySelectorAll('input[type="text"]'))
                .map(el => el.value)
                .filter(val => val.trim());
            document.getElementById('colorsHidden').value = colors.join(', ');
        }

                // Load existing color stock
        const existingColorStock = @json($product->color_stock ?? []);
        Object.entries(existingColorStock).forEach(([colorSize, quantity]) => {
            // Parse color and size from "Color (Size)" format
            const match = colorSize.match(/^(.+?)\s*\((.+?)\)$/);
            if (match) {
                addColorQuantityRow(match[1], match[2], quantity);
            } else {
                addColorQuantityRow(colorSize, '', quantity);
            }
        });

        // If no existing colors, add one empty row
        if (Object.keys(existingColorStock).length === 0) {
            addColorQuantityRow();
        }

        // Add event listeners to color inputs
        document.addEventListener('input', function(e) {
            if (e.target.name.startsWith('color_')) {
                updateColors();
            }
        });

                // Initialize hidden field
                updateColors();

                // Load existing size guide into table
                const existingSizeGuide = @json($product->size_guide ?? null);
                if (existingSizeGuide) {
                    try {
                        const sizeData = typeof existingSizeGuide === 'string' ? JSON.parse(existingSizeGuide) : existingSizeGuide;
                        const sizes = ['xxs', 'xs', 's', 'm', 'l', 'xl', '2xl', '3xl', '4xl'];
                        const measurements = ['chest', 'waist'];
                
                        measurements.forEach(measurement => {
                            if (sizeData[measurement]) {
                                sizes.forEach(size => {
                                    const sizeUpper = size.toUpperCase();
                                    if (sizeData[measurement][sizeUpper]) {
                                        const input = document.querySelector(`input[name="size_${measurement}_${size}"]`);
                                        if (input) {
                                            input.value = sizeData[measurement][sizeUpper];
                                        }
                                    }
                                });
                            }
                        });
                    } catch (e) {
                        // If it's old format, ignore
                        console.log('Size guide in old format');
                    }
                }

                // Update size guide hidden field before form submission
                document.querySelector('form').addEventListener('submit', function(e) {
                    const sizeGuideData = {};
                    const sizes = ['xxs', 'xs', 's', 'm', 'l', 'xl', '2xl', '3xl', '4xl'];
                    const measurements = ['chest', 'waist'];
            
                    measurements.forEach(measurement => {
                        sizeGuideData[measurement] = {};
                        sizes.forEach(size => {
                            const input = document.querySelector(`input[name="size_${measurement}_${size}"]`);
                            if (input && input.value.trim()) {
                                sizeGuideData[measurement][size.toUpperCase()] = input.value.trim();
                            }
                        });
                    });
            
                    // Convert to JSON and store in hidden field
                    document.getElementById('sizeGuideHidden').value = JSON.stringify(sizeGuideData);
                });

        // Preview multiple images
        function previewImages(event) {
            const previewContainer = document.getElementById('imagePreview');
            previewContainer.innerHTML = '';
            const files = event.target.files;
            
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const wrapper = document.createElement('div');
                        wrapper.style.position = 'relative';
                        wrapper.innerHTML = `
                            <img src="${e.target.result}" style="width:100%;height:120px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb;">
                            <span style="position:absolute;top:4px;left:4px;background:#000;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;">NEW ${index + 1}</span>
                        `;
                        previewContainer.appendChild(wrapper);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        // Preview video
        function previewVideo(event) {
            const previewContainer = document.getElementById('videoPreview');
            previewContainer.innerHTML = '';
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <video controls style="max-width:100%;max-height:300px;border-radius:8px;border:2px solid #e5e7eb;">
                            <source src="${e.target.result}" type="${file.type}">
                            Your browser does not support the video tag.
                        </video>
                        <p style="margin-top:8px;font-size:0.85rem;color:#10b981;">New video will be added</p>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
@endsection
