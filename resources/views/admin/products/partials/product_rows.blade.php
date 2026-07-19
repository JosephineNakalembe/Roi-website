@foreach($products as $product)
    <div class="product-item" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border:1px solid #e5e7eb;border-radius:14px;">
        <div style="width:64px;height:64px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f3f4f6;">
            @if($product->primaryImage)
                <img src="{{ media_url($product->primaryImage->path) }}" alt="" style="width:100%;height:100%;object-fit:cover;">
            @else
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:0.85rem;">No<br>Image</div>
            @endif
        </div>
        <div style="flex:1;min-width:0;">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <strong style="font-size:1.1rem;">{{ $product->name }}</strong>
                <span style="font-size:0.9rem;color:#6b7280;background:#f3f4f6;padding:2px 8px;border-radius:4px;">{{ $product->product_id }}</span>
            </div>
            <div class="text-muted" style="font-size:0.95rem;margin-top:2px;">
                @if($product->categories->isNotEmpty())
                    {{ $product->categories->pluck('name')->implode(', ') }}
                @elseif($product->category)
                    {{ $product->category->name }}
                @else
                    Uncategorized
                @endif
                • UGX{{ number_format($product->price, 2) }} • Stock {{ $product->stock }}
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;flex-shrink:0;">
            <button class="btn btn-secondary" onclick="openStockModal('{{ $product->id }}', '{{ addslashes($product->name) }}', '{{ $product->stock }}')" style="padding:6px 14px;font-size:0.95rem;background:#059669;color:#fff;border:none;">+ Stock</button>
            <a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}" style="padding:6px 14px;font-size:0.95rem;">Edit</a>
            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="margin:0;" onsubmit="return confirm('Delete this product?');">
                @csrf
                @method('DELETE')
                <button class="btn" style="background:#ef4444;color:#fff;padding:6px 14px;font-size:0.95rem;">Delete</button>
            </form>
        </div>
    </div>
@endforeach
