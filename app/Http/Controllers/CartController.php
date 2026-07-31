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
    protected function getGuestCart(Request $request): array
    {
        return $request->session()->get('guest_cart', []);
    }

    protected function setGuestCart(Request $request, array $cart): void
    {
        $request->session()->put('guest_cart', $cart);
    }

    protected function guestCartKey(Product $product, ?string $color, ?string $size): string
    {
        return $product->id . '|' . ($color ?: '') . '|' . ($size ?: '');
    }

    public function index(Request $request)
    {
        $frequentCategorySlugs = Cache::get('frequent_categories', []);
        $popularCategoryIds = Category::whereIn('slug', array_keys($frequentCategorySlugs))->pluck('id');

        if (!Auth::check()) {
            $guestCart = $this->getGuestCart($request);

            if (empty($guestCart)) {
                return view('cart.index', [
                    'availableItems' => collect(),
                    'outOfStockItems' => collect(),
                    'subtotal' => 0,
                    'totalQuantity' => 0,
                    'suggestions' => collect(),
                    'suggestedCategories' => collect(),
                ]);
            }

            $productIds = array_column($guestCart, 'product_id');
            $products = Product::with('primaryImage')->whereIn('id', $productIds)->get()->keyBy('id');

            $items = collect();
            foreach ($guestCart as $index => $cartItem) {
                $product = $products->get($cartItem['product_id']);
                if (!$product) continue;

                $unitPrice = $product->priceForColor($cartItem['color']);
                $items->push([
                    'product' => $product,
                    'color' => $cartItem['color'],
                    'size' => $cartItem['size'],
                    'quantity' => $cartItem['quantity'],
                    'cart_key' => $index,
                    'selected' => $cartItem['selected'],
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $cartItem['quantity'],
                ]);
            }

            $availableItems = $items->filter(fn($item) => $item['product']->getVariantStock($item['color'], $item['size']) > 0);
            $outOfStockItems = $items->filter(fn($item) => $item['product']->getVariantStock($item['color'], $item['size']) < 1);
            $selectedItems = $availableItems->filter(fn($item) => $item['selected']);
            $subtotal = $selectedItems->sum('total');
            $totalQuantity = $selectedItems->sum('quantity');

            $suggestions = Product::with('primaryImage')
                ->where('is_active', true)
                ->whereNotIn('id', $productIds)
                ->when($popularCategoryIds->isNotEmpty(), function ($q) use ($popularCategoryIds) {
                    $q->whereIn('category_id', $popularCategoryIds);
                })
                ->inRandomOrder()
                ->take(4)
                ->get();

            $suggestedCategories = Category::whereIn('slug', array_keys($frequentCategorySlugs))
                ->take(5)
                ->get();

            return view('cart.index', compact('availableItems', 'outOfStockItems', 'subtotal', 'totalQuantity', 'suggestions', 'suggestedCategories'));
        }

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

        $availableItems = $items->filter(fn($item) => $item['product']->getVariantStock($item['color'], $item['size']) > 0);
        $outOfStockItems = $items->filter(fn($item) => $item['product']->getVariantStock($item['color'], $item['size']) < 1);
        $selectedItems = $availableItems->filter(fn($item) => $item['selected']);
        $subtotal = $selectedItems->sum('total');
        $totalQuantity = $selectedItems->sum('quantity');

        $suggestions = Product::with('primaryImage')
            ->where('is_active', true)
            ->whereNotIn('id', $items->pluck('product.id')->filter()->values())
            ->when($popularCategoryIds->isNotEmpty(), function ($q) use ($popularCategoryIds) {
                $q->whereIn('category_id', $popularCategoryIds);
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        $suggestedCategories = Category::whereIn('slug', array_keys($frequentCategorySlugs))
            ->take(5)
            ->get();

        return view('cart.index', compact('availableItems', 'outOfStockItems', 'subtotal', 'totalQuantity', 'suggestions', 'suggestedCategories'));
    }

    public function add(Request $request, Product $product)
    {
        $color = $request->input('color');
        $size = $request->input('size');
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        // Check per-variant stock
        $maxStock = $product->getVariantStock($color, $size);
        if ($maxStock < 1) {
            return back()->withErrors(['This product variant is out of stock and cannot be added to the cart.']);
        }

        if (!Auth::check()) {
            $cart = $this->getGuestCart($request);
            $key = $this->guestCartKey($product, $color, $size);

            $found = false;
            foreach ($cart as &$item) {
                if (($item['product_id'] . '|' . ($item['color'] ?? '') . '|' . ($item['size'] ?? '')) === $key) {
                    $item['quantity'] = min($maxStock, $item['quantity'] + $data['quantity']);
                    $item['selected'] = true;
                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $cart[] = [
                    'product_id' => $product->id,
                    'quantity' => min($maxStock, $data['quantity']),
                    'color' => $color ?: null,
                    'size' => $size ?: null,
                    'selected' => true,
                ];
            }

            $this->setGuestCart($request, $cart);
            return back()->with('success', 'Product added to cart.');
        }

        $user = Auth::user();

        $existingItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->where('color', $color ?: null)
            ->where('size', $size ?: null)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $data['quantity'];
            $existingItem->update([
                'quantity' => min($maxStock, $newQty),
                'selected' => true,
            ]);
        } else {
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => min($maxStock, $data['quantity']),
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

        // Check new variant stock
        $maxStock = $product->getVariantStock($color, $size);
        if ($maxStock < 1) {
            return back()->withErrors(['This product variant is out of stock.']);
        }

        if (!Auth::check()) {
            $cart = $this->getGuestCart($request);
            $oldKey = $this->guestCartKey($product, $oldColor, $oldSize);
            $newKey = $this->guestCartKey($product, $color, $size);

            $foundIndex = null;
            foreach ($cart as $index => $item) {
                $itemKey = $item['product_id'] . '|' . ($item['color'] ?? '') . '|' . ($item['size'] ?? '');
                if ($itemKey === $oldKey) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex === null) {
                return back()->withErrors(['Item not found in cart.']);
            }

            if ($oldKey !== $newKey) {
                $mergeIndex = null;
                foreach ($cart as $index => $item) {
                    if ($index === $foundIndex) continue;
                    $itemKey = $item['product_id'] . '|' . ($item['color'] ?? '') . '|' . ($item['size'] ?? '');
                    if ($itemKey === $newKey) {
                        $mergeIndex = $index;
                        break;
                    }
                }

                if ($mergeIndex !== null) {
                    $cart[$mergeIndex]['quantity'] = min($maxStock, $cart[$mergeIndex]['quantity'] + $data['quantity']);
                    array_splice($cart, $foundIndex, 1);
                } else {
                    $cart[$foundIndex]['color'] = $color ?: null;
                    $cart[$foundIndex]['size'] = $size ?: null;
                    $cart[$foundIndex]['quantity'] = min($maxStock, $data['quantity']);
                }
            } else {
                $cart[$foundIndex]['quantity'] = min($maxStock, $data['quantity']);
            }

            $this->setGuestCart($request, array_values($cart));
            return back();
        }

        $user = Auth::user();

        $oldItem = $user->cartItems()
            ->where('product_id', $product->id)
            ->where('color', $oldColor ?: null)
            ->where('size', $oldSize ?: null)
            ->first();

        if (!$oldItem) {
            return back()->withErrors(['Item not found in cart.']);
        }

        if ($oldColor !== $color || $oldSize !== $size) {
            $existingItem = $user->cartItems()
                ->where('product_id', $product->id)
                ->where('color', $color ?: null)
                ->where('size', $size ?: null)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => min($maxStock, $existingItem->quantity + $data['quantity']),
                ]);
                $oldItem->delete();
            } else {
                $oldItem->update([
                    'color' => $color ?: null,
                    'size' => $size ?: null,
                    'quantity' => min($maxStock, $data['quantity']),
                ]);
            }
        } else {
            $oldItem->update([
                'quantity' => min($maxStock, $data['quantity']),
            ]);
        }

        return back();
    }

    public function remove(Request $request, Product $product)
    {
        $color = $request->input('color');
        $size = $request->input('size');

        if (!Auth::check()) {
            $cart = $this->getGuestCart($request);
            $removeKey = $this->guestCartKey($product, $color, $size);

            $cart = array_values(array_filter($cart, function ($item) use ($removeKey, $product) {
                $itemKey = $item['product_id'] . '|' . ($item['color'] ?? '') . '|' . ($item['size'] ?? '');
                return $itemKey !== $removeKey;
            }));

            $this->setGuestCart($request, $cart);
            return back();
        }

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
            'selected' => ['required', 'boolean'],
        ]);

        if (!Auth::check()) {
            $cartKey = $request->input('cart_key');
            $cart = $this->getGuestCart($request);

            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['selected'] = (bool) $data['selected'];
                $this->setGuestCart($request, $cart);
            }

            return back();
        }

        $cartItemId = $request->input('cart_item_id');
        $item = CartItem::findOrFail($cartItemId);
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

        if (!Auth::check()) {
            $cart = $this->getGuestCart($request);

            $productIds = array_column($cart, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($cart as &$item) {
                $product = $products->get($item['product_id']);
                if ($product && $product->getVariantStock($item['color'] ?? null, $item['size'] ?? null) > 0) {
                    $item['selected'] = (bool) $data['selected'];
                }
            }
            unset($item);

            $this->setGuestCart($request, $cart);
            return back();
        }

        Auth::user()->cartItems()
            ->with('product')
            ->get()
            ->filter(fn($item) => $item->product && $item->product->getVariantStock($item->color, $item->size) > 0)
            ->each(fn($item) => $item->update(['selected' => $data['selected']]));

        return back();
    }
}
