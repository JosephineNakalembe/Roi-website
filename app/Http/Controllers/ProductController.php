<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $categorySlug = $request->query('category');

        $products = Product::with('primaryImage', 'category')
            ->where('is_active', true)
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($categorySlug, fn ($query) => $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories', 'search', 'categorySlug'));
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

        return view('shop.show', compact('product', 'inWishlist', 'reviews', 'avgRating', 'reviewCount'));
    }
}
