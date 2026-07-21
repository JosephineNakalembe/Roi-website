@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content header-content-between">
            <div class="header-title-row">
                @include('partials.back-button', ['fallback' => route('admin.dashboard')])
                <h1 class="mb-0">Out of Stock</h1>
            </div>
            <a class="btn" href="{{ route('admin.products.index') }}">All Products</a>
        </div>
    </div>
    <div class="card">
        <style>
            @media (max-width: 768px) {
                .admin-search-form { width: 100% !important; }
                .admin-search-form > div { flex: 1 !important; min-width: 100% !important; }
                .product-item { flex-direction: column !important; align-items: flex-start !important; }
                .product-item > div:first-child { width: 100% !important; height: 120px !important; }
                .product-item > div:last-child { width: 100% !important; flex-wrap: wrap !important; }
            }
        </style>

        <p class="text-muted" style="margin-top:12px;">Products that need restocking.</p>

        <form class="admin-search-form" method="GET" action="{{ route('admin.products.out-of-stock') }}" style="display:flex;flex-wrap:wrap;gap:12px;margin-top:16px;padding:16px;background:#f9fafb;border-radius:12px;border:1px solid #e5e7eb;">
            <div style="flex:1;min-width:200px;">
                <input type="text" name="search" class="input" placeholder="Search by name or product ID..." value="{{ request('search') }}" style="width:100%;">
            </div>
            <div style="display:flex;gap:8px;">
                @if(request('search'))
                    <a href="{{ route('admin.products.out-of-stock') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </form>

        <div id="productList" style="display:grid;gap:14px;margin-top:16px;">
            @forelse($products as $product)
                <div class="product-item" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid #e5e7eb;border-radius:14px;">
                    <div style="width:64px;height:64px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f3f4f6;">
                        @if($product->primaryImage)
                            <img src="{{ media_url($product->primaryImage->path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:0.85rem;">No<br>Image</div>
                        @endif
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <strong style="font-size:1.1rem;">{{ $product->name }}</strong>
                            <span style="font-size:0.9rem;color:#6b7280;background:#f3f4f6;padding:2px 8px;border-radius:4px;">{{ $product->product_id }}</span>
                            <span style="font-size:0.8rem;color:#fff;background:#dc2626;padding:2px 8px;border-radius:4px;">Out of Stock</span>
                        </div>
                        <div class="text-muted" style="font-size:0.95rem;margin-top:2px;">
                            @if($product->categories->isNotEmpty())
                                {{ $product->categories->pluck('name')->implode(', ') }}
                            @elseif($product->category)
                                {{ $product->category->name }}
                            @else
                                Uncategorized
                            @endif
                            • UGX{{ number_format($product->price, 2) }}
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;flex-shrink:0;">
                        <button class="btn btn-secondary" onclick="openStockModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->stock }}')" style="padding:6px 14px;font-size:0.95rem;background:#059669;color:#fff;border:none;">+ Stock</button>
                        <a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}" style="padding:6px 14px;font-size:0.95rem;">Edit</a>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:40px;color:#9ca3af;">
                    <p style="font-size:1.2rem;">All products are in stock!</p>
                </div>
            @endforelse
        </div>

        <div id="loadingState" style="display:none;text-align:center;margin-top:20px;padding:20px;">
            <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#1a1a2e;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
            <p style="margin-top:8px;color:#6b7280;">Loading more products…</p>
        </div>
        <div id="endState" style="display:none;text-align:center;margin-top:12px;color:#6b7280;padding:12px;">All out-of-stock products loaded.</div>

        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

        <script>
        function addAjaxParam(url) {
            if (!url) return null;
            const sep = url.includes('?') ? '&' : '?';
            return url + sep + '_ajax=1';
        }

        let nextPageUrl = @json($products->nextPageUrl());
        let isLoading = false;
        let hasMore = {{ $products->hasMorePages() ? 'true' : 'false' }};

        async function loadMoreProducts() {
            if (!nextPageUrl || isLoading || !hasMore) return;
            isLoading = true;
            document.getElementById('loadingState').style.display = 'block';

            try {
                const url = addAjaxParam(nextPageUrl);
                const response = await fetch(url);
                if (!response.ok) { hasMore = false; document.getElementById('loadingState').style.display = 'none'; document.getElementById('endState').style.display = 'block'; isLoading = false; return; }
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) { hasMore = false; document.getElementById('loadingState').style.display = 'none'; document.getElementById('endState').style.display = 'block'; isLoading = false; return; }
                const data = await response.json();
                if (data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const items = temp.querySelectorAll('.product-item');
                    items.forEach(item => document.getElementById('productList').appendChild(item));
                }
                nextPageUrl = data.next_page_url || null;
                if (!nextPageUrl) { hasMore = false; document.getElementById('endState').style.display = 'block'; }
            } catch (e) {
                hasMore = false;
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('endState').style.display = 'block';
            }
            document.getElementById('loadingState').style.display = 'none';
            isLoading = false;
        }

        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 800) { loadMoreProducts(); }
            }, 100);
        });
        </script>
    </div>

    <!-- Add Stock Modal -->
    <div id="stockModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;padding:24px;max-width:440px;width:90%;margin:auto;">
            <h2 style="margin:0 0 8px 0;">Add Stock</h2>
            <p id="stockProductName" style="color:#6b7280;font-size:1rem;margin-bottom:16px;"></p>
            <form id="stockForm" method="POST" style="display:grid;gap:12px;">
                @csrf
                <div>
                    <label>Quantity to add <span style="color:#dc2626;">*</span></label>
                    <input type="number" class="input" name="quantity" min="1" value="1" required style="padding:10px;font-size:1.1rem;">
                </div>
                <div>
                    <label>New Cost Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.9rem;">— optional</span></label>
                    <input type="number" class="input" name="cost_price" step="0.01" min="0" placeholder="Leave blank to keep current" style="padding:10px;font-size:1.1rem;">
                </div>
                <div>
                    <label>New Selling Price (UGX) <span class="text-muted" style="font-weight:400;font-size:0.9rem;">— optional</span></label>
                    <input type="number" class="input" name="price" step="0.01" min="0" placeholder="Leave blank to keep current" style="padding:10px;font-size:1.1rem;">
                </div>
                <p style="font-size:0.95rem;color:#6b7280;" id="currentStockDisplay">Current stock: 0</p>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeStockModal()">Cancel</button>
                    <button class="btn" type="submit" style="background:#059669;color:#fff;">Add Stock</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openStockModal(id, name, currentStock) {
        document.getElementById('stockProductName').textContent = name;
        document.getElementById('currentStockDisplay').textContent = 'Current stock: ' + currentStock;
        document.getElementById('stockForm').action = '{{ route("admin.products.add-stock", "__ID__") }}'.replace('__ID__', id);
        document.getElementById('stockModal').style.display = 'flex';
    }
    function closeStockModal() {
        document.getElementById('stockModal').style.display = 'none';
    }
    document.getElementById('stockModal').addEventListener('click', function(e) {
        if (e.target === this) closeStockModal();
    });
    </script>
@endsection
