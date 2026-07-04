<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $cartItems = $user->cartItems()->with('product.primaryImage')->get();

        $items = $cartItems->map(function ($cartItem) {
            $product = $cartItem->product;
            if (!$product) return null;

            $unitPrice = $product->priceForColor($cartItem->color);
            return [
                'product' => $product,
                'color' => $cartItem->color,
                'size' => $cartItem->size,
                'quantity' => $cartItem->quantity,
                'cart_key' => $cartItem->id,
                'selected' => $cartItem->selected,
                'unit_price' => $unitPrice,
                'total' => $unitPrice * $cartItem->quantity,
            ];
        })->filter();

        $availableItems = $items->filter(fn($item) => $item['product']->stock > 0);
        $outOfStockItems = $items->filter(fn($item) => $item['product']->stock < 1);
        $selectedItems = $availableItems->filter(fn($item) => $item['selected']);
        $subtotal = $selectedItems->sum('total');

        // Get suggestions based on popular categories
        $frequentCategorySlugs = Cache::get('frequent_categories', []);
        $popularCategoryIds = Category::whereIn('slug', array_keys($frequentCategorySlugs))->pluck('id');

        $suggestions = Product::with('primaryImage')
            ->where('is_active', true)
            ->whereNotIn('id', $items->pluck('product.id')->filter()->values())
            ->when($popularCategoryIds->isNotEmpty(), function ($q) use ($popularCategoryIds) {
                $q->whereIn('category_id', $popularCategoryIds);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Also get suggested categories shown as tags
        $suggestedCategories = Category::whereIn('slug', array_keys($frequentCategorySlugs))
            ->take(5)
            ->get();

        return view('cart.index', compact('availableItems', 'outOfStockItems', 'subtotal', 'suggestions', 'suggestedCategories'));
    }

    public function add(Request $request, Product $product)
    {
        if ($product->stock < 1) {
            return back()->withErrors(['This product is out of stock and cannot be added to the cart.']);
        }

        $color = $request->input('color');
        $size = $request->input('size');
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $user = Auth::user();

        // Check if this product variant already exists in the user's cart
        $existingItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->where('color', $color ?: null)
            ->where('size', $size ?: null)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $data['quantity'];
            $existingItem->update([
                'quantity' => min($product->stock, $newQty),
                'selected' => true,
            ]);
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => min($product->stock, $data['quantity']),
                'color' => $color ?: null,
                'size' => $size ?: null,
                'selected' => true,
            ]);
        }

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request, Product $product)
    {
        $color = $request->input('color');
        $size = $request->input('size');
        $oldColor = $request->input('old_color');
        $oldSize = $request->input('old_size');
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $user = Auth::user();

        // Find the old cart item
        $oldItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->where('color', $oldColor ?: null)
            ->where('size', $oldSize ?: null)
            ->first();

        if (!$oldItem) {
            return back()->withErrors(['Item not found in cart.']);
        }

        // If color/size changed, delete old and create new
        if ($oldColor !== $color || $oldSize !== $size) {
            // Check if the new variant already exists in cart
            $existingItem = $user->cartItems()
                ->where('product_id', $product->id)
                ->where('color', $color ?: null)
                ->where('size', $size ?: null)
                ->first();

            if ($existingItem) {
                // Merge quantities, then delete old
                $existingItem->update([
                    'quantity' => min($product->stock, $existingItem->quantity + $data['quantity']),
                ]);
                $oldItem->delete();
            } else {
                $oldItem->update([
                    'color' => $color ?: null,
                    'size' => $size ?: null,
                    'quantity' => min($product->stock, $data['quantity']),
                ]);
            }
        } else {
            $oldItem->update([
                'quantity' => min($product->stock, $data['quantity']),
            ]);
        }

        return back();
    }

    public function remove(Request $request, Product $product)
    {
        $color = $request->input('color');
        $size = $request->input('size');

        $user = Auth::user();
        $user->cartItems()
            ->where('product_id', $product->id)
            ->where('color', $color ?: null)
            ->where('size', $size ?: null)
            ->delete();

        return back();
    }

    public function toggleSelect(Request $request)
    {
        $data = $request->validate([
            'cart_item_id' => ['required', 'exists:cart_items,id'],
            'selected' => ['required', 'boolean'],
        ]);

        $item = CartItem::findOrFail($data['cart_item_id']);
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $item->update(['selected' => $data['selected']]);
        return back();
    }

    public function toggleSelectAll(Request $request)
    {
        $data = $request->validate([
            'selected' => ['required', 'boolean'],
        ]);

        Auth::user()->cartItems()
            ->whereHas('product', fn($q) => $q->where('stock', '>', 0))
            ->update(['selected' => $data['selected']]);

        return back();
    }
}