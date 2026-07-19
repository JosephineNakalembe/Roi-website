<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerServiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\CustomerServiceController as AdminCustomerServiceController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\PreventAdminAccess;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Test email from ROI Store', function ($message) {
            $message->to(\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->email : 'josephinenakalembe33@gmail.com')
                    ->subject('ROI Store - Test Email');
        });
        return response()->json(['status' => 'sent', 'mailer' => config('mail.default'), 'host' => config('mail.mailers.smtp.host')]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'failed', 'error' => $e->getMessage(), 'mailer' => config('mail.default')]);
    }
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('password.send-code');
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerifyCodeForm'])->name('password.verify-code');
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyCode'])->name('password.verify-code.post');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.post');
});

Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/shop', [ProductController::class, 'index'])->name('shop.index');
Route::get('/shop/{slug}', [ProductController::class, 'show'])->name('shop.show');

Route::middleware(['auth', PreventAdminAccess::class])->group(function () {
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
    Route::post('/profile/addresses', [ProfileController::class, 'saveAddress'])->name('profile.address.save');
    Route::get('/profile/payments', [ProfileController::class, 'paymentMethods'])->name('profile.payments');
    Route::post('/profile/payments', [ProfileController::class, 'savePaymentMethod'])->name('profile.payment.save');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm-received', [OrderController::class, 'confirmReceived'])->name('orders.confirm-received');
    Route::post('/orders/{order}/items/{item}/review', [OrderController::class, 'review'])->name('orders.items.review');
    Route::post('/orders/{order}/bulk-review', [OrderController::class, 'bulkReview'])->name('orders.bulk-review');
    Route::post('/orders/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/{order}/return', [OrderReturnController::class, 'create'])->name('orders.return.create');
    Route::post('/orders/{order}/return', [OrderReturnController::class, 'store'])->name('orders.return.store');
    Route::get('/returns/{orderReturn}/track', [OrderReturnController::class, 'track'])->name('returns.track');
    Route::get('/returns', [OrderReturnController::class, 'myReturns'])->name('returns.index');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/{product}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/customer-service', [CustomerServiceController::class, 'index'])->name('customer-service.index');
    Route::post('/customer-service', [CustomerServiceController::class, 'store'])->name('customer-service.store');
    Route::post('/customer-service/{message}/reply', [CustomerServiceController::class, 'reply'])->name('customer-service.reply');
    Route::post('/customer-service/{message}/close', [CustomerServiceController::class, 'close'])->name('customer-service.close');
    Route::post('/profile/delete-account', [ProfileController::class, 'deleteAccount'])->name('profile.delete-account');
});

// Cart routes accessible to guests and logged-in users
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/toggle-select', [CartController::class, 'toggleSelect'])->name('cart.toggle-select');
Route::post('/cart/toggle-select-all', [CartController::class, 'toggleSelectAll'])->name('cart.toggle-select-all');
Route::post('/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');

Route::prefix('admin')->middleware(['auth', AdminMiddleware::class])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
    Route::patch('categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
    Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');
    Route::get('products/next-id', [AdminProductController::class, 'nextId'])->name('admin.products.next-id');
    Route::post('products/{product}/add-stock', [AdminProductController::class, 'addStock'])->name('admin.products.add-stock');
    Route::delete('products/media/{media}', [AdminProductController::class, 'destroyMedia'])->name('admin.products.destroy-media');
    Route::resource('products', AdminProductController::class)->names('admin.products')->except(['show']);
    Route::get('orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('orders/{order}', [AdminOrderController::class, 'update'])->name('admin.orders.update');
    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('admin.users.status');
    Route::get('support', [AdminCustomerServiceController::class, 'index'])->name('admin.support.index');
    Route::get('support/{message}', [AdminCustomerServiceController::class, 'show'])->name('admin.support.show');
    Route::patch('support/{message}', [AdminCustomerServiceController::class, 'update'])->name('admin.support.update');
    Route::get('reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('returns', [AdminReturnController::class, 'index'])->name('admin.returns.index');
    Route::get('returns/{orderReturn}', [AdminReturnController::class, 'show'])->name('admin.returns.show');
    Route::patch('returns/{orderReturn}', [AdminReturnController::class, 'update'])->name('admin.returns.update');
});
