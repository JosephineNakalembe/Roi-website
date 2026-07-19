@extends('layouts.app')

@section('content')
    <div class="sticky-header">
        <div class="header-content">
            @include('partials.back-button')
            <h1 class="mb-0">Wishlist</h1>
        </div>
    </div>
    <div class="card">
        @if($items->isEmpty())
            <p>Your wishlist is empty. Browse the shop to save products for later.</p>
        @else
            <div id="wishlistGrid" class="grid-3">
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
            </div>

            <div id="loadingState" style="display:none;text-align:center;margin-top:20px;padding:20px;">
                <div style="display:inline-block;width:32px;height:32px;border:3px solid #e5e7eb;border-top-color:#1a1a2e;border-radius:50%;animation:spin 0.8s linear infinite;"></div>
                <p style="margin-top:8px;color:#6b7280;">Loading more items…</p>
            </div>
            <div id="endState" style="display:none;text-align:center;margin-top:12px;color:#6b7280;padding:12px;">All items loaded.</div>

            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>

            <script>
            function addAjaxParam(url) {
                if (!url) return null;
                const sep = url.includes('?') ? '&' : '?';
                return url + sep + '_ajax=1';
            }

            let nextPageUrl = @json($paginator->nextPageUrl());
            let isLoading = false;
            let hasMore = {{ $paginator->hasMorePages() ? 'true' : 'false' }};

            async function loadMoreItems() {
                if (!nextPageUrl || isLoading || !hasMore) return;
                isLoading = true;
                document.getElementById('loadingState').style.display = 'block';

                try {
                    const url = addAjaxParam(nextPageUrl);
                    const response = await fetch(url);

                    if (!response.ok) { hasMore = false; document.getElementById('loadingState').style.display = 'none'; document.getElementById('endState').style.display = 'block'; isLoading = false; return; }

                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) { hasMore = false; document.getElementById('loadingState').style.display = 'none'; document.getElementById('endState').style.display = 'block'; isLoading = false; return; }

                    const data = await response.json();

                    if (data.html) {
                        const temp = document.createElement('div');
                        temp.innerHTML = data.html;
                        const cards = temp.querySelectorAll('.wishlist-card');
                        cards.forEach(card => document.getElementById('wishlistGrid').appendChild(card));
                    }

                    nextPageUrl = data.next_page_url || null;
                    if (!nextPageUrl) { hasMore = false; document.getElementById('endState').style.display = 'block'; }
                } catch (e) {
                    console.error('Infinite scroll error:', e);
                    hasMore = false;
                    document.getElementById('loadingState').style.display = 'none';
                    document.getElementById('endState').style.display = 'block';
                }

                document.getElementById('loadingState').style.display = 'none';
                isLoading = false;
            }

            let scrollTimeout;
            window.addEventListener('scroll', () => {
                if (scrollTimeout) clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(() => {
                    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 800) {
                        loadMoreItems();
                    }
                }, 100);
            });
            </script>
        @endif
    </div>
@endsection
