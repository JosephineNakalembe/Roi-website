@extends('layouts.app')

@section('content')
    <div class="card" style="max-width:700px;margin:0 auto;">
        <h1>Edit Product</h1>
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" style="display:grid;gap:12px;">
            @csrf
                        @method('PATCH')
            <label>Product ID</label>
            <input class="input" name="product_id_display" value="{{ $product->product_id }}" readonly style="background:#f3f4f6;cursor:not-allowed;">
            <input type="hidden" name="product_id" value="{{ $product->product_id }}">
            
            <label>Name</label>
            <input class="input" name="name" value="{{ old('name', $product->name) }}" required>
            
            <label>Primary Category</label>
            <select class="input" name="category_id">
                <option value="">No primary category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            
            <label>Additional Categories <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— a product can belong to multiple categories</span></label>
            <div id="additionalCategories" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                @php
                    $selectedCategoryIds = $product->categories->pluck('id')->toArray();
                @endphp
                @foreach($categories as $category)
                    <label style="display:flex;align-items:center;gap:4px;font-weight:400;font-size:0.9rem;">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, old('categories', $selectedCategoryIds)) ? 'checked' : '' }}>
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary" onclick="showAddCategoryModal()" style="margin-bottom:8px;">+ Add New Category</button>
            
            <label>Description</label>
            <textarea class="input" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
            <label>Base Price (UGX)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Used as a fallback when a color has no specific price.</p>
            <input class="input" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required>
            
            <label>Cost Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— for profit reports, not shown to customers</span></label>
            <input class="input" name="cost_price" type="number" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '0.00') }}" placeholder="What you paid per unit">
            
            <label style="font-weight:700;">Color, Size, Quantity, Price & Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Each color can have its own price and its own set of images. Type the size manually (e.g., S, M, L, XL, 42, etc.)</p>
            <div id="colorQuantityContainer" style="display:grid;gap:10px;margin-bottom:10px;"></div>
            <button type="button" class="btn btn-secondary" onclick="addColorQuantityRow()">+ Add Color</button>

            
            <label style="font-weight:700;margin-top:12px;">Size Guide (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Enter measurements for each size. Only filled fields will be displayed on the product page.</p>
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
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Waist (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Hip (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Length (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Inseam (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Thigh (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Burst (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                        </tr>
                        <tr>
                            <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Shoulder (inches)</td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_xxs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_xs" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_s" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_m" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_l" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_2xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_3xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
                            <td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_4xl" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>
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

    <!-- Add Category Modal -->
    <div id="addCategoryModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:24px;max-width:400px;width:90%;margin:auto;">
            <h2 style="margin:0 0 16px 0;">Add New Category</h2>
            <input type="text" id="newCategoryName" class="input" placeholder="Category name" style="margin-bottom:12px;">
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeAddCategoryModal()">Cancel</button>
                <button type="button" class="btn" onclick="saveNewCategory()">Add Category</button>
            </div>
            <p id="addCategoryError" style="color:#ef4444;font-size:0.9rem;display:none;margin:8px 0 0 0;"></p>
        </div>
    </div>

    <script>
        let colorQuantityCount = 0;

        const existingColorPrices = @json($product->color_prices ?? []);
        const existingColorImages = @json(
            $product->images
                ->where('media_type', 'image')
                ->whereNotNull('color')
                ->groupBy('color')
                ->map(function ($imgs) {
                    return $imgs->map(function($img) {
                        return ['id' => $img->id, 'url' => asset('storage/' . $img->path)];
                    })->values();
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
                    <input type="text" class="input" name="size_${index}" placeholder="Size (e.g., S, M, L, XL, 42)" value="${size}" style="padding:6px;font-size:0.9rem;">
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
                        const measurements = ['waist', 'hip', 'length', 'inseam', 'thigh', 'burst', 'shoulder'];
                
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
                    const measurements = ['waist', 'hip', 'length', 'inseam', 'thigh', 'burst', 'shoulder'];
            
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

        // Add Category Modal functions
        function showAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'flex';
            document.getElementById('newCategoryName').value = '';
            document.getElementById('addCategoryError').style.display = 'none';
        }

        function closeAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'none';
        }

        function saveNewCategory() {
            const name = document.getElementById('newCategoryName').value.trim();
            if (!name) {
                document.getElementById('addCategoryError').textContent = 'Please enter a category name.';
                document.getElementById('addCategoryError').style.display = 'block';
                return;
            }

            fetch('{{ route("admin.categories.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ name: name }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Add to primary category dropdown
                    const primarySelect = document.querySelector('select[name="category_id"]');
                    const option = document.createElement('option');
                    option.value = data.category.id;
                    option.textContent = data.category.name;
                    primarySelect.appendChild(option);

                    // Add to additional categories checkboxes
                    const additionalDiv = document.getElementById('additionalCategories');
                    const label = document.createElement('label');
                    label.style.cssText = 'display:flex;align-items:center;gap:4px;font-weight:400;font-size:0.9rem;';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.name = 'categories[]';
                    cb.value = data.category.id;
                    cb.checked = true;
                    label.appendChild(cb);
                    label.appendChild(document.createTextNode(' ' + data.category.name));
                    additionalDiv.appendChild(label);

                    closeAddCategoryModal();
                } else {
                    document.getElementById('addCategoryError').textContent = data.message || 'Failed to create category.';
                    document.getElementById('addCategoryError').style.display = 'block';
                }
            })
            .catch(err => {
                document.getElementById('addCategoryError').textContent = 'An error occurred. Please try again.';
                document.getElementById('addCategoryError').style.display = 'block';
            });
        }

        // Close modal on outside click
        document.getElementById('addCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddCategoryModal();
        });
    </script>
@endsection