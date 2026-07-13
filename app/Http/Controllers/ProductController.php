<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categorySlug = $request->query('category');

        // Track frequently searched terms (store in cache, max 100)
        if ($search) {
            $frequentSearches = Cache::get('frequent_searches', []);
            $term = strtolower(trim($search));
            if (!isset($frequentSearches[$term])) {
                $frequentSearches[$term] = 0;
            }
            $frequentSearches[$term]++;
            // Keep only top 100
            arsort($frequentSearches);
            $frequentSearches = array_slice($frequentSearches, 0, 100);
            Cache::forever('frequent_searches', $frequentSearches);
        }

        // Track frequently viewed categories
        if ($categorySlug) {
            $frequentCategories = Cache::get('frequent_categories', []);
            if (!isset($frequentCategories[$categorySlug])) {
                $frequentCategories[$categorySlug] = 0;
            }
            $frequentCategories[$categorySlug]++;
            arsort($frequentCategories);
            $frequentCategories = array_slice($frequentCategories, 0, 20);
            Cache::forever('frequent_categories', $frequentCategories);
        }

        $query = Product::with('primaryImage', 'category')
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categorySlug && $categorySlug !== 'all', fn ($q) => $q->whereHas('category', fn ($cq) => $cq->where('slug', $categorySlug)));

        // Separate in-stock and out-of-stock products
        if (!$search && (!$categorySlug || $categorySlug === 'all')) {
            // Get all products first to separate them
            $allProducts = $query->get();
            $inStockProducts = $allProducts->where('stock', '>', 0);
            $outOfStockProducts = $allProducts->where('stock', '<=', 0);

            // Shuffle only in-stock products
            $shuffledInStock = $inStockProducts->shuffle();

            // Combine: shuffled in-stock first, then out-of-stock
            $products = $shuffledInStock->concat($outOfStockProducts);

            // Create a paginator manually
            $page = $request->get('page', 1);
            $perPage = 12;
            $offset = ($page - 1) * $perPage;
            $slicedProducts = $products->slice($offset, $perPage);
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedProducts,
                $products->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // If AJAX request, return only the product cards HTML + next page URL
            if ($request->ajax()) {
                $html = view('shop.partials.product_cards', compact('paginator'))->render();
                return response()->json([
                    'html' => $html,
                    'next_page_url' => $paginator->nextPageUrl(),
                ]);
            }

            $products = $paginator;
        } else {
            // For filtered views, use latest order but still push out-of-stock to bottom
            $allProducts = $query->latest()->get();
            $inStockProducts = $allProducts->where('stock', '>', 0);
            $outOfStockProducts = $allProducts->where('stock', '<=', 0);

            // Combine: in-stock first (latest order), then out-of-stock (latest order)
            $products = $inStockProducts->concat($outOfStockProducts);

            // Create a paginator manually
            $page = $request->get('page', 1);
            $perPage = 12;
            $offset = ($page - 1) * $perPage;
            $slicedProducts = $products->slice($offset, $perPage);
            $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $slicedProducts,
                $products->count(),
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            // If AJAX request, return only the product cards HTML + next page URL
            if ($request->ajax()) {
                $html = view('shop.partials.product_cards', compact('paginator'))->render();
                return response()->json([
                    'html' => $html,
                    'next_page_url' => $paginator->nextPageUrl(),
                ]);
            }

            $products = $paginator;
        }

        $categories = Category::orderBy('name')->get();

        // Get frequently searched categories for suggestions
        $frequentCategorySlugs = Cache::get('frequent_categories', []);
        $frequentSlugs = array_keys($frequentCategorySlugs);
        $suggestedCategories = collect();
        if (!empty($frequentSlugs)) {
            $orderBy = implode(',', array_map(fn($s) => "'" . str_replace("'", "''", $s) . "'", $frequentSlugs));
            $suggestedCategories = Category::whereIn('slug', $frequentSlugs)
                ->orderByRaw("FIELD(slug, {$orderBy})")
                ->take(5)
                ->get();
        }

        return view('shop.index', compact('products', 'categories', 'search', 'categorySlug', 'suggestedCategories'));
    }

    public function show($slug)
    {
        $product = Product::with('images', 'category')->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $inWishlist = false;

        if (Auth::check()) {
            $inWishlist = Auth::user()->wishlistItems()->where('product_id', $product->id)->exists();
        }

        // Fetch reviews via OrderItem
        $orderItemIds = \App\Models\OrderItem::where('product_id', $product->id)->pluck('id');
        $reviews = \App\Models\OrderItemReview::whereIn('order_item_id', $orderItemIds)
            ->with('user')
            ->latest()
            ->get();

        // Calculate average rating
        $avgRating = $reviews->avg('rating');
        $reviewCount = $reviews->count();

        // Get suggested products from same category
        $suggestedProducts = Product::with('primaryImage')
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product) {
                if ($product->category_id) {
                    $q->where('category_id', $product->category_id);
                }
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('shop.show', compact('product', 'inWishlist', 'reviews', 'avgRating', 'reviewCount', 'suggestedProducts'));
    }
}