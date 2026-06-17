<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Auth::user()->wishlistItems()->with('product.primaryImage')->get();
        return view('wishlist.index', compact('items'));
    }

    public function toggle(Product $product)
    {
        $user = Auth::user();
        $wishlist = $user->wishlistItems()->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            return back()->with('success', 'Item removed from your wishlist.');
        }

        $user->wishlistItems()->create(['product_id' => $product->id]);
        return back()->with('success', 'Item added to your wishlist.');
    }
}
