@extends('layouts.app')

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
        <h1 style="margin-bottom:0;">Shop</h1>
        <form method="GET" action="{{ route('shop.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;">
            <input class="input" type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search" style="width:160px;padding:8px 12px;">
            <select class="input" name="category" style="width:auto;padding:8px 12px;">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}"{{ $categorySlug === $category->slug ? ' selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <button class="btn" type="submit" style="padding:8px 16px;">Filter</button>
        </form>
    </div>

    @php
        $defaults = ['women' => 'Women', 'men' => 'Men', 'kids' => 'Kids', 'electronics' => 'Electronics', 'make-up' => 'Make-up'];
        $otherCategories = $categories->filter(fn($c) => !in_array($c->slug, array_keys($defaults)));
        $hasMore = $otherCategories->isNotEmpty();
    @endphp

    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;align-items:center;">
        <a href="{{ route('shop.index', array_filter(['search' => $search ?? null])) }}" class="btn btn-secondary" style="padding:6px 14px;border-radius:999px;font-size:0.85rem;{{ !$categorySlug ? 'opacity:1;' : '' }}">All</a>
        @foreach($defaults as $slug => $label)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $slug])) }}" style="padding:6px 14px;border-radius:999px;font-size:0.85rem;text-decoration:none;font-weight:500;{{ $categorySlug === $slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $label }}</a>
        @endforeach
        @if($hasMore)
            <button onclick="toggleCategories()" id="catToggle" style="padding:6px 14px;border-radius:999px;font-size:0.85rem;border:none;background:#f1f3f5;color:#1a1a2e;cursor:pointer;font-weight:500;">+{{ $otherCategories->count() }} more</button>
        @endif
    </div>

    @if($hasMore)
    <div id="extraCategories" style="display:none;flex-wrap:wrap;gap:6px;margin-bottom:14px;">
        @foreach($otherCategories as $category)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $category->slug])) }}" style="padding:6px 14px;border-radius:999px;font-size:0.85rem;text-decoration:none;font-weight:500;{{ $categorySlug === $category->slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $category->name }}</a>
        @endforeach
    </div>
    @endif

    <div id="productGrid" style="display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
        @forelse($products as $product)
            <a href="{{ route('shop.show', $product->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
                <img src="{{ optional($product->primaryImage)->path ? asset('storage/' . $product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;">
                <div style="padding:12px 14px 14px;">
                    <h2 style="font-size:0.95rem;font-weight:600;margin-bottom:2px;">{{ $product->name }}</h2>
                    <p style="font-size:0.8rem;color:#6c757d;margin-bottom:4px;">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                    <p style="font-weight:700;font-size:1rem;">UGX{{ number_format($product->price, 0) }}</p>
                    @if($product->stock <= 0)
                        <span class="badge badge-red">Out of Stock</span>
                    @elseif($product->stock <= 2)
                        <span style="font-size:0.75rem;color:#c62828;">Only {{ $product->stock }} left</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="card" style="grid-column:1/-1;">No products found.</div>
        @endforelse
    </div>

    <div id="loadingState" style="display:none;text-align:center;margin-top:20px;">Loading more products…</div>
    <div id="endState" style="display:none;text-align:center;margin-top:20px;color:#6b7280;">No more products to show.</div>

    <script>
        function toggleCategories() {
            const extra = document.getElementById('extraCategories');
            const btn = document.getElementById('catToggle');
            if (extra.style.display === 'flex') {
                extra.style.display = 'none';
                btn.textContent = btn.textContent.replace('less', 'more');
                btn.textContent = '+{{ $otherCategories->count() }} more';
            } else {
                extra.style.display = 'flex';
                btn.textContent = 'Show less';
            }
        }

        let nextPageUrl = @json($products->nextPageUrl());
        let isLoading = false;

        async function loadMoreProducts() {
            if (!nextPageUrl || isLoading) return;
            isLoading = true;
            document.getElementById('loadingState').style.display = 'block';

            const response = await fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCards = doc.querySelectorAll('.product-card');
            newCards.forEach(card => document.getElementById('productGrid').appendChild(card));
            nextPageUrl = doc.querySelector('.pagination .page-item .page-link[rel="next"]')?.href || null;

            document.getElementById('loadingState').style.display = 'none';
            if (!nextPageUrl) {
                document.getElementById('endState').style.display = 'block';
            }
            isLoading = false;
        }

        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 700) {
                loadMoreProducts();
            }
        });
    </script>
@endsection
