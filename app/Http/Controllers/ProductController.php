<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categorySlug = $request->query('category');
        $page = (int) $request->get('page', 1);
        $perPage = 12;
        $isAjax = $request->header('X-Requested-With') === 'XMLHttpRequest';
        $shuffleId = $request->query('sid');

        if ($search) {
            $frequentSearches = Cache::get('frequent_searches', []);
            $term = strtolower(trim($search));
            if (!isset($frequentSearches[$term])) {
                $frequentSearches[$term] = 0;
            }
            $frequentSearches[$term]++;
            arsort($frequentSearches);
            $frequentSearches = array_slice($frequentSearches, 0, 100);
            Cache::forever('frequent_searches', $frequentSearches);
        }

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

        $allProducts = $query->latest()->get();

        $inStockProducts = $allProducts->where('stock', '>', 0);
        $outOfStockProducts = $allProducts->where('stock', '<=', 0);

        $useShuffle = !$search && (!$categorySlug || $categorySlug === 'all');

        if ($useShuffle) {
            // Fresh page load (not AJAX, page 1): create a new shuffle order
            if (!$isAjax && $page <= 1 && !$shuffleId) {
                $shuffleId = Str::random(16);
                $shuffledIds = $inStockProducts->pluck('id')->shuffle()->values()->toArray();
                $oosIds = $outOfStockProducts->pluck('id')->values()->toArray();
                Cache::put("shop_order_{$shuffleId}", array_merge($shuffledIds, $oosIds), 3600);
            }

            if ($shuffleId && Cache::has("shop_order_{$shuffleId}")) {
                $orderedIds = Cache::get("shop_order_{$shuffleId}");
                $allMap = $allProducts->keyBy('id');
                $products = collect($orderedIds)->filter(fn($id) => $allMap->has($id))->map(fn($id) => $allMap[$id])->values();
            } else {
                // Fallback: no cached order, use in-stock first then out-of-stock
                $products = $inStockProducts->concat($outOfStockProducts);
            }
        } else {
            // For search/category: in-stock first (latest order), then out-of-stock
            $products = $inStockProducts->concat($outOfStockProducts);
        }

        $total = $products->count();
        $slicedProducts = $products->slice(($page - 1) * $perPage, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $slicedProducts,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($isAjax) {
            return response()->json([
                'html' => view('shop.partials.product_cards', compact('paginator'))->render(),
                'next_page_url' => $paginator->nextPageUrl(),
            ]);
        }

        $products = $paginator;
        $categories = Category::orderBy('name')->get();

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

        return view('shop.index', compact('products', 'categories', 'search', 'categorySlug', 'suggestedCategories', 'shuffleId'));
    }

    public function show($slug)
    {
        $product = Product::with('images', 'category')->where('slug', $slug)->where('is_active', true)->firstOrFail();
        $inWishlist = false;

        if (Auth::check()) {
            $inWishlist = Auth::user()->wishlistItems()->where('product_id', $product->id)->exists();
        }

        $orderItemIds = \App\Models\OrderItem::where('product_id', $product->id)->pluck('id');
        $reviews = \App\Models\OrderItemReview::whereIn('order_item_id', $orderItemIds)
            ->with('user')
            ->latest()
            ->get();

        $avgRating = $reviews->avg('rating');
        $reviewCount = $reviews->count();

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
