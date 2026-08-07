@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.products.index')])
            <h1 class="mb-0">Edit Product</h1>
        </div>
    </div>
    <div class="card" style="max-width:700px;margin:0 auto;">
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
            
            <label>Additional Categories <span class="text-muted" style="font-weight:400;font-size:0.95rem;">— a product can belong to multiple categories</span></label>
            <div id="additionalCategories" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                @php
                    $selectedCategoryIds = $product->categories->pluck('id')->toArray();
                @endphp
                @foreach($categories as $category)
                    <label style="display:flex;align-items:center;gap:4px;font-weight:400;font-size:1rem;">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, old('categories', $selectedCategoryIds)) ? 'checked' : '' }}>
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary" onclick="showAddCategoryModal()" style="margin-bottom:8px;">+ Add New Category</button>
            
            <label>Description</label>
            <textarea class="input" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
            <label>Base Price (UGX)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:1rem;">Used as a fallback when a color has no specific price.</p>
            <input class="input" name="price" type="number" step="0.01" value="{{ old('price', $product->price) }}" required>
            
            <label>Cost Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.95rem;">— for profit reports, not shown to customers</span></label>
            <input class="input" name="cost_price" type="number" step="0.01" value="{{ old('cost_price', $product->cost_price ?? '0.00') }}" placeholder="What you paid per unit">

            <label>Supplier <span class="text-muted" style="font-weight:400;font-size:0.95rem;">— admin only, not shown to customers</span></label>
            <input class="input" name="supplier" type="text" value="{{ old('supplier', $product->supplier) }}" placeholder="Who you bought this from">

            <label style="font-weight:700;">Color, Size, Quantity, Price & Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:1rem;">Each color can have its own price and its own set of images. If the product has color options, pick the color and upload a photo of that color so buyers see a picture instead of a swatch. If there are no options (e.g. it only comes in one color), just type the color name — the color picker and photo are optional. Type the size manually (e.g., S, M, L, XL, 42, etc.)</p>
            <div id="colorQuantityContainer" style="display:grid;gap:10px;margin-bottom:10px;"></div>
            <button type="button" class="btn btn-secondary" onclick="addColorQuantityRow()">+ Add Color</button>

            
            <label style="font-weight:700;margin-top:12px;">Size Guide (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:1rem;">Build a custom size chart — add columns (e.g. sizes) and rows (e.g. measurements) as needed. Only filled fields will be displayed on the product page.</p>

            <div id="sizeGuideBuilder" style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:1rem;">
                    <thead>
                        <tr id="sizeGuideHeadRow" style="background:#f3f4f6;"></tr>
                    </thead>
                    <tbody id="sizeGuideBody"></tbody>
                </table>
            </div>
            <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary" onclick="addSizeGuideColumn()">+ Add Column</button>
                <button type="button" class="btn btn-secondary" onclick="addSizeGuideRow()">+ Add Row</button>
            </div>
            <input type="hidden" name="size_guide" id="sizeGuideHidden">
            <input type="hidden" name="size_guide_type" value="table">
            
                        <input type="hidden" name="colors" id="colorsHidden">
            <input type="hidden" name="stock" value="0">
            <label style="display:none;"><input type="checkbox" name="is_active" value="1"{{ $product->is_active ? ' checked' : '' }}> Published</label>
            <label style="display:flex;align-items:center;gap:8px;font-weight:500;margin-top:8px;">
                <input type="checkbox" name="non_returnable" value="1"{{ $product->non_returnable ? ' checked' : '' }}> Non-returnable
            </label>
            
            <label style="font-weight:700;">Add More Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:1rem;">Upload additional images (JPG, PNG, max 5MB each)</p>
            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" onchange="previewImages(event)">
            <div id="imagePreview" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:12px;margin-top:12px;"></div>
            
            <label style="font-weight:700;margin-top:12px;">Add Video (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:1rem;">Upload a product video (MP4, MOV, max 50MB)</p>
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
                                    <source src="{{ media_url($media->path) }}" type="video/mp4">
                                </video>
                                <span style="position:absolute;top:8px;left:8px;background:rgba(0,0,0,0.7);color:#fff;padding:4px 8px;border-radius:4px;font-size:0.85rem;">VIDEO</span>
                                <form method="POST" action="{{ route('admin.products.destroy-media', $media) }}" style="position:absolute;top:8px;right:8px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Remove this video?')" style="background:#ef4444;color:#fff;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-weight:bold;font-size:1.1rem;line-height:1;">×</button>
                                </form>
                            </div>
                        @else
                            <div style="position:relative;">
                                <img src="{{ media_url($media->path) }}" alt="" style="width:100%;border-radius:12px;object-fit:cover;height:150px;">
                                <form method="POST" action="{{ route('admin.products.destroy-media', $media) }}" style="position:absolute;top:8px;right:8px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Remove this image?')" style="background:#ef4444;color:#fff;border:none;border-radius:50%;width:28px;height:28px;cursor:pointer;font-weight:bold;font-size:1.1rem;line-height:1;">×</button>
                                </form>
                            </div>
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
            <p id="addCategoryError" style="color:#ef4444;font-size:1rem;display:none;margin:8px 0 0 0;"></p>
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
                        return ['id' => $img->id, 'url' => media_url($img->path)];
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
            row.classList.add('color-row');

            // Parse existing color which may be "#hex:Name", plain hex, or a plain text name
            let hexCode = '';
            let colorName = color || '';
            if (color && color.includes(':')) {
                const parts = color.split(':');
                hexCode = /^#[0-9a-fA-F]{6}$/.test(parts[0]) ? parts[0] : '';
                colorName = hexCode ? (parts[1] || '') : color;
            } else if (color && /^#[0-9a-fA-F]{6}$/.test(color)) {
                hexCode = color;
                colorName = '';
            }

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
                <div style="display:grid;grid-template-columns:1fr 1fr 80px auto;gap:8px;align-items:center;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="color" name="color_${index}" value="${hexCode}" data-touched="${hexCode ? 'true' : 'false'}" style="width:50px;height:40px;border:none;border-radius:8px;cursor:pointer;padding:0;flex-shrink:0;" title="Optional — only needed when the product has color options to pick from">
                    <input type="text" class="input" name="color_name_${index}" placeholder="Color name (e.g., Navy Blue)" value="${colorName}" style="flex:1;padding:6px;font-size:1rem;">
                </div>
                    <input type="text" class="input" name="size_${index}" placeholder="Size (e.g., S, M, L, XL, 42)" value="${size}" style="padding:6px;font-size:1rem;">
                    <input type="number" class="input" name="quantity_${index}" placeholder="Qty" min="1" value="${quantity}" style="padding:6px;font-size:1rem;">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=border]').remove(); updateColors();" style="padding:4px 8px;font-size:0.95rem;">Remove</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.95rem;font-weight:600;">Price for this color (UGX)</label>
                    <input type="number" class="input" name="price_${index}" placeholder="Leave blank to use base price" step="0.01" min="0" value="${priceVal}" style="padding:6px;font-size:1rem;">
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.95rem;font-weight:600;">Add images for this color (optional)</label>
                    ${existingImagesHtml ? `<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(80px, 1fr));gap:8px;">${existingImagesHtml}</div>` : ''}
                    <input type="file" name="color_images_${index}[]" multiple accept="image/*" onchange="previewColorImages(event, ${index})" style="font-size:0.95rem;">
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
            const colorNames = [];
            container.querySelectorAll('.color-row').forEach(row => {
                const colorInput = row.querySelector('input[type="color"][name^="color_"]');
                const nameInput = row.querySelector('input[name^="color_name_"]');
                const touched = colorInput && colorInput.dataset.touched === 'true';
                const name = nameInput && nameInput.value.trim();
                if (touched && name) {
                    colorNames.push(colorInput.value + ':' + name);
                } else if (name) {
                    colorNames.push(name);
                } else if (touched && colorInput.value) {
                    colorNames.push(colorInput.value);
                }
            });
            document.getElementById('colorsHidden').value = JSON.stringify(colorNames);
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
                if (e.target.type === 'color') {
                    e.target.dataset.touched = 'true';
                }
                updateColors();
            }
        });

                // Initialize hidden field
                updateColors();

                // Size guide dynamic table builder
                function esc(v) {
                    return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                }

                function addSizeGuideColumn(title = '') {
                    const headRow = document.getElementById('sizeGuideHeadRow');
                    const body = document.getElementById('sizeGuideBody');
                    const th = document.createElement('th');
                    th.style.cssText = 'padding:6px;border:1px solid #e5e7eb;text-align:center;';
                    th.innerHTML = `
                        <div style="display:flex;align-items:center;justify-content:center;gap:4px;">
                            <input type="text" class="input sg-col-input" value="${esc(title)}" placeholder="Size" style="padding:4px;font-size:0.95rem;text-align:center;width:80px;">
                            <button type="button" onclick="removeSizeGuideColumn(this)" style="background:#ef4444;color:#fff;border:none;border-radius:4px;width:20px;height:20px;cursor:pointer;font-weight:bold;font-size:0.95rem;line-height:1;flex-shrink:0;" title="Remove column">×</button>
                        </div>
                    `;
                    headRow.appendChild(th);
                    body.querySelectorAll('tr').forEach(tr => {
                        const td = document.createElement('td');
                        td.style.cssText = 'padding:4px;border:1px solid #e5e7eb;';
                        td.innerHTML = `<input type="text" class="input sg-cell-input" style="padding:4px;font-size:0.95rem;text-align:center;width:100%;box-sizing:border-box;" placeholder="-">`;
                        tr.appendChild(td);
                    });
                }

                function removeSizeGuideColumn(btn) {
                    const th = btn.closest('th');
                    const idx = Array.from(th.parentElement.children).indexOf(th) - 1;
                    th.remove();
                    document.querySelectorAll('#sizeGuideBody tr').forEach(tr => {
                        const cells = tr.querySelectorAll('.sg-cell-input');
                        if (cells[idx]) cells[idx].closest('td').remove();
                    });
                }

                function addSizeGuideRow(label = '', cells = []) {
                    const body = document.getElementById('sizeGuideBody');
                    const tr = document.createElement('tr');
                    const labelTd = document.createElement('td');
                    labelTd.style.cssText = 'padding:6px;border:1px solid #e5e7eb;';
                    labelTd.innerHTML = `
                        <div style="display:flex;align-items:center;gap:4px;">
                            <input type="text" class="input sg-row-label" value="${esc(label)}" placeholder="Measurement" style="padding:4px;font-size:0.95rem;text-align:left;width:100%;box-sizing:border-box;">
                            <button type="button" onclick="removeSizeGuideRow(this)" style="background:#ef4444;color:#fff;border:none;border-radius:4px;width:20px;height:20px;cursor:pointer;font-weight:bold;font-size:0.95rem;line-height:1;flex-shrink:0;" title="Remove row">×</button>
                        </div>
                    `;
                    tr.appendChild(labelTd);
                    const colCount = document.querySelectorAll('#sizeGuideHeadRow .sg-col-input').length;
                    for (let i = 0; i < colCount; i++) {
                        const td = document.createElement('td');
                        td.style.cssText = 'padding:4px;border:1px solid #e5e7eb;';
                        td.innerHTML = `<input type="text" class="input sg-cell-input" value="${esc(cells[i] ?? '')}" style="padding:4px;font-size:0.95rem;text-align:center;width:100%;box-sizing:border-box;" placeholder="-">`;
                        tr.appendChild(td);
                    }
                    body.appendChild(tr);
                }

                function removeSizeGuideRow(btn) {
                    btn.closest('tr').remove();
                }

                function collectSizeGuide() {
                    const headerInputs = Array.from(document.querySelectorAll('#sizeGuideHeadRow .sg-col-input'));
                    const keptIndexes = [];
                    const columns = [];
                    headerInputs.forEach((inp, idx) => {
                        const v = inp.value.trim();
                        if (v) { keptIndexes.push(idx); columns.push(v); }
                    });
                    const rows = [];
                    document.querySelectorAll('#sizeGuideBody tr').forEach(tr => {
                        const label = tr.querySelector('.sg-row-label').value.trim();
                        const cellInputs = Array.from(tr.querySelectorAll('.sg-cell-input'));
                        const cells = keptIndexes.map(idx => cellInputs[idx] ? cellInputs[idx].value.trim() : '');
                        if (cells.some(c => c !== '')) {
                            rows.push({label, cells});
                        }
                    });
                    if (columns.length === 0 || rows.length === 0) return null;
                    return {type: 'table', columns, rows};
                }

                function initSizeGuide() {
                    ['S', 'M', 'L', 'XL'].forEach(c => addSizeGuideColumn(c));
                    addSizeGuideRow('Waist', ['', '', '', '']);
                    addSizeGuideRow('Length', ['', '', '', '']);
                }

                function loadExistingSizeGuide(raw) {
                    let parsed = raw;
                    if (typeof parsed === 'string') {
                        if (!parsed.trim()) return false;
                        try { parsed = JSON.parse(parsed); } catch (e) { return false; }
                    }
                    if (parsed && parsed.type === 'table' && Array.isArray(parsed.columns)) {
                        parsed.columns.forEach(c => addSizeGuideColumn(c));
                        (parsed.rows || []).forEach(r => addSizeGuideRow(r.label || '', (r.cells || []).slice()));
                        return true;
                    }
                    if (parsed && Array.isArray(parsed.shoes)) {
                        const shoeMeta = {
                            baby: 'US Baby', toddler: 'US Toddler', kids: 'US Kids', youth: 'US Youth',
                            mens: "US Men's", womens: "US Women's", uk: 'UK', eu: 'EU', cm: 'CM'
                        };
                        const fields = Object.keys(shoeMeta);
                        const cols = fields.filter(f => parsed.shoes.some(r => r[f] !== undefined && r[f] !== ''));
                        cols.forEach(f => addSizeGuideColumn(shoeMeta[f]));
                        parsed.shoes.forEach((row, idx) => {
                            const cells = cols.map(f => (row[f] !== undefined ? row[f] : '').toString());
                            if (cells.some(c => c !== '')) addSizeGuideRow('Row ' + (idx + 1), cells);
                        });
                        return true;
                    }
                    if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                        const labels = {waist:'Waist',hip:'Hip',length:'Length',inseam:'Inseam',thigh:'Thigh',burst:'Burst',shoulder:'Shoulder'};
                        const allSizes = ['XXS','XS','S','M','L','XL','2XL','3XL','4XL'];
                        const measurementKeys = Object.keys(parsed).filter(k => parsed[k] && typeof parsed[k] === 'object' && !Array.isArray(parsed[k]));
                        if (measurementKeys.length) {
                            const sizesWithData = allSizes.filter(sz => measurementKeys.some(m => parsed[m][sz] !== undefined && parsed[m][sz] !== ''));
                            sizesWithData.forEach(sz => addSizeGuideColumn(sz));
                            measurementKeys.forEach(m => {
                                const cells = sizesWithData.map(sz => parsed[m][sz] !== undefined ? String(parsed[m][sz]) : '');
                                addSizeGuideRow((labels[m] || m) + ' (inches)', cells);
                            });
                            return true;
                        }
                    }
                    return false;
                }

                // Load existing size guide into the builder
                const existingSizeGuide = @json($product->size_guide ?? null);
                if (!loadExistingSizeGuide(existingSizeGuide)) {
                    initSizeGuide();
                }

                // Update size guide hidden field before form submission
                document.querySelector('form').addEventListener('submit', function(e) {
                    const data = collectSizeGuide();
                    document.getElementById('sizeGuideHidden').value = data ? JSON.stringify(data) : '';
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
                            <span style="position:absolute;top:4px;left:4px;background:#000;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.85rem;">NEW ${index + 1}</span>
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
                        <p style="margin-top:8px;font-size:0.95rem;color:#10b981;">New video will be added</p>
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
                    label.style.cssText = 'display:flex;align-items:center;gap:4px;font-weight:400;font-size:1rem;';
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