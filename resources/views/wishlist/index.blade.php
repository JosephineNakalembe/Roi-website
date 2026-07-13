@extends('layouts.app')

@section('content')
    <!-- Sticky Header -->
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
            <div class="grid-3">
                @foreach($items as $item)
                    <div class="card">
                        <img src="{{ optional($item->product->primaryImage)->path ? asset('storage/' . $item->product->primaryImage->path) : 'https://via.placeholder.com/400x400' }}" alt="{{ $item->product->name }}" style="width:100%;border-radius:12px;object-fit:cover;height:240px;aspect-ratio:1/1;">
                        <h2>{{ $item->product->name }}</h2>
                        <p class="text-muted">{{ $item->product->category?->name ?? 'Uncategorized' }}</p>
                        <p style="font-weight:700;">UGX{{ number_format($item->product->price, 0) }}</p>
                        <div style="display:flex;justify-content:space-between;gap:10px;margin-top:14px;">
                            <a class="btn btn-secondary" href="{{ route('shop.show', $item->product->slug) }}">View</a>
                            <form method="POST" action="{{ route('wishlist.toggle', $item->product) }}" style="margin:0;">
                                @csrf
                                <button class="btn" type="submit">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
