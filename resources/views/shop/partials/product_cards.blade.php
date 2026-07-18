@foreach($paginator as $product)
    <a href="{{ route('shop.show', $product->slug) }}" class="product-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
        <div style="position:relative;">
            <img src="{{ optional($product->primaryImage)->path ? media_url($product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;{{ $product->stock <= 0 ? 'opacity:0.4;filter:grayscale(50%);' : '' }}" loading="lazy">
            @if($product->stock <= 0)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,0.2);"></div>
            @endif
        </div>
        <div style="padding:8px 10px 10px;">
            <h2 style="font-size:1rem;font-weight:600;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $product->name }}</h2>
            <p style="font-weight:700;font-size:1.05rem;">UGX{{ number_format($product->price, 0) }}</p>
        </div>
    </a>
@endforeach