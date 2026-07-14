@extends('layouts.app')

@section('content')
    <style>
        @media (max-width: 768px) {
            .shop-header {
                flex-direction: row !important;
                align-items: center !important;
            }
            .shop-header h1 {
                flex-direction: row !important;
                align-items: center !important;
            }
            .shop-header form {
                width: 100% !important;
                max-width: 80% !important;
                margin: 0 !important;
            }
            .shop-header input {
                width: 100% !important;
                font-size: 0.75rem !important;
                padding: 4px 12px !important;
            }
            .shop-header button {
                font-size: 0.75rem !important;
                padding: 8px 12px !important;
            }
            #productGrid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 10px !important;
            }
            .category-pills {
                font-size: 0.65rem !important;
                padding: 4px 8px !important;
            }
            .product-card h2 {
                font-size: 0.6rem !important;
                font-weight: 400 !important;
            }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            #productGrid {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }
        @media (min-width: 1025px) {
            #productGrid {
                grid-template-columns: repeat(4, 1fr) !important;
            }
        }
        .search-wrapper {
            position: relative;
        }
        .search-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
    <div class="shop-header flex justify-between items-center gap-2 flex-wrap mb-2" style="margin-bottom:20px;">
        <h1 class="mb-0 flex gap-2 items-center flex-wrap">
            Shop
            <form method="GET" action="{{ route('shop.index') }}" class="flex gap-1.5 flex-wrap" style="max-width:1000px;margin:0;">
                <div class="search-wrapper relative">
                    <input class="input" type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search" class="w-35 px-2 py-1.5 pr-8">
                </div>
            </form>
        </h1>
    </div>

    @php
        $defaults = ['all' => 'All', 'women-s-clothing' => 'Women', 'men-s-clothing' => 'Men', 'beauty-personal-care' => 'Beauty'];
        $otherCategories = $categories->filter(fn($c) => !in_array($c->slug, array_keys($defaults)));
        $hasMore = $otherCategories->isNotEmpty();
    @endphp

    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;align-items:center;">
        @foreach($defaults as $slug => $label)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $slug])) }}" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.7rem;text-decoration:none;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;{{ $categorySlug === $slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $label }}</a>
        @endforeach
        @if($hasMore)
            <button onclick="toggleCategories()" id="catToggle" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.7rem;border:none;background:#f1f3f5;color:#1a1a2e;cursor:pointer;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">+{{ $otherCategories->count() }} more</button>
        @endif
    </div>

    @if($hasMore)
    <div id="extraCategories" style="display:none;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
        @foreach($otherCategories as $category)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $category->slug])) }}" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.7rem;text-decoration:none;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;{{ $categorySlug === $category->slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $category->name }}</a>
        @endforeach
    </div>
    @endif

    <div id="productGrid" style="display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
        @forelse($products as $product)
            <a href="{{ route('shop.show', $product->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:10px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
                <img src="{{ optional($product->primaryImage)->path ? asset('storage/' . $product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;" loading="lazy">
                <div style="padding:10px 12px 12px;">
                    <h2 style="font-size:0.65rem;font-weight:600;margin-bottom:2px;">{{ $product->name }}</h2>
                    <p style="font-weight:900;font-size:0.85rem;">UGX{{ number_format($product->price, 0) }}</p>
                    @if($product->stock <= 0)
                        <span class="badge badge-red">Out of Stock</span>
                    @elseif($product->stock <= 2)
                        <span style="font-size:0.6rem;color:#c62828;">Only {{ $product->stock }} left</span>
                    @endif
                </div>
            </a>
        @empty
            <div class="card" style="grid-column:1/-1;">No products found.</div>
        @endforelse
    </div>

    <div id="loadingState" style="display:none;text-align:center;margin-top:20px;padding:20px;">
        <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#1a1a2e;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
        <p style="margin-top:8px;color:#6b7280;">Loading more products…</p>
    </div>
    <div id="endState" style="display:none;text-align:center;margin-top:20px;color:#6b7280;padding:20px;">No more products to show.</div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>

    <script>
        function toggleCategories() {
            const extra = document.getElementById('extraCategories');
            const btn = document.getElementById('catToggle');
            if (extra.style.display === 'flex') {
                extra.style.display = 'none';
                btn.textContent = '+{{ $otherCategories->count() }} more';
            } else {
                extra.style.display = 'flex';
                btn.textContent = 'Show less';
            }
        }

        let nextPageUrl = @json($products->nextPageUrl());
        let isLoading = false;
        let hasMore = true;

        async function loadMoreProducts() {
            if (!nextPageUrl || isLoading || !hasMore) return;
            isLoading = true;
            document.getElementById('loadingState').style.display = 'block';

            try {
                const response = await fetch(nextPageUrl, { 
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    } 
                });
                
                if (!response.ok) {
                    hasMore = false;
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('endState').style.display = 'block';
                    isLoading = false;
                    return;
                }

                const data = await response.json();
                
                if (data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    const cards = temp.querySelectorAll('.product-card');
                    cards.forEach(card => document.getElementById('productGrid').appendChild(card));
                }
                
                nextPageUrl = data.next_page_url || null;
                
                if (!nextPageUrl) {
                    hasMore = false;
                    document.getElementById('endState').style.display = 'block';
                }
            } catch (e) {
                hasMore = false;
                document.getElementById('endState').textContent = 'Error loading products.';
                document.getElementById('endState').style.display = 'block';
            }

            document.getElementById('loadingState').style.display = 'none';
            isLoading = false;
        }

        // Infinite scroll with debounce
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 800) {
                    loadMoreProducts();
                }
            }, 100);
        });

        // Also load more when page is near bottom on load
        document.addEventListener('DOMContentLoaded', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 800) {
                loadMoreProducts();
            }
        });
    </script>
@endsection
