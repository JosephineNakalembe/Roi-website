@foreach($items as $item)
    <div class="card wishlist-card" style="display:block;text-decoration:none;color:inherit;background:#fff;border:1px solid #e9ecef;border-radius:14px;overflow:hidden;">
        <a href="{{ route('shop.show', $item->product->slug) }}" style="text-decoration:none;color:inherit;">
            <img src="{{ optional($item->product->primaryImage)->path ? media_url($item->product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $item->product->name }}" style="width:100%;border-radius:12px;object-fit:cover;height:240px;aspect-ratio:1/1;">
            <h2 style="padding:8px 12px 0;">{{ $item->product->name }}</h2>
            <p class="text-muted" style="padding:0 12px;">{{ $item->product->category?->name ?? 'Uncategorized' }}</p>
            <p style="font-weight:700;padding:0 12px;">UGX{{ number_format($item->product->price, 0) }}</p>
        </a>
        <div style="display:flex;justify-content:space-between;gap:10px;margin:14px 12px 12px;">
            <a class="btn btn-secondary" href="{{ route('shop.show', $item->product->slug) }}">View</a>
            <form method="POST" action="{{ route('wishlist.toggle', $item->product) }}" style="margin:0;">
                @csrf
                <button class="btn" type="submit">Remove</button>
            </form>
        </div>
    </div>
@endforeach
