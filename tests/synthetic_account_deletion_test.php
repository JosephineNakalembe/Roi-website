<?php

/**
 * Synthetic test for anonymous account deletion.
 *
 * Runs fully in-memory (SQLite — no MySQL). Verifies that when a user
 * deletes their account:
 *   1. Order + return records are KEPT for admin but anonymized
 *      (user_id / name / phone / notes / refund / pickup details removed).
 *   2. Personal data (addresses, messages, cart, wishlist) is deleted.
 *   3. The account is removed, so the same email can be re-registered.
 *   4. A re-registered account does NOT get the old order history back.
 *
 * Run with:  php tests/synthetic_account_deletion_test.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ProfileController;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ---------------------------------------------------------------- helpers

$pass = 0;
$fail = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo '  ['.($ok ? 'PASS' : 'FAIL').'] '.$label.($detail !== '' ? " — $detail" : '').PHP_EOL;
}

// ------------------------------------------------------------ environment

config(['database.default' => 'sqlite']);
config(['database.connections.sqlite.database' => ':memory:']);
config(['cache.default' => 'array']);
config(['session.driver' => 'array']);
config(['mail.default' => 'array']);

Schema::create('users', function ($t) {
    $t->id();
    $t->string('name');
    $t->string('email')->unique();
    $t->string('password');
    $t->string('role')->default('user');
    $t->string('status')->default('active');
    $t->string('gender')->nullable();
    $t->timestamps();
});

Schema::create('addresses', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->string('line1')->nullable();
    $t->string('city')->nullable();
    $t->timestamps();
});

Schema::create('payment_methods', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->string('last4')->nullable();
    $t->timestamps();
});

Schema::create('cart_items', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('product_id');
    $t->timestamps();
});

Schema::create('wishlist_items', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->unsignedBigInteger('product_id');
    $t->timestamps();
});

Schema::create('customer_messages', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id');
    $t->string('subject')->nullable();
    $t->timestamps();
});

Schema::create('orders', function ($t) {
    $t->id();
    $t->unsignedBigInteger('user_id')->nullable();
    $t->unsignedBigInteger('address_id')->nullable();
    $t->unsignedBigInteger('payment_method_id')->nullable();
    $t->string('shipping_name')->nullable();
    $t->string('shipping_phone')->nullable();
    $t->string('delivery_area')->nullable();
    $t->string('order_number');
    $t->string('status')->default('pending');
    $t->text('notes')->nullable();
    $t->decimal('total', 10, 2)->default(0);
    $t->timestamps();
});

Schema::create('order_returns', function ($t) {
    $t->id();
    $t->unsignedBigInteger('order_id');
    $t->unsignedBigInteger('user_id')->nullable();
    $t->string('return_number');
    $t->string('reason');
    $t->text('notes')->nullable();
    $t->string('refund_number')->nullable();
    $t->string('refund_name')->nullable();
    $t->string('pickup_address')->nullable();
    $t->string('pickup_contact')->nullable();
    $t->string('status')->default('pending');
    $t->timestamps();
});

// ------------------------------------------------------------- seed data

$user = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => 'secret123',
    'role' => 'user',
    'status' => 'active',
    'gender' => 'female',
]);

$address = \App\Models\Address::create(['user_id' => $user->id, 'line1' => 'Plot 7 Kampala Rd', 'city' => 'Kampala']);

$order = Order::create([
    'user_id' => $user->id,
    'address_id' => $address->id,
    'order_number' => 'RS24001',
    'shipping_name' => 'Jane Doe',
    'shipping_phone' => '+256700123456',
    'delivery_area' => 'Kampala Central',
    'notes' => 'Delivery Area: Kampala Central | Address: Plot 7 Kampala Rd',
    'total' => 45000,
    'status' => 'delivered',
]);

$return = OrderReturn::create([
    'order_id' => $order->id,
    'user_id' => $user->id,
    'return_number' => 'RET24001',
    'reason' => 'Item Arrived Damaged',
    'notes' => 'Arrived broken',
    'refund_number' => '+256701111111',
    'refund_name' => 'Jane Doe',
    'pickup_address' => 'Plot 7 Kampala Rd',
    'pickup_contact' => '+256700123456',
    'status' => 'pending',
]);

$payment = \App\Models\PaymentMethod::create(['user_id' => $user->id, 'last4' => '4242']);
\App\Models\CartItem::create(['user_id' => $user->id, 'product_id' => 1]);
\App\Models\WishlistItem::create(['user_id' => $user->id, 'product_id' => 1]);
\App\Models\CustomerMessage::create(['user_id' => $user->id, 'subject' => 'Hello']);
$orderId = $order->id;
$returnId = $return->id;

// ------------------------------------------------------------ delete flow

Auth::login($user);
$controller = new ProfileController();
$request = Request::create('/profile/delete-account', 'POST', ['confirm' => '1']);
$controller->deleteAccount($request);

// ------------------------------------------------------------- assertions

echo "\n== 1. Sales records kept but anonymized ==\n";
$order = Order::find($orderId);
check('order kept for admin', $order !== null);
if ($order) {
    check('order user_id anonymized', $order->user_id === null);
    check('order shipping_name removed', $order->shipping_name === null);
    check('order shipping_phone removed', $order->shipping_phone === null);
    check('order notes (address) removed', $order->notes === null);
    check('order totals/area kept for analytics', $order->total == 45000 && $order->delivery_area === 'Kampala Central', "total={$order->total} area={$order->delivery_area}");
    check('order status kept', $order->status === 'delivered');
}

$return = OrderReturn::find($returnId);
check('return kept for admin', $return !== null);
if ($return) {
    check('return user_id anonymized', $return->user_id === null);
    check('return refund details removed', $return->refund_name === null && $return->refund_number === null);
    check('return pickup details removed', $return->pickup_address === null && $return->pickup_contact === null);
    check('return reason/status kept', $return->reason === 'Item Arrived Damaged' && $return->status === 'pending');
}

echo "\n== 2. Personal data deleted ==\n";
check('address deleted', \App\Models\Address::find($address->id) === null);
check('payment method deleted', \App\Models\PaymentMethod::find($payment->id) === null);
check('customer message deleted', \App\Models\CustomerMessage::where('user_id', $user->id)->count() === 0);
check('user account deleted', User::find($user->id) === null);
check('orphaned orders not linked to any account', Order::whereNotNull('user_id')->count() === 0);

echo "\n== 3. Email reusable + no data revival ==\n";
$newUser = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => 'newpassword1',
    'role' => 'user',
    'status' => 'active',
]);
check('same email can be registered again', $newUser->exists);
check('new account has no old order history', $newUser->orders()->count() === 0);
check('old anonymized order not retrievable by anyone', Order::find($orderId)->user_id === null);

echo "\nRESULTS: $pass passed, $fail failed".PHP_EOL;
exit($fail > 0 ? 1 : 0);
