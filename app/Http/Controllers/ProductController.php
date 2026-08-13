<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request, RecommendationService $recommendations)
    {
        $search = $request->query('search');
        $categorySlug = $request->query('category');

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

        $userId = Auth::id();
        $guestId = $request->session()->getId();
        $searchCorrection = null;

        if ($search) {
            $result = $recommendations->search($search, $categorySlug, $userId, $guestId);
            $products = $result['products'];
            $searchCorrection = $result['bestMatch'];
        } else {
            $query = Product::with('primaryImage', 'category')
                ->where('is_active', true)
                ->when($categorySlug && $categorySlug !== 'all', function ($q) use ($categorySlug) {
                    $q->where(function ($sub) use ($categorySlug) {
                        $sub->whereHas('category', fn ($cq) => $cq->where('slug', $categorySlug))
                            ->orWhereHas('categories', fn ($cq) => $cq->where('slug', $categorySlug));
                    });
                });

            $products = $recommendations->orderProducts($query->get(), $userId, $guestId, Auth::user()?->gender);
        }

        $cartQuantities = [];
        if ($userId) {
            CartItem::where('user_id', $userId)
                ->whereIn('product_id', $products->pluck('id'))
                ->get(['product_id', 'quantity'])
                ->each(function ($item) use (&$cartQuantities) {
                    $cartQuantities[$item->product_id] = ($cartQuantities[$item->product_id] ?? 0) + $item->quantity;
                });
        } else {
            foreach (session('guest_cart', []) as $cartItem) {
                $cartQuantities[$cartItem['product_id']] = ($cartQuantities[$cartItem['product_id']] ?? 0) + $cartItem['quantity'];
            }
        }

        $categories = Category::orderBy('name')->get();

        $frequentCategorySlugs = Cache::get('frequent_categories', []);
        $frequentSlugs = array_keys($frequentCategorySlugs);
        $suggestedCategories = collect();
        if (!empty($frequentSlugs)) {
            $case = 'CASE ' . implode(' ', array_map(
                fn($s, $i) => "WHEN slug = '" . str_replace("'", "''", $s) . "' THEN {$i}",
                $frequentSlugs,
                array_keys($frequentSlugs)
            )) . ' END';
            $suggestedCategories = Category::whereIn('slug', $frequentSlugs)
                ->orderByRaw($case)
                ->take(5)
                ->get();
        }

        return view('shop.index', compact('products', 'categories', 'search', 'categorySlug', 'suggestedCategories', 'searchCorrection', 'cartQuantities'));
    }

    public function show($slug, Request $request, RecommendationService $recommendations)
    {
        $product = Product::with('images', 'category')->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $recommendations->trackView($product->id, Auth::id(), $request->session()->getId());

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

        $suggestedProducts = $recommendations->relatedFor($product, 4);

        return view('shop.show', compact('product', 'inWishlist', 'reviews', 'avgRating', 'reviewCount', 'suggestedProducts'));
    }
}
