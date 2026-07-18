<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ROI Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if (class_exists(\Illuminate\Support\Facades\Vite::class) && file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/css/responsive.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/themes/classic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr/dist/pickr.min.js"></script>

    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;line-height:1.4;background:#f8f9fa;color:#1a1a2e;font-size:0.975rem;}
        .container{max-width:1200px;margin:0 auto;padding:8px 12px;}
        .btn{display:inline-flex;align-items:center;gap:3px;padding:6px 12px;border-radius:6px;text-decoration:none;color:#fff;background:#1a1a2e;font-size:0.9rem;font-weight:500;border:none;cursor:pointer;transition:all 0.2s;}
        .btn:hover{background:#2d2d44;transform:translateY(-1px);box-shadow:0 4px 12px rgba(26,26,46,0.15);}
        .btn-secondary{background:#6c757d;}
        .btn-secondary:hover{background:#5a6268;}
        .card{background:#fff;border:1px solid #e9ecef;border-radius:10px;padding:12px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);}
        .input{width:100%;padding:8px 10px;border:1.5px solid #dee2e6;border-radius:6px;font-size:0.9rem;font-family:inherit;transition:border-color 0.2s;}
        .input:focus{outline:none;border-color:#1a1a2e;box-shadow:0 0 0 3px rgba(26,26,46,0.08);}
        .text-muted{color:#6c757d;}
        .grid-3{display:grid;gap:12px;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));}
        .nav-link{display:inline-flex;align-items:center;justify-content:center;padding:6px 14px;border-radius:6px;background:#f1f3f5;color:#1a1a2e;text-decoration:none;font-size:0.9rem;font-weight:500;transition:all 0.2s;min-width:60px;}
        .nav-link:hover{background:#e9ecef;}
        .product-image{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:10px;}
        .badge{display:inline-flex;align-items:center;padding:2px 6px;border-radius:999px;font-size:0.8rem;font-weight:500;}
        .badge-green{background:#e8f5e9;color:#2e7d32;}
        .badge-blue{background:#e3f2fd;color:#1565c0;}
        .badge-amber{background:#fff8e1;color:#f57f17;}
        .badge-gray{background:#f1f3f5;color:#495057;}
        .badge-red{background:#fce4ec;color:#c62828;}
        table{width:100%;border-collapse:collapse;font-size:0.9rem;}
        th{padding:8px 12px;border-bottom:2px solid #e9ecef;text-align:left;font-weight:600;color:#495057;background:#f8f9fa;}
        td{padding:8px 12px;border-bottom:1px solid #f1f3f5;color:#1a1a2e;}
        tr:last-child td{border-bottom:none;}
        h1{font-size:1.35rem;font-weight:700;margin-bottom:6px;color:#1a1a2e;}
        h2{font-size:1.15rem;font-weight:600;margin-bottom:8px;color:#1a1a2e;}
        h3{font-size:1.05rem;font-weight:600;margin-bottom:4px;color:#1a1a2e;}
        .stat-card{padding:12px;border-radius:10px;border:1px solid #e9ecef;background:#fff;transition:all 0.2s;}
        .stat-card:hover{box-shadow:0 4px 12px rgba(0,0,0,0.06);}
        .stat-value{font-size:1.35rem;font-weight:700;color:#1a1a2e;}
        .stat-label{font-size:0.85rem;color:#6c757d;margin-top:2px;}
        .nav-badge{position:relative;display:inline-flex;align-items:center;}
        .nav-badge sup{position:absolute;top:-5px;right:-6px;min-width:16px;height:16px;border-radius:8px;font-size:0.7rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;box-shadow:0 2px 4px rgba(0,0,0,0.15);}
        .nav-badge sup.badge-red{background:#dc2626;color:#fff;}
        .nav-badge sup.badge-orange{background:#f97316;color:#fff;}
        .unread-help-badge{position:fixed;bottom:20px;right:20px;z-index:999;animation:pulse 2s infinite;}
        #orderSummarySlider::-webkit-scrollbar {height: 5px;}
        #orderSummarySlider::-webkit-scrollbar-track {background: #f1f1f1;border-radius: 6px;}
        #orderSummarySlider::-webkit-scrollbar-thumb {background: #888;border-radius: 6px;}
        #orderSummarySlider::-webkit-scrollbar-thumb:hover{background: #555;}
        .cart-float{position:fixed;bottom:50px;right:12px;z-index:999;width:45px;height:45px;border-radius:50%;background:#1a1a2e;color:#fff;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 16px rgba(26,26,46,0.5);transition:all 0.2s;font-size:1.35rem;}
        .cart-float:hover{transform:scale(1.1);box-shadow:0 6px 24px rgba(26,26,46,0.6);}
        .cart-float sup{position:absolute;top:-3px;right:-3px;min-width:18px;height:18px;border-radius:9px;background:#dc2626;color:#fff;font-size:0.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px;border:2px solid #1a1a2e;}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(220,38,38,0.4);}70%{box-shadow:0 0 0 15px rgba(220,38,38,0);}100%{box-shadow:0 0 0 0 rgba(220,38,38,0);}}
        .modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:1000;}
        .modal-card{background:#fff;border-radius:12px;padding:24px;max-width:400px;width:90%;box-shadow:0 10px 40px rgba(0,0,0,0.2);}
        .modal-title{font-size:1.2rem;font-weight:600;color:#1a1a2e;margin-bottom:8px;}
        .modal-text{font-size:1rem;color:#6c757d;margin-bottom:20px;line-height:1.5;}
        .modal-buttons{display:flex;gap:12px;justify-content:flex-end;}
        .modal-btn{padding:8px 20px;border-radius:6px;font-size:0.95rem;font-weight:500;cursor:pointer;border:none;transition:all 0.2s;}
        .modal-btn-cancel{background:#f1f3f5;color:#1a1a2e;}
        .modal-btn-cancel:hover{background:#e9ecef;}
        .modal-btn-confirm{background:#dc2626;color:#fff;}
        .modal-btn-confirm:hover{background:#b91c1c;}
        .hidden{display:none;}
        .back-button{display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;background:none;border:none;cursor:pointer;padding:2px 6px;color:#1a1a2e;font-size:1.35rem;font-weight:600;line-height:1;min-width:28px;min-height:28px;border-radius:6px;transition:background-color 0.2s;}
        .back-button:hover{background:rgba(26,26,46,0.08);}
        .back-button:active{background:rgba(26,26,46,0.12);}
        .header-title-row{display:flex;align-items:center;gap:8px;min-width:0;flex:1;}
        .header-title-row h1{margin-bottom:0;}
        .header-content-between{justify-content:space-between;flex-wrap:wrap;}
        @media (max-width:768px){.back-button{font-size:1.2rem;min-width:26px;min-height:26px;padding:2px 4px;}.header-title-row{gap:6px;}}
    </style>
</head>
<body>
    <header class="mb-2 sticky top-0 z-50 px-3 py-2">
        @guest
            <div class="flex justify-between items-center">
                <div class="hidden md:flex flex-1 justify-start">
                    <a href="{{ url('/') }}" class="text-lg font-bold text-gray-900 no-underline">ROI Store</a>
                </div>
                <!-- Mobile Icon Navigation for Guests -->
                <div class="md:hidden flex items-center justify-between w-full">
                    <span class="text-lg font-bold text-gray-900">ROI Store</span>
                    <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900" style="text-decoration:none;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </a>
                    <a href="{{ route('cart.index') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900" style="text-decoration:none;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900" style="text-decoration:none;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                    </div>
                </div>
                <nav class="hidden md:flex gap-3 flex-wrap items-center">
                    <a class="nav-link" href="{{ route('cart.index') }}">Cart</a>
                    <a class="btn" href="{{ route('login') }}">Login</a>
                </nav>
            </div>
        @else
            <div class="flex justify-between items-center flex-nowrap">
                <div class="hidden md:flex flex-1 justify-start">
                    <a href="{{ url('/') }}" class="text-lg font-bold text-gray-900 no-underline">ROI Store</a>
                </div>
                <!-- Mobile Icon Navigation -->
                <div class="md:hidden flex items-center justify-between w-full">
                    <span class="text-lg font-bold text-gray-900">ROI Store</span>
                    <div class="flex items-center gap-4">
                    @unless(auth()->user()->isAdmin())
                        @php
                            $cartCount = auth()->user()->cartItems()->count();
                        @endphp
                        <a href="{{ route('shop.index') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900" style="text-decoration:none;">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </a>
                        <a href="{{ route('cart.index') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900 relative" style="text-decoration:none;">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            @if($cartCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <div class="relative">
                            <button onclick="toggleProfileDropdown(event)" class="flex flex-col items-center text-gray-700 hover:text-gray-900 bg-transparent border-none cursor-pointer p-0 z-10">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </button>
                            <div id="profileDropdown" class="hidden absolute top-full right-0 mt-2 bg-white shadow-lg rounded-lg border border-gray-200 py-2 min-w-48 z-50">
                                @php
                                    $activeOrdersCount = auth()->user()->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count();
                                    $unreadMessagesCount = auth()->user()->customerMessages()->where('seen_by_user', false)
                                        ->whereIn('status', ['open', 'answered'])
                                        ->count();
                                @endphp
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" style="text-decoration:none;">
                                    Orders
                                    @if($activeOrdersCount > 0)
                                        <span class="ml-2 bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $activeOrdersCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" style="text-decoration:none;">Wishlist</a>
                                <a href="{{ route('customer-service.index') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" style="text-decoration:none;">
                                    Help
                                    @if($unreadMessagesCount > 0)
                                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $unreadMessagesCount }}</span>
                                    @endif
                                </a>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100" style="text-decoration:none;">Account</a>
                                <button type="button" onclick="showLogoutModal()" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100 bg-transparent border-none cursor-pointer">Logout</button>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="flex flex-col items-center text-gray-700 hover:text-gray-900" style="text-decoration:none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </a>
                        <button type="button" onclick="showLogoutModal()" class="flex flex-col items-center text-gray-700 hover:text-gray-900 bg-transparent border-none cursor-pointer p-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    @endunless
                    </div>
                </div>
                <nav id="desktopNav" class="hidden md:flex gap-3 flex-wrap items-center">
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
                            <a href="{{ route('customer-service.index') }}" class="unread-help-badge btn" style="background:#dc2626;padding:10px 16px;border-radius:50px;font-weight:600;text-decoration:none;font-size:0.85rem;">
                                🔔 {{ $unreadMessagesCount }} new message{{ $unreadMessagesCount > 1 ? 's' : '' }}
                            </a>
                        @endif
                    @else
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endunless
                    <button type="button" onclick="showLogoutModal()" style="background:none;border:none;color:#111;cursor:pointer;font-size:0.9rem;">Logout</button>
                </nav>
            </div>
        @endguest
        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"></div>
        <div id="mobileMenu" class="hidden fixed top-0 left-0 h-full w-64 bg-white z-50 shadow-lg transform transition-transform duration-300 ease-in-out md:hidden pt-4 pb-4 px-4 overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <a href="{{ url('/') }}" class="text-xl font-bold text-gray-900 no-underline">ROI Store</a>
                <button id="closeMobileMenu" class="bg-transparent border-none cursor-pointer text-2xl text-gray-600 hover:text-gray-900">
                    ✕
                </button>
            </div>
            <nav class="flex flex-col gap-2">
                @guest
                    <a class="nav-link" href="{{ route('cart.index') }}">Cart</a>
                    <a class="btn" href="{{ route('login') }}" style="margin-top:8px;">Login</a>
                @else
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
                            <a href="{{ route('customer-service.index') }}" class="unread-help-badge btn" style="background:#dc2626;padding:10px 16px;border-radius:50px;font-weight:600;text-decoration:none;font-size:0.85rem;">
                                🔔 {{ $unreadMessagesCount }} new message{{ $unreadMessagesCount > 1 ? 's' : '' }}
                            </a>
                        @endif
                    @else
                        <a class="nav-link" href="{{ route('admin.dashboard') }}">Admin</a>
                    @endunless
                    <button type="button" onclick="showLogoutModal()" style="background:none;border:none;color:#111;cursor:pointer;font-size:0.9rem;">Logout</button>
                @endif
            </nav>
        </div>
    </header>

    <main class="container">
        @include('partials.alerts')
        @yield('content')
    </main>

    @auth
        @unless(auth()->user()->isAdmin())
            @php
                $floatCartCount = auth()->user()->cartItems()->count();
            @endphp
            <a href="{{ route('cart.index') }}" class="cart-float" title="View Cart" style="display:flex;align-items:center;justify-content:center;">
                <svg style="width:28px;height:28px;stroke:#fff;fill:none;stroke-width:2;" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                @if($floatCartCount > 0)
                    <sup>{{ $floatCartCount }}</sup>
                @endif
            </a>
        @endunless
    @endauth

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal-overlay hidden">
        <div class="modal-card">
            <h3 class="modal-title">Confirm Logout</h3>
            <p class="modal-text">Are you sure you want to log out of this page?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="hideLogoutModal()">Cancel</button>
                <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="modal-btn modal-btn-confirm">Confirm</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let previousSearchPage = null;

        function goBack(button) {
            const fallback = button?.dataset?.fallback || '/';
            if (window.history.length > 1) {
                history.back();
            } else {
                window.location.href = fallback;
            }
        }

        function setupSearchBar(form) {
            const input = form.querySelector('input[name="search"]');
            const searchBtn = form.querySelector('button[type="submit"]');
            
            if (!input || !searchBtn) return;

            // Check if we're on a search results page
            const hasSearchParam = window.location.search.includes('search=');
            const searchValue = input.value.trim();

            // If on search results page, hide search button and show cancel button
            if (hasSearchParam || searchValue) {
                searchBtn.style.display = 'none';
                let cancelBtn = form.querySelector('.search-cancel-btn');
                if (!cancelBtn) {
                    cancelBtn = document.createElement('button');
                    cancelBtn.type = 'button';
                    cancelBtn.className = 'search-cancel-btn';
                    cancelBtn.innerHTML = '✕';
                    cancelBtn.style.cssText = 'position:absolute;right:4px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;font-size:1rem;color:#6c757d;line-height:1;';
                    cancelBtn.onclick = function(e) {
                        e.preventDefault();
                        // Clear input and navigate back
                        window.location.href = previousSearchPage || window.location.pathname;
                    };
                    input.parentNode.appendChild(cancelBtn);
                }
            }

            // Handle input changes
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    searchBtn.style.display = 'none';
                    let cancelBtn = form.querySelector('.search-cancel-btn');
                    if (!cancelBtn) {
                        cancelBtn = document.createElement('button');
                        cancelBtn.type = 'button';
                        cancelBtn.className = 'search-cancel-btn';
                        cancelBtn.innerHTML = '✕';
                        cancelBtn.style.cssText = 'position:absolute;right:4px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:2px;font-size:1rem;color:#6c757d;line-height:1;';
                        cancelBtn.onclick = function(e) {
                            e.preventDefault();
                            input.value = '';
                            searchBtn.style.display = 'block';
                            cancelBtn.remove();
                            // If on search results page, navigate back
                            if (window.location.search.includes('search=')) {
                                window.location.href = previousSearchPage || window.location.pathname;
                            } else {
                                input.focus();
                            }
                        };
                        input.parentNode.appendChild(cancelBtn);
                    }
                } else {
                    // Only show search button if not on search results page
                    if (!window.location.search.includes('search=')) {
                        searchBtn.style.display = 'block';
                    }
                    const cancelBtn = form.querySelector('.search-cancel-btn');
                    if (cancelBtn) cancelBtn.remove();
                }
            });

            // Store current page before search
            if (!hasSearchParam && !searchValue) {
                previousSearchPage = window.location.href;
            }

            // Handle form submission
            form.addEventListener('submit', function(e) {
                if (!input.value.trim()) {
                    e.preventDefault();
                    return false;
                }
            });
        }

        // Initialize all search bars on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('form[method="GET"]').forEach(function(form) {
                if (form.querySelector('input[name="search"]')) {
                    setupSearchBar(form);
                }
            });
        });

        function toggleProfileDropdown(event) {
            if (event) {
                event.stopPropagation();
            }
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                if (dropdown.classList.contains('hidden')) {
                    dropdown.classList.remove('hidden');
                } else {
                    dropdown.classList.add('hidden');
                }
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const button = event.target.closest('button[onclick^="toggleProfileDropdown"]');
            if (dropdown && !dropdown.contains(event.target) && !button) {
                dropdown.classList.add('hidden');
            }
        });

        document.getElementById('mobileMenuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            mobileMenu.classList.remove('hidden');
            overlay.classList.remove('hidden');
        });

        document.getElementById('closeMobileMenu').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            mobileMenu.classList.add('hidden');
            overlay.classList.add('hidden');
        });

        document.getElementById('mobileMenuOverlay').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            mobileMenu.classList.add('hidden');
            overlay.classList.add('hidden');
        });

        // Logout Modal Functions
        function showLogoutModal() {
            const modal = document.getElementById('logoutModal');
            const dropdown = document.getElementById('profileDropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
            modal.classList.remove('hidden');
        }

        function hideLogoutModal() {
            const modal = document.getElementById('logoutModal');
            modal.classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('logoutModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideLogoutModal();
            }
        });
    </script>
</body>
</html>