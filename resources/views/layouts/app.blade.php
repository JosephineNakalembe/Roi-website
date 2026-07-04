<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROI Store</title>
    @if (class_exists(\Illuminate\Support\Facades\Vite::class) && file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/css/responsive.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;line-height:1.6;background:#f8f9fa;color:#1a1a2e;}
        .container{max-width:1200px;margin:0 auto;padding:16px 24px;}
        .btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:10px;text-decoration:none;color:#fff;background:#1a1a2e;font-size:0.9rem;font-weight:500;border:none;cursor:pointer;transition:all 0.2s;}
        .btn:hover{background:#2d2d44;transform:translateY(-1px);box-shadow:0 4px 12px rgba(26,26,46,0.15);}
        .btn-secondary{background:#6c757d;}
        .btn-secondary:hover{background:#5a6268;}
        .card{background:#fff;border:1px solid #e9ecef;border-radius:16px;padding:24px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);}
        .input{width:100%;padding:12px 14px;border:1.5px solid #dee2e6;border-radius:10px;font-size:0.9rem;font-family:inherit;transition:border-color 0.2s;}
        .input:focus{outline:none;border-color:#1a1a2e;box-shadow:0 0 0 3px rgba(26,26,46,0.08);}
        .text-muted{color:#6c757d;}
        .grid-3{display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));}
        .nav-link{display:inline-flex;align-items:center;justify-content:center;padding:10px 16px;border-radius:10px;background:#f1f3f5;color:#1a1a2e;text-decoration:none;font-size:0.9rem;font-weight:500;transition:all 0.2s;}
        .nav-link:hover{background:#e9ecef;}
        .product-image{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:14px;}
        .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;font-size:0.8rem;font-weight:500;}
        .badge-green{background:#e8f5e9;color:#2e7d32;}
        .badge-blue{background:#e3f2fd;color:#1565c0;}
        .badge-amber{background:#fff8e1;color:#f57f17;}
        .badge-gray{background:#f1f3f5;color:#495057;}
        .badge-red{background:#fce4ec;color:#c62828;}
        table{width:100%;border-collapse:collapse;font-size:0.9rem;}
        th{padding:12px 16px;border-bottom:2px solid #e9ecef;text-align:left;font-weight:600;color:#495057;background:#f8f9fa;}
        td{padding:12px 16px;border-bottom:1px solid #f1f3f5;color:#1a1a2e;}
        tr:last-child td{border-bottom:none;}
        h1{font-size:1.5rem;font-weight:700;margin-bottom:4px;color:#1a1a2e;}
        h2{font-size:1.2rem;font-weight:600;margin-bottom:12px;color:#1a1a2e;}
        h3{font-size:1.05rem;font-weight:600;margin-bottom:8px;color:#1a1a2e;}
        .stat-card{padding:20px;border-radius:14px;border:1px solid #e9ecef;background:#fff;transition:all 0.2s;}
        .stat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,0.06);}
        .stat-value{font-size:1.5rem;font-weight:700;color:#1a1a2e;}
        .stat-label{font-size:0.85rem;color:#6c757d;margin-top:2px;}
        .nav-badge{position:relative;display:inline-flex;align-items:center;}
        .nav-badge sup{position:absolute;top:-8px;right:-10px;min-width:20px;height:20px;border-radius:10px;font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 5px;box-shadow:0 2px 4px rgba(0,0,0,0.15);}
        .nav-badge sup.badge-red{background:#dc2626;color:#fff;}
        .nav-badge sup.badge-orange{background:#f97316;color:#fff;}
        .unread-help-badge{position:fixed;bottom:20px;right:20px;z-index:999;animation:pulse 2s infinite;}
        .cart-float{position:fixed;bottom:80px;right:20px;z-index:999;width:60px;height:60px;border-radius:50%;background:#1a1a2e;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 16px rgba(26,26,46,0.5);transition:all 0.2s;font-size:1.6rem;}
        .cart-float:hover{transform:scale(1.1);box-shadow:0 6px 24px rgba(26,26,46,0.6);}
        .cart-float sup{position:absolute;top:-6px;right:-6px;min-width:24px;height:24px;border-radius:12px;background:#dc2626;color:#fff;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 6px;border:2px solid #1a1a2e;}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(220,38,38,0.4);}70%{box-shadow:0 0 0 15px rgba(220,38,38,0);}100%{box-shadow:0 0 0 0 rgba(220,38,38,0);}}
    </style>
</head>
<body>
    <header class="container" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
            <a href="{{ url('/') }}" style="font-size:1.4rem;font-weight:700;color:#111;text-decoration:none;">ROI Store</a>
            <nav style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                @auth
                    @unless(auth()->user()->isAdmin())
                        @php
                            $cartCount = auth()->user()->cartItems()->count();
                            $activeOrdersCount = auth()->user()->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();
                            $unreadMessagesCount = auth()->user()->customerMessages()->where('seen_by_user', false)
                                ->whereIn('status', ['open', 'answered'])
                                ->count();
                        @endphp
                        <a class="nav-link" href="{{ route('shop.index') }}">Shop</a>
                        <a class="nav-link" href="{{ route('cart.index') }}">
                            <span class="nav-badge">
                                Cart
                                @if($cartCount > 0)
                                    <sup class="badge-red">{{ $cartCount }}</sup>
                                @endif
                            </span>
                        </a>
                        <a class="nav-link" href="{{ route('orders.index') }}">
                            <span class="nav-badge">
                                Orders
                                @if($activeOrdersCount > 0)
                                    <sup class="badge-orange">{{ $activeOrdersCount }}</sup>
                                @endif
                            </span>
                        </a>
                        <a class="nav-link" href="{{ route('wishlist.index') }}">Wishlist</a>
                        <a class="nav-link" href="{{ route('customer-service.index') }}">
                            <span class="nav-badge">
                                Help
                                @if($unreadMessagesCount > 0)
                                    <sup class="badge-red">{{ $unreadMessagesCount }}</sup>
                                @endif
                            </span>
                        </a>
                        <a class="nav-link" href="{{ route('dashboard') }}">Account</a>
                        @if($unreadMessagesCount > 0)
                            <a href="{{ route('customer-service.index') }}" class="unread-help-badge btn" style="background:#dc2626;padding:14px 20px;border-radius:50px;font-weight:600;text-decoration:none;">
                                🔔 {{ $unreadMessagesCount }} new message{{ $unreadMessagesCount > 1 ? 's' : '' }}
                            </a>
                        @endif
                    @else
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endunless
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:#111;cursor:pointer;">Logout</button>
                    </form>
                @else
                    <a class="nav-link" href="{{ route('shop.index') }}">Shop</a>
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
        <div style="margin-top:6px;">
            <p style="margin:0;font-family:'Playfair Display', Georgia, 'Times New Roman', serif;font-size:1.15rem;font-weight:500;color:#6c757d;letter-spacing:0.3px;">Affordable shopping made easy</p>
        </div>
    </header>

    <main class="container">
        @include('partials.alerts')
        <div style="margin-bottom:18px;">
            <button class="btn btn-secondary" type="button" onclick="history.back()">Back</button>
        </div>
        @yield('content')
    </main>

    @auth
        @unless(auth()->user()->isAdmin())
            @php
                $floatCartCount = auth()->user()->cartItems()->count();
            @endphp
            <a href="{{ route('cart.index') }}" class="cart-float" title="View Cart">
                🛒
                @if($floatCartCount > 0)
                    <sup>{{ $floatCartCount }}</sup>
                @endif
            </a>
        @endunless
    @endauth
</body>
</html>