<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('categories', 'category', 'primaryImage');

        // Search by name or product_id
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('product_id', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->input('category')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'size_guide' => ['nullable', 'string'],
            'size_guide_type' => ['nullable', 'string', 'in:clothing,shoes'],
            'colors' => ['nullable', 'string'],
            'sizes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'video' => ['nullable', 'mimes:mp4,mov,avi,wmv', 'max:51200'],
        ]);

        // Auto-generate product_id
        $data['product_id'] = $this->generateNextProductId();

        $data = $this->applyColorVariants($request, $data);

        $data['slug'] = Str::slug($data['name']);
        $suffix = 1;
        $originalSlug = $data['slug'];
        while (Product::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $suffix++;
        }

        $product = Product::create(array_merge($data, ['is_active' => $request->boolean('is_active')]));

        // Attach selected categories (many-to-many)
        $categoryIds = $request->input('categories', []);
        if ($request->input('category_id')) {
            $categoryIds[] = $request->input('category_id');
        }
        if (!empty($categoryIds)) {
            $product->categories()->attach(array_unique($categoryIds));
        }

        $this->storeProductMedia($request, $product, 0);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load('images', 'categories');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'size_guide' => ['nullable', 'string'],
            'colors' => ['nullable', 'string'],
            'sizes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'images.*' => ['nullable', 'image', 'max:5120'],
            'video' => ['nullable', 'mimes:mp4,mov,avi,wmv', 'max:51200'],
        ]);

        // Keep existing product_id (don't change on update)
        $data['product_id'] = $product->product_id;

        $data = $this->applyColorVariants($request, $data);

        $data['slug'] = Str::slug($data['name']);
        $suffix = 1;
        $originalSlug = $data['slug'];
        while (Product::where('slug', $data['slug'])->where('id', '!=', $product->id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $suffix++;
        }

        $product->update(array_merge($data, ['is_active' => $request->boolean('is_active')]));

        // Sync categories (many-to-many)
        $categoryIds = $request->input('categories', []);
        if ($request->input('category_id')) {
            $categoryIds[] = $request->input('category_id');
        }
        $product->categories()->sync(array_unique($categoryIds));

        $existingImageCount = $product->images()->where('media_type', 'image')->count();
        $this->storeProductMedia($request, $product, $existingImageCount);

        return back()->with('success', 'Product updated.');
    }

    public function addStock(Request $request, Product $product)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updates = [];
        $updates['stock'] = $product->stock + $data['quantity'];

        if ($request->filled('cost_price')) {
            $updates['cost_price'] = $data['cost_price'];
        }

        if ($request->filled('price')) {
            $updates['price'] = $data['price'];
        }

        $product->update($updates);

        $msg = "Added {$data['quantity']} units to stock.";
        if ($request->filled('price')) {
            $msg .= " New price: UGX " . number_format($data['price'], 2);
        }
        $msg .= " New stock: {$product->fresh()->stock}";

        return back()->with('success', $msg);
    }

    public function nextId()
    {
        return response()->json([
            'product_id' => $this->generateNextProductId(),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /**
     * Parse the dynamic color/size/quantity/price inputs into structured
     * product attributes (color_stock, stock, sizes, colors, color_prices).
     */
    private function applyColorVariants(Request $request, array $data): array
    {
        // Convert comma-separated strings to arrays
        $data['colors'] = $data['colors'] ? array_filter(array_map('trim', explode(',', $data['colors']))) : null;

        // Process color-size-quantity combinations and auto-collect sizes
        $colorStock = [];
        $totalStock = 0;
        $collectedSizes = [];
        $collectedColors = [];
        $colorPrices = [];
        for ($i = 0; $i < 100; $i++) {
            $color = $request->input("color_$i");
            $size = $request->input("size_$i");
            $quantity = $request->input("quantity_$i");
            $colorPrice = $request->input("price_$i");
            if ($color && $quantity) {
                $color = trim($color);
                $key = $size ? "$color ($size)" : $color;
                $colorStock[$key] = (int)$quantity;
                $totalStock += (int)$quantity;
                // Collect unique sizes
                if ($size && !in_array($size, $collectedSizes)) {
                    $collectedSizes[] = $size;
                }
                // Collect unique colors
                if (!in_array($color, $collectedColors)) {
                    $collectedColors[] = $color;
                }
                // Collect per-color price (first non-empty wins for a given color)
                if ($colorPrice !== null && $colorPrice !== '' && !isset($colorPrices[$color])) {
                    $colorPrices[$color] = (float)$colorPrice;
                }
            }
        }
        $data['color_stock'] = !empty($colorStock) ? $colorStock : null;
        $data['stock'] = $totalStock > 0 ? $totalStock : $data['stock'];
        $data['sizes'] = !empty($collectedSizes) ? $collectedSizes : null;
        $data['colors'] = !empty($collectedColors) ? $collectedColors : $data['colors'];
        $data['color_prices'] = !empty($colorPrices) ? $colorPrices : null;

        return $data;
    }

    /**
     * Persist uploaded general images, per-color images and an optional video.
     * $startOrder seeds the running order/primary counter so existing media is
     * preserved when updating an existing product.
     */
    private function storeProductMedia(Request $request, Product $product, int $startOrder): void
    {
        $orderCounter = $startOrder;

        // General / no specific color images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'media_type' => 'image',
                    'is_primary' => $orderCounter === 0,
                    'order' => $orderCounter,
                ]);
                $orderCounter++;
            }
        }

        // Per-color images
        for ($i = 0; $i < 100; $i++) {
            $color = $request->input("color_$i");
            if (!$color) {
                continue;
            }
            $color = trim($color);
            if ($request->hasFile("color_images_$i")) {
                foreach ($request->file("color_images_$i") as $image) {
                    $path = $image->store('products', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'media_type' => 'image',
                        'color' => $color,
                        'is_primary' => $orderCounter === 0,
                        'order' => $orderCounter,
                    ]);
                    $orderCounter++;
                }
            }
        }

        // Video (appears last in slideshow)
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $path = $video->store('products/videos', 'public');
            $product->images()->create([
                'path' => $path,
                'media_type' => 'video',
                'is_primary' => false,
                'order' => 999,
            ]);
        }
    }

    private function generateNextProductId(): string
    {
        $lastProduct = Product::where('product_id', 'like', 'ER%')
            ->orderByRaw("CAST(SUBSTRING(product_id, 3) AS UNSIGNED) DESC")
            ->first();

        if ($lastProduct) {
            $lastNumber = (int) substr($lastProduct->product_id, 2);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 36;
        }

        return 'ER' . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
    }
}