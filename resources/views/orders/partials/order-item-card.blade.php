@php
    $colorParts = explode(':', $item->color ?? '');
    $colorDisplayName = $colorParts[1] ?? $item->color ?? '';
    $imgUrl = $item->product && $item->product->primaryImage
        ? media_url($item->product->primaryImage->path)
        : 'https://via.placeholder.com/80x80?text=No+Image';
    $productUrl = $item->product ? route('shop.show', $item->product->slug) : null;
@endphp
<div style="display:flex;gap:12px;padding:12px;border:1px solid {{ $item->cancelled_at ? '#fecaca' : '#e5e7eb' }};border-radius:12px;background:{{ $item->cancelled_at ? '#fef2f2' : '#fff' }};">
    <div style="flex-shrink:0;width:80px;height:80px;border-radius:8px;overflow:hidden;background:#f3f4f6;">
        @if($productUrl)
            <a href="{{ $productUrl }}" target="_blank">
                <img src="{{ $imgUrl }}" alt="{{ $item->product_name }}" style="width:100%;height:100%;object-fit:cover;">
            </a>
        @else
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e5e7eb;color:#9ca3af;font-size:0.7rem;text-align:center;padding:2px;">
                No Image
            </div>
        @endif
    </div>
    <div style="flex:1;min-width:0;">
        <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;">
            <div>
                @if($productUrl)
                    <a href="{{ $productUrl }}" target="_blank" style="font-weight:600;text-decoration:none;color:inherit;">
                        {{ $item->product_name }} × {{ $item->quantity }}
                    </a>
                @else
                    <span style="font-weight:600;">{{ $item->product_name }} × {{ $item->quantity }}</span>
                    <p style="margin:2px 0 0;font-size:0.9rem;color:#dc2626;font-weight:600;">Item no longer sold</p>
                @endif
                @if($colorDisplayName || $item->size)
                    <p style="font-size:0.95rem;color:#6b7280;margin-top:2px;">
                        @if($colorDisplayName)<span>Color: {{ $colorDisplayName }}</span>@endif
                        @if($item->size)<span> | Size: {{ $item->size }}</span>@endif
                    </p>
                @endif
                @if($item->cancelled_at)
                    <p style="margin:2px 0 0;font-size:0.9rem;color:#dc2626;font-weight:600;">
                        Cancelled — {{ $item->cancellation_reason }}
                    </p>
                @endif
                @if($item->product && $item->product->non_returnable && !$item->cancelled_at)
                    <p style="margin:2px 0 0;font-size:0.9rem;color:#991b1b;font-weight:600;">
                        Non-returnable
                    </p>
                @endif
                @if($item->review)
                    <div style="margin-top:6px;padding:8px;background:#f3f4f6;border-radius:8px;">
                        <p style="margin:0 0 2px;font-weight:700;">Your review</p>
                        <p style="margin:0;font-size:1rem;">Rating: {{ $item->review->rating }}/5</p>
                        @if($item->review->comment)
                            <p style="margin:4px 0 0;font-size:1rem;color:#374151;">"{{ $item->review->comment }}"</p>
                        @endif
                    </div>
                @endif
            </div>
            <strong style="white-space:nowrap;flex-shrink:0;">UGX{{ number_format($item->total_price, 2) }}</strong>
        </div>
        @if($showReviews && !$item->review)
            <div style="margin-top:10px;display:grid;gap:8px;">
                <label style="font-weight:700;font-size:0.95rem;">Leave a review (optional)</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <select class="input" name="reviews[{{ $item->id }}][rating]" style="max-width:100px;">
                        <option value="">Rating</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    <textarea class="input" name="reviews[{{ $item->id }}][comment]" rows="2" placeholder="Share how the product felt..." style="flex:1;min-width:150px;"></textarea>
                </div>
            </div>
        @endif
    </div>
</div>
