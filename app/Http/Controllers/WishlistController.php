<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = Auth::user()->wishlistItems()->with('product.primaryImage', 'product.category');
        $perPage = 12;
        $page = (int) $request->get('page', 1);
        $total = $items->count();
        $pagedItems = $items->skip(($page - 1) * $perPage)->take($perPage)->get();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $isAjax = $request->boolean('_ajax');

        if ($isAjax) {
            return response()->json([
                'html' => view('wishlist.partials.wishlist_cards', ['items' => $pagedItems])->render(),
                'next_page_url' => $paginator->nextPageUrl(),
            ]);
        }

        return view('wishlist.index', ['items' => $pagedItems, 'paginator' => $paginator]);
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
