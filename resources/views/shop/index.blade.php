@extends('layouts.app')

@section('title', request()->routeIs('home') ? 'ROI Store' : 'Shop | ROI Store')
@section('meta_description', 'Shop fashion, beauty, accessories, home and more at ROI Store — affordable prices across Uganda with fast delivery.')

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
                font-size: 0.75rem !important;
                padding: 4px 8px !important;
            }
            .product-card h2 {
                font-size: 0.7rem !important;
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
            @if($search)
                @include('partials.back-button', ['fallback' => route('shop.index')])
            @endif
            Shop
            <form method="GET" action="{{ route('shop.index') }}" class="flex gap-1.5 flex-wrap" style="max-width:1000px;margin:0;">
                <div class="search-wrapper relative">
                    <input class="input" type="search" name="search" value="{{ $search ?? '' }}" placeholder="Search" class="w-35 px-2 py-1.5 pr-8">
                </div>
            </form>
        </h1>
    </div>

    @php
        $defaults = ['all' => 'All', 'women' => 'Women', 'men' => 'Men', 'beauty' => 'Beauty'];
        $otherCategories = $categories->filter(fn($c) => !in_array($c->slug, array_keys($defaults)));
        $hasMore = $otherCategories->isNotEmpty();
    @endphp

    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;align-items:center;">
        @foreach($defaults as $slug => $label)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $slug])) }}" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.8rem;text-decoration:none;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;{{ $categorySlug === $slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $label }}</a>
        @endforeach
        @if($hasMore)
            <button onclick="toggleCategories()" id="catToggle" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.8rem;border:none;background:#f1f3f5;color:#1a1a2e;cursor:pointer;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;">+{{ $otherCategories->count() }} more</button>
        @endif
    </div>

    @if($hasMore)
    <div id="extraCategories" style="display:none;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
        @foreach($otherCategories as $category)
            <a href="{{ route('shop.index', array_merge(request()->query(), ['category' => $category->slug])) }}" class="category-pills" style="padding:5px 12px;border-radius:6px;font-size:0.8rem;text-decoration:none;font-weight:500;white-space:nowrap;text-align:center;display:inline-flex;align-items:center;justify-content:center;{{ $categorySlug === $category->slug ? 'background:#1a1a2e;color:#fff;' : 'background:#f1f3f5;color:#1a1a2e;' }}transition:all 0.2s;">{{ $category->name }}</a>
        @endforeach
    </div>
    @endif

    @if($searchCorrection && $search)
        <div style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:0.85rem;color:#795548;">
            No exact match for <strong>"{{ $search }}"</strong>. Showing closest results for <strong>"{{ $searchCorrection }}"</strong>.
        </div>
    @endif

    <div id="productGrid" style="display:grid;gap:10px;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));">
        @forelse($products as $product)
            @include('shop.partials.product-card', ['product' => $product])
        @empty
            <div class="card" style="grid-column:1/-1;">No products found.</div>
        @endforelse
    </div>

    <script>
        function setCategoriesExpanded(expanded) {
            try { sessionStorage.setItem('shop_categories_expanded', expanded ? '1' : '0'); } catch(e) {}
        }

        function applyCategoriesState() {
            const extra = document.getElementById('extraCategories');
            const btn = document.getElementById('catToggle');
            if (!extra || !btn) return;
            const expanded = sessionStorage.getItem('shop_categories_expanded') === '1';
            extra.style.display = expanded ? 'flex' : 'none';
            btn.textContent = expanded ? 'Show less' : '+{{ $otherCategories->count() }} more';
        }

        function toggleCategories() {
            const extra = document.getElementById('extraCategories');
            setCategoriesExpanded(extra.style.display !== 'flex');
            applyCategoriesState();
        }

        document.addEventListener('DOMContentLoaded', applyCategoriesState);
    </script>
@endsection
