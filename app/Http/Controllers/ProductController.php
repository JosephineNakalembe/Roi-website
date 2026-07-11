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

        // Track frequently searched terms (keep top 100)
        if ($search) {
            $this->trackFrequency('frequent_searches', strtolower(trim($search)), 100);
        }

        // Track frequently viewed categories (keep top 20)
        if ($categorySlug) {
            $this->trackFrequency('frequent_categories', $categorySlug, 20);
        }

        $query = Product::with('primaryImage', 'category')
            ->where('is_active', true)
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($cq) => $cq->where('slug', $categorySlug)));

        // Randomize order for non-filtered views, stable for filtered
        if (!$search && !$categorySlug) {
            $query->inRandomOrder();
        } else {
            $query->latest();
        }

        // If AJAX request, return only the product cards HTML + next page URL
        if ($request->ajax()) {
            $products = $query->paginate(12)->withQueryString();
            $html = view('shop.partials.product_cards', compact('products'))->render();
            return response()->json([
                'html' => $html,
                'next_page_url' => $products->nextPageUrl(),
            ]);
        }

        $products = $query->paginate(12)->withQueryString();
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

    /**
     * Increment a hit counter for $key inside the given cache bucket, keeping
     * only the $limit most frequent entries.
     */
    private function trackFrequency(string $cacheKey, string $key, int $limit): void
    {
        $counts = Cache::get($cacheKey, []);
        $counts[$key] = ($counts[$key] ?? 0) + 1;
        arsort($counts);
        Cache::forever($cacheKey, array_slice($counts, 0, $limit));
    }
}