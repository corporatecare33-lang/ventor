<?php

namespace Tests\Feature;

use App\Livewire\AddToCart;
use App\Livewire\CartPage;
use App\Models\Cart;
use App\Models\ProductOption;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    private function cartItems()
    {
        return Cart::latest('id')->first()?->items()->get() ?? collect();
    }

    public function test_add_to_cart(): void
    {
        $product = $this->product(['stock_quantity' => 5]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('quantity', 2)
            ->call('add')
            ->assertHasNoErrors()
            ->assertDispatched('cart-updated');

        $this->assertSame(2, (int) $this->cartItems()->sum('quantity'));
    }

    public function test_variant_product_adds_selected_variant(): void
    {
        $product = $this->product();
        $black = ProductOption::create(['type' => 'color', 'name' => 'Black', 'value' => '#000']);
        $tan = ProductOption::create(['type' => 'color', 'name' => 'Tan', 'value' => '#a0612b']);
        $product->variants()->create(['color_id' => $black->id, 'sku' => 'V-BLK', 'stock_quantity' => 3]);
        $tanVariant = $product->variants()->create(['color_id' => $tan->id, 'sku' => 'V-TAN', 'stock_quantity' => 4, 'price' => 2500]);

        $this->assertSame(7, $product->fresh()->stock_quantity, 'product stock mirrors variant stock');

        Livewire::test(AddToCart::class, ['product' => $product->fresh()])
            ->call('selectColor', $tan->id)
            ->call('add')
            ->assertHasNoErrors();

        $item = $this->cartItems()->first();
        $this->assertSame($tanVariant->id, $item->product_variant_id);
        $this->assertEquals(2500, $item->unitPrice());
    }

    public function test_cannot_add_more_than_stock(): void
    {
        $product = $this->product(['stock_quantity' => 1]);

        $this->expectException(ValidationException::class);
        app(CartService::class)->add($product, null, 2);
    }

    public function test_update_quantity_and_remove_item(): void
    {
        $product = $this->product(['stock_quantity' => 10]);
        $item = app(CartService::class)->add($product, null, 1);
        $token = $item->cart->token;

        Livewire::withCookie(CartService::COOKIE, $token)->test(CartPage::class)
            ->call('updateQuantity', $item->id, 3)
            ->assertHasNoErrors();
        $this->assertSame(3, $item->fresh()->quantity);

        Livewire::withCookie(CartService::COOKIE, $token)->test(CartPage::class)
            ->call('updateQuantity', $item->id, 50)
            ->assertHasErrors('cart');
        $this->assertSame(3, $item->fresh()->quantity);

        Livewire::withCookie(CartService::COOKIE, $token)->test(CartPage::class)
            ->call('remove', $item->id);
        $this->assertNull($item->fresh());
    }

    public function test_coupon_applies_discount(): void
    {
        $product = $this->product(['regular_price' => 3000, 'stock_quantity' => 10]);
        $item = app(CartService::class)->add($product, null, 1);

        Livewire::withCookie(CartService::COOKIE, $item->cart->token)->test(CartPage::class)
            ->set('couponCode', 'welcome10')
            ->call('applyCoupon')
            ->assertHasNoErrors()
            ->assertSee('WELCOME10')
            ->assertSee('−৳300');

        Livewire::withCookie(CartService::COOKIE, $item->cart->token)->test(CartPage::class)
            ->set('couponCode', 'NOPE')
            ->call('applyCoupon')
            ->assertHasErrors('couponCode');
    }

    public function test_guest_cart_merges_into_customer_cart_on_login(): void
    {
        $product = $this->product();
        $user = $this->customer(['password' => 'Password123']);
        $guestItem = app(CartService::class)->add($product, null, 2);

        $this->withCookie(CartService::COOKIE, $guestItem->cart->token)
            ->post('/login', ['email' => $user->email, 'password' => 'Password123'])
            ->assertRedirect(route('account.dashboard'));

        $this->assertSame($user->id, $guestItem->cart->fresh()->user_id);
    }
}
