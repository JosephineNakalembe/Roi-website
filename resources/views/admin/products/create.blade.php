@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button', ['fallback' => route('admin.products.index')])
            <h1 class="mb-0">Add Product</h1>
        </div>
    </div>
    <div class="card" style="max-width:700px;margin:0 auto;">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" style="display:grid;gap:12px;">
            @csrf
                        <label>Product ID <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— Auto-generated</span></label>
            <input class="input" name="product_id_display" id="productIdDisplay" value="ER0000036" readonly style="background:#f3f4f6;cursor:not-allowed;">
            <input type="hidden" name="product_id" id="productIdHidden" value="ER0000036">
            
            <label>Name</label>
            <input class="input" name="name" value="{{ old('name') }}" required>
            
            <label>Primary Category</label>
            <select class="input" name="category_id">
                <option value="">No primary category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            
            <label>Additional Categories <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— a product can belong to multiple categories</span></label>
            <div id="additionalCategories" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
                @foreach($categories as $category)
                    <label style="display:flex;align-items:center;gap:4px;font-weight:400;font-size:0.9rem;">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}" {{ in_array($category->id, old('categories', [])) ? 'checked' : '' }}>
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>

            <button type="button" class="btn btn-secondary" onclick="showAddCategoryModal()" style="margin-bottom:8px;">+ Add New Category</button>
            
            <label>Description</label>
            <textarea class="input" name="description" rows="4">{{ old('description') }}</textarea>
            <label>Base Price (UGX)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Used as a fallback when a color has no specific price.</p>
            <input class="input" name="price" type="number" step="0.01" value="{{ old('price', '0.00') }}" required>
            
            <label>Cost Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.85rem;">— for profit reports, not shown to customers</span></label>
            <input class="input" name="cost_price" type="number" step="0.01" value="{{ old('cost_price', '0.00') }}" placeholder="What you paid per unit">
            
            <label style="font-weight:700;">Color, Size, Quantity, Price & Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Each color can have its own price and its own set of images. Type the size manually (e.g., S, M, L, XL, 42, etc.)</p>
            <div id="colorQuantityContainer" style="display:grid;gap:10px;margin-bottom:10px;"></div>
            <button type="button" class="btn btn-secondary" onclick="addColorQuantityRow()">+ Add Color</button>

            
            <label style="font-weight:700;margin-top:12px;">Size Guide (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Select the type and enter measurements. Only filled fields will be displayed on the product page.</p>
            
            <div style="display:flex;gap:12px;margin-bottom:12px;">
                <label style="display:flex;align-items:center;gap:6px;font-weight:500;cursor:pointer;">
                    <input type="radio" name="size_guide_type" value="clothing" checked onchange="toggleSizeGuideType()"> 👕 Clothing
                </label>
                <label style="display:flex;align-items:center;gap:6px;font-weight:500;cursor:pointer;">
                    <input type="radio" name="size_guide_type" value="shoes" onchange="toggleSizeGuideType()"> 👟 Shoes
                </label>
            </div>

            <!-- Clothing Size Guide -->
            <div id="clothingSizeGuide" style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:0.9rem;">
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
                    <tbody>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Waist (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_waist_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Hip (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_hip_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Length (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_length_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Inseam (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_inseam_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Thigh (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_thigh_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Burst (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_burst_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;font-weight:600;">Shoulder (inches)</td>@foreach(['xxs','xs','s','m','l','xl','2xl','3xl','4xl'] as $sz)<td style="padding:4px;border:1px solid #e5e7eb;"><input type="text" name="size_shoulder_{{ $sz }}" class="input size-guide-input" style="padding:4px;font-size:0.85rem;text-align:center;" placeholder="-"></td>@endforeach</tr>
                    </tbody>
                </table>
            </div>

            <!-- Shoes Size Guide (Baby to Grown-up) -->
            <div id="shoesSizeGuide" style="overflow-x:auto;display:none;">
                <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Baby</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Toddler</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Kids</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Youth</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Men's</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">US Women's</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">UK</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">EU</th>
                            <th style="padding:6px;border:1px solid #e5e7eb;text-align:center;">CM</th>
                        </tr>
                    </thead>
                    <tbody id="shoesSizeGuideBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="size_guide" id="sizeGuideHidden">
            
            <input type="hidden" name="stock" value="0">
            <label style="display:none;"><input type="checkbox" name="is_active" value="1" checked> Published</label>
            
            <label style="font-weight:700;">Product Images</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Upload multiple images. Drag to reorder, click × to remove.</p>
            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" onchange="previewImages(event)">
            <div id="imagePreview" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(120px, 1fr));gap:12px;margin-top:12px;"></div>

            <label style="font-weight:700;margin-top:12px;">Product Video (Optional)</label>
            <p class="text-muted" style="margin:-8px 0 8px 0;font-size:0.9rem;">Upload a video (MP4, MOV, max 50MB)</p>
            <input type="file" name="video" id="videoInput" accept="video/*" onchange="previewVideo(event)">
            <div id="videoPreview" style="margin-top:12px;"></div>
            
            <button class="btn" type="submit">Save Product</button>
        </form>
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

        // Predefined shoe size chart (baby to grown-up)
        const shoeSizeData = [
            {baby:'0-1',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'0',eu:'15',cm:'8.3'},
            {baby:'1-2',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'0.5',eu:'16',cm:'8.9'},
            {baby:'2-3',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'1',eu:'16.5',cm:'9.5'},
            {baby:'3-4',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'1.5',eu:'17',cm:'10.2'},
            {baby:'4-5',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'2',eu:'18',cm:'10.8'},
            {baby:'5-6',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'2.5',eu:'18.5',cm:'11.4'},
            {baby:'6-7',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'3',eu:'19',cm:'12.1'},
            {baby:'7-8',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'3.5',eu:'20',cm:'12.7'},
            {baby:'8-9',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'4',eu:'21',cm:'13.3'},
            {baby:'9-10',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'4.5',eu:'22',cm:'14'},
            {baby:'10-11',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'5',eu:'22.5',cm:'14.6'},
            {baby:'11-12',toddler:'',kids:'',youth:'',mens:'',womens:'',uk:'5.5',eu:'23',cm:'15.2'},
            {baby:'',toddler:'1',kids:'',youth:'',mens:'',womens:'',uk:'6',eu:'23.5',cm:'15.9'},
            {baby:'',toddler:'2',kids:'',youth:'',mens:'',womens:'',uk:'6.5',eu:'24',cm:'16.5'},
            {baby:'',toddler:'3',kids:'',youth:'',mens:'',womens:'',uk:'7',eu:'25',cm:'17.1'},
            {baby:'',toddler:'4',kids:'',youth:'',mens:'',womens:'',uk:'7.5',eu:'25.5',cm:'17.8'},
            {baby:'',toddler:'5',kids:'',youth:'',mens:'',womens:'',uk:'8',eu:'26',cm:'18.4'},
            {baby:'',toddler:'',kids:'10',youth:'',mens:'',womens:'',uk:'8.5',eu:'27',cm:'19.1'},
            {baby:'',toddler:'',kids:'11',youth:'',mens:'',womens:'',uk:'9',eu:'27.5',cm:'19.7'},
            {baby:'',toddler:'',kids:'12',youth:'',mens:'',womens:'',uk:'9.5',eu:'28',cm:'20.3'},
            {baby:'',toddler:'',kids:'13',youth:'',mens:'',womens:'',uk:'10',eu:'29',cm:'21'},
            {baby:'',toddler:'',kids:'',youth:'1',mens:'',womens:'',uk:'10.5',eu:'29.5',cm:'21.6'},
            {baby:'',toddler:'',kids:'',youth:'2',mens:'',womens:'',uk:'11',eu:'30',cm:'22.2'},
            {baby:'',toddler:'',kids:'',youth:'3',mens:'',womens:'',uk:'11.5',eu:'31',cm:'22.9'},
            {baby:'',toddler:'',kids:'',youth:'4',mens:'',womens:'',uk:'12',eu:'31.5',cm:'23.5'},
            {baby:'',toddler:'',kids:'',youth:'5',mens:'',womens:'',uk:'12.5',eu:'32',cm:'24.1'},
            {baby:'',toddler:'',kids:'',youth:'6',mens:'5',womens:'6.5',uk:'13',eu:'33',cm:'24.8'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'6',womens:'7.5',uk:'1',eu:'34',cm:'25.4'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'7',womens:'8.5',uk:'2',eu:'35',cm:'26'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'8',womens:'9.5',uk:'3',eu:'36',cm:'26.7'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'9',womens:'10.5',uk:'4',eu:'37',cm:'27.3'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'10',womens:'11.5',uk:'5',eu:'38',cm:'28'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'11',womens:'12.5',uk:'6',eu:'39',cm:'28.6'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'12',womens:'13.5',uk:'7',eu:'40',cm:'29.2'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'13',womens:'14.5',uk:'8',eu:'41',cm:'29.8'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'14',womens:'15.5',uk:'9',eu:'42',cm:'30.5'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'15',womens:'16.5',uk:'10',eu:'43',cm:'31.1'},
            {baby:'',toddler:'',kids:'',youth:'',mens:'16',womens:'',uk:'11',eu:'44',cm:'31.8'},
        ];
        const shoeFields = ['baby','toddler','kids','youth','mens','womens','uk','eu','cm'];

        function buildShoeTable() {
            const tbody = document.getElementById('shoesSizeGuideBody');
            tbody.innerHTML = '';
            shoeSizeData.forEach((row, idx) => {
                const tr = document.createElement('tr');
                shoeFields.forEach(field => {
                    const td = document.createElement('td');
                    td.style.cssText = 'padding:4px;border:1px solid #e5e7eb;text-align:center;';
                    td.innerHTML = `<input type="text" name="shoe_${idx}_${field}" class="input shoe-guide-input" value="${row[field]}" style="padding:4px;font-size:0.85rem;text-align:center;width:100%;box-sizing:border-box;" placeholder="-">`;
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
        }

        function toggleSizeGuideType() {
            const type = document.querySelector('input[name="size_guide_type"]:checked').value;
            document.getElementById('clothingSizeGuide').style.display = type === 'clothing' ? 'block' : 'none';
            document.getElementById('shoesSizeGuide').style.display = type === 'shoes' ? 'block' : 'none';
        }

        // Fetch next product ID on page load
        document.addEventListener('DOMContentLoaded', function() {
            fetch('{{ route("admin.products.next-id") }}')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('productIdDisplay').value = data.product_id;
                    document.getElementById('productIdHidden').value = data.product_id;
                })
                .catch(() => {});
            buildShoeTable();
        });

        function addColorQuantityRow() {
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

            row.innerHTML = `
                <div style="display:grid;grid-template-columns:1fr 1fr 80px auto;gap:8px;align-items:center;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <input type="color" name="color_${index}" value="#000000" style="width:50px;height:40px;border:none;border-radius:8px;cursor:pointer;padding:0;flex-shrink:0;">
                    <input type="text" class="input" name="color_name_${index}" placeholder="Color name (e.g., Navy Blue)" style="flex:1;padding:6px;font-size:0.9rem;flex:1;">
                </div>

                    <input type="text" class="input" name="size_${index}" placeholder="Size (e.g., S, M, L, XL, 42)" style="padding:6px;font-size:0.9rem;">
                    <input type="number" class="input" name="quantity_${index}" placeholder="Qty" min="1" style="padding:6px;font-size:0.9rem;">
                    <button type="button" class="btn btn-secondary" onclick="this.closest('div[style*=border]').remove(); updateColors();" style="padding:4px 8px;font-size:0.85rem;">Remove</button>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.85rem;font-weight:600;">Price for this color (UGX)</label>
                    <input type="number" class="input" name="price_${index}" placeholder="Leave blank to use base price" step="0.01" min="0" style="padding:6px;font-size:0.9rem;">
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:6px;">
                    <label style="font-size:0.85rem;font-weight:600;">Images for this color (optional)</label>
                    <input type="file" name="color_images_${index}[]" multiple accept="image/*" onchange="previewColorImages(event, ${index})" style="font-size:0.85rem;">
                    <div id="colorImagePreview_${index}" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(80px, 1fr));gap:8px;margin-top:4px;"></div>
                </div>
            `;
            container.appendChild(row);
        }

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
                        img.style.border = '2px solid #e5e7eb';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        }

        function updateColors() {
           const container = document.getElementById('colorQuantityContainer');
           const colors = [];
           const colorNames = [];
    
           container.querySelectorAll('.color-row').forEach(row => {
           const colorInput = row.querySelector('input[type="color"]');
           const nameInput = row.querySelector('input[name^="color_name_"]');
           if (colorInput && nameInput && nameInput.value.trim()) {
            colors.push(colorInput.value);
            colorNames.push(colorInput.value + ':' + nameInput.value.trim());
           }
          });
    
           document.getElementById('colorsHidden').value = JSON.stringify(colorNames);
        }


        document.addEventListener('input', function(e) {
            if (e.target.name.startsWith('color_')) {
                updateColors();
            }
        });

        document.querySelector('form').addEventListener('submit', function(e) {
            const type = document.querySelector('input[name="size_guide_type"]:checked').value;
            let sizeGuideData = {};

            if (type === 'clothing') {
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
            } else {
                // Shoes: collect all the shoe inputs
                const shoeFields = ['baby','toddler','kids','youth','mens','womens','uk','eu','cm'];
                sizeGuideData.shoes = [];
                shoeSizeData.forEach((_, idx) => {
                    const row = {};
                    shoeFields.forEach(field => {
                        const input = document.querySelector(`input[name="shoe_${idx}_${field}"]`);
                        if (input && input.value.trim()) {
                            row[field] = input.value.trim();
                        }
                    });
                    if (Object.keys(row).length > 0) {
                        sizeGuideData.shoes.push(row);
                    }
                });
            }

            document.getElementById('sizeGuideHidden').value = JSON.stringify(sizeGuideData);
        });

        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = document.querySelector('input[name="_token"]').value;
            document.head.appendChild(meta);
        }

        addColorQuantityRow();

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
                         wrapper.draggable = true;
                         wrapper.innerHTML = `
                             <img src="${e.target.result}" style="width:100%;height:120px;object-fit:cover;border-radius:8px;border:2px solid #e5e7eb;">
                             <button type="button" onclick="removeImage(this)" style="position:absolute;top:4px;right:4px;background:#ef4444;color:#fff;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-weight:bold;font-size:1rem;line-height:1;">×</button>
                             <span style="position:absolute;top:4px;left:4px;background:#000;color:#fff;padding:2px 6px;border-radius:4px;font-size:0.75rem;">${index + 1}</span>
                        `;
                        previewContainer.appendChild(wrapper);
                    };
                      reader.readAsDataURL(file);
                });
            }
        }

        function removeImage(btn) {
            btn.closest('div[style*="position: relative"]').remove();
        }


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
                    `;
                };
                reader.readAsDataURL(file);
            }
        }

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
                    const primarySelect = document.querySelector('select[name="category_id"]');
                    const option = document.createElement('option');
                    option.value = data.category.id;
                    option.textContent = data.category.name;
                    primarySelect.appendChild(option);

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

        document.getElementById('addCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeAddCategoryModal();
        });

    </script>
@endsection