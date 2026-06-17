@extends('layouts.app')

@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
            <h1>Products</h1>
            <a class="btn" href="{{ route('admin.products.create') }}">Add Product</a>
        </div>
        <div style="display:grid;gap:14px;margin-top:16px;">
            @foreach($products as $product)
                <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px;border:1px solid #e5e7eb;border-radius:14px;">
                    <div>
                        <strong>{{ $product->name }}</strong>
                        <div class="text-muted">{{ $product->category?->name ?? 'Uncategorized' }} • UGX{{ number_format($product->price, 2) }} • Stock {{ $product->stock }}</div>
                    </div>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button class="btn" style="background:#ef4444;">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top:20px;">{{ $products->links() }}</div>
    </div>
@endsection
