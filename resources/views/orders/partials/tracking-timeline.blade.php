@php
    $icons = [
        'placed' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
        'pending' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'processing' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'shipped' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1h-1m5-1h3a1 1 0 001-1V9a1 1 0 00-.5-.865L16 6"/></svg>',
        'delivered' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'cancelled' => '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    ];
    $updates = $order->updates;
    $showActor = $showActor ?? false;
@endphp
<style>
    .tt{position:relative;}
    .tt-item{position:relative;display:flex;gap:14px;padding-bottom:18px;}
    .tt-item:last-child{padding-bottom:0;}
    .tt-line{position:absolute;left:16px;top:34px;bottom:-4px;width:2px;background:#e8e4df;}
    .tt-item:last-child .tt-line{display:none;}
    .tt-node{position:relative;flex-shrink:0;width:34px;height:34px;border-radius:50%;background:#1a1a2e;color:#fff;display:flex;align-items:center;justify-content:center;z-index:1;border:2px solid #fff;box-shadow:0 0 0 2px #e8e4df;}
    .tt-node svg{width:17px;height:17px;}
    .tt-node-muted{background:#f0ece6;color:#6c757d;}
    .tt-card{flex:1;min-width:0;padding:10px 12px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;}
    .tt-card-current{background:#f5f3ef;border-color:#1a1a2e;}
    .tt-head{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;}
    .tt-head strong{font-size:0.95rem;color:#1a1a2e;}
    .tt-head time{font-size:0.85rem;color:#9ca3af;}
    .tt-note{margin:6px 0 0;font-size:0.95rem;color:#6c757d;}
    .tt-actor{margin:4px 0 0;font-size:0.8rem;color:#9ca3af;}
</style>
<div class="tt">
    @if($updates->isNotEmpty())
        @foreach($updates as $i => $update)
            @php
                $icon = $icons[$update->status] ?? $icons['pending'];
                $label = $update->status === 'shipped' ? 'Shipped' : ($update->status === 'delivered' ? 'Delivered' : ($update->status === 'cancelled' ? 'Cancelled' : ucfirst($update->status)));
            @endphp
            <div class="tt-item">
                <div class="tt-line"></div>
                <div class="tt-node">{!! $icon !!}</div>
                <div class="tt-card {{ $i === 0 ? 'tt-card-current' : '' }}">
                    <div class="tt-head">
                        <strong>{{ $label }}</strong>
                        <time>{{ $update->created_at->format('M d, H:i') }}</time>
                    </div>
                    @if($update->note)
                        <p class="tt-note">{{ $update->note }}</p>
                    @endif
                    @if($showActor)
                        <p class="tt-actor">by {{ $update->status === 'delivered' && str_contains($update->note ?? '', 'Buyer confirmed') ? 'Buyer' : 'Admin' }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
    <div class="tt-item">
        <div class="tt-line"></div>
        <div class="tt-node tt-node-muted">{!! $icons['placed'] !!}</div>
        <div class="tt-card">
            <div class="tt-head">
                <strong>Order Placed</strong>
                <time>{{ $order->created_at->format('M d, H:i') }}</time>
            </div>
        </div>
    </div>
</div>
