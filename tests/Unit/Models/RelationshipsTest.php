<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemReturn;
use App\Models\OrderItemReview;
use App\Models\OrderReturn;
use App\Models\OrderReturnItem;
use App\Models\OrderReturnUpdate;
use App\Models\OrderUpdate;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RelationshipsTest extends TestCase
{
    /**
     * @return array<string, array{0: class-string<Model>, 1: string, 2: class-string<Relation>, 3: class-string<Model>}>
     */
    public static function relations(): array
    {
        return [
            'Address->user' => [Address::class, 'user', BelongsTo::class, User::class],
            'Address->orders' => [Address::class, 'orders', HasMany::class, Order::class],
            'CartItem->user' => [CartItem::class, 'user', BelongsTo::class, User::class],
            'CartItem->product' => [CartItem::class, 'product', BelongsTo::class, Product::class],
            'Category->products' => [Category::class, 'products', BelongsToMany::class, Product::class],
            'CustomerMessage->user' => [CustomerMessage::class, 'user', BelongsTo::class, User::class],
            'OrderItem->order' => [OrderItem::class, 'order', BelongsTo::class, Order::class],
            'OrderItem->product' => [OrderItem::class, 'product', BelongsTo::class, Product::class],
            'OrderItem->review' => [OrderItem::class, 'review', HasOne::class, OrderItemReview::class],
            'OrderItemReturn->item' => [OrderItemReturn::class, 'item', BelongsTo::class, OrderItem::class],
            'OrderItemReturn->user' => [OrderItemReturn::class, 'user', BelongsTo::class, User::class],
            'OrderItemReview->item' => [OrderItemReview::class, 'item', BelongsTo::class, OrderItem::class],
            'OrderItemReview->user' => [OrderItemReview::class, 'user', BelongsTo::class, User::class],
            'OrderReturnItem->orderReturn' => [OrderReturnItem::class, 'orderReturn', BelongsTo::class, OrderReturn::class],
            'OrderReturnItem->orderItem' => [OrderReturnItem::class, 'orderItem', BelongsTo::class, OrderItem::class],
            'OrderReturnUpdate->orderReturn' => [OrderReturnUpdate::class, 'orderReturn', BelongsTo::class, OrderReturn::class],
            'OrderUpdate->order' => [OrderUpdate::class, 'order', BelongsTo::class, Order::class],
            'PaymentMethod->user' => [PaymentMethod::class, 'user', BelongsTo::class, User::class],
            'PaymentMethod->orders' => [PaymentMethod::class, 'orders', HasMany::class, Order::class],
            'ProductImage->product' => [ProductImage::class, 'product', BelongsTo::class, Product::class],
            'WishlistItem->user' => [WishlistItem::class, 'user', BelongsTo::class, User::class],
            'WishlistItem->product' => [WishlistItem::class, 'product', BelongsTo::class, Product::class],
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  class-string<Relation>  $relationType
     * @param  class-string<Model>  $related
     */
    #[DataProvider('relations')]
    public function test_relationship_is_configured_correctly(string $model, string $method, string $relationType, string $related): void
    {
        $instance = new $model;

        $relation = $instance->{$method}();

        $this->assertInstanceOf($relationType, $relation);
        $this->assertInstanceOf($related, $relation->getRelated());
    }
}
