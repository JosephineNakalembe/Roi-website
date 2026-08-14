@php $inCartQty = $cartQuantities[$product->id] ?? 0; @endphp
<div class="product-card" style="position:relative;display:flex;flex-direction:column;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;cursor:pointer;transition:box-shadow 0.2s, transform 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='';this.style.transform='';">
    <a href="{{ route('shop.show', $product->slug) }}" style="text-decoration:none;color:inherit;display:block;flex:1;">
        <div style="position:relative;">
            <img src="{{ optional($product->primaryImage)->path ? media_url($product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $product->name }}" style="width:100%;aspect-ratio:1/1;object-fit:cover;{{ $product->stock <= 0 ? 'opacity:0.4;filter:grayscale(50%);' : '' }}" loading="lazy">
            @if($product->stock <= 0)
                <div style="position:absolute;inset:0;background:rgba(0,0,0,0.2);"></div>
            @endif
        </div>
        <div style="padding:8px 10px 10px;padding-right:38px;">
            <h2 style="font-size:1rem;font-weight:600;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $product->name }}</h2>
            <p style="font-weight:700;font-size:1.05rem;">UGX{{ number_format($product->price, 0) }}</p>
        </div>
    </a>
    <span style="position:absolute;bottom:10px;right:10px;display:inline-flex;align-items:flex-start;gap:3px;" title="{{ $inCartQty > 0 ? $inCartQty . ' in cart' : 'Not in cart' }}">
        <svg style="width:22px;height:22px;stroke:#1a1a2e;fill:none;stroke-width:1.5;" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        @if($inCartQty > 0)
            <sup style="font-size:0.75rem;color:#1a1a2e;font-weight:600;line-height:1;margin-top:-1px;">{{ $inCartQty }}</sup>
        @endif
    </span>
</div>
