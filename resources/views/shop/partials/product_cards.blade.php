@foreach($products as $product)
    <a href="{{ route('shop.show', $product->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
        <img src="{{ optional($product->primaryImage)->path ? asset('storage/' . $product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;" loading="lazy">
        <div style="padding:12px 14px 14px;">
            <h2 style="font-size:0.95rem;font-weight:600;margin-bottom:2px;">{{ $product->name }}</h2>
            <p style="font-size:0.8rem;color:#6c757d;margin-bottom:4px;">{{ $product->category?->name ?? 'Uncategorized' }}</p>
            <p style="font-weight:700;font-size:1rem;">UGX{{ number_format($product->price, 0) }}</p>
            @if($product->stock <= 0)
                <span class="badge badge-red">Out of Stock</span>
            @elseif($product->stock <= 2)
                <span style="font-size:0.75rem;color:#c62828;">Only {{ $product->stock }} left</span>
            @endif
        </div>
    </a>
@endforeach