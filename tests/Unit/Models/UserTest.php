<?php

namespace Tests\Unit\Models;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\CustomerMessage;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_is_recognised_as_admin(): void
    {
        $user = new User(['role' => 'admin', 'email' => 'someone@example.com']);

        $this->assertTrue($user->isAdmin());
    }

    public function test_owner_email_is_always_admin(): void
    {
        $user = new User(['role' => 'user', 'email' => 'josephinenakalembe33@gmail.com']);

        $this->assertTrue($user->isAdmin());
    }

    public function test_regular_user_is_not_admin(): void
    {
        $user = new User(['role' => 'user', 'email' => 'customer@example.com']);

        $this->assertFalse($user->isAdmin());
    }

    public function test_password_and_remember_token_are_hidden_from_serialization(): void
    {
        $user = User::factory()->create();

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_password_is_hashed_when_set(): void
    {
        $user = User::factory()->create(['password' => 'plain-text-secret']);

        $this->assertNotSame('plain-text-secret', $user->password);
        $this->assertTrue(password_verify('plain-text-secret', $user->password));
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Carbon::class, $user->email_verified_at);
    }

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public static function hasManyRelations(): array
    {
        return [
            'orders' => ['orders', Order::class],
            'addresses' => ['addresses', Address::class],
            'paymentMethods' => ['paymentMethods', PaymentMethod::class],
            'wishlistItems' => ['wishlistItems', WishlistItem::class],
            'cartItems' => ['cartItems', CartItem::class],
            'customerMessages' => ['customerMessages', CustomerMessage::class],
        ];
    }

    /**
     * @param  class-string  $related
     */
    #[DataProvider('hasManyRelations')]
    public function test_user_has_many_relations(string $method, string $related): void
    {
        $user = new User;

        $relation = $user->{$method}();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf($related, $relation->getRelated());
    }
}
