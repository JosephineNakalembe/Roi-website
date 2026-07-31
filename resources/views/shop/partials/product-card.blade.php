<a href="{{ route('shop.show', $product->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:10px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
    <img src="{{ optional($product->primaryImage)->path ? media_url($product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;" loading="lazy">
    <div style="padding:10px 12px 12px;">
        <h2 style="font-size:0.75rem;font-weight:600;margin-bottom:2px;">{{ $product->name }}</h2>
        <p style="font-weight:900;font-size:0.95rem;">UGX{{ number_format($product->price, 0) }}</p>
        @if($product->stock <= 0)
            <span class="badge badge-red">Out of Stock</span>
        @elseif($product->stock <= 2)
            <span style="font-size:0.7rem;color:#c62828;">Only {{ $product->stock }} left</span>
        @endif
    </div>
</a>
