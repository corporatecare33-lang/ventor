<?php

namespace Tests\Feature;

use App\Enums\EngravingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Livewire\AddToCart;
use App\Livewire\Checkout;
use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    private function checkout(string $token)
    {
        return Livewire::withCookie(CartService::COOKIE, $token)->test(Checkout::class)
            ->set('customer_name', 'Ashik Rahman')
            ->set('phone', '+880 1712-345678')
            ->set('email', 'ashik@example.com')
            ->set('address', 'House 12, Road 5')
            ->set('area', 'Dhanmondi')
            ->set('city', 'Dhaka')
            ->set('shipping_zone_id', ShippingZone::where('name', 'Inside Dhaka')->value('id'));
    }

    public function test_checkout_creates_order_and_reduces_stock(): void
    {
        $product = $this->product(['regular_price' => 1000, 'stock_quantity' => 5]);
        $item = app(CartService::class)->add($product, null, 2);

        $this->checkout($item->cart->token)
            ->set('payment_method', 'cod')
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect();

        $order = Order::with('items')->sole();
        $this->assertSame('01712345678', $order->phone, 'phone is normalised');
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertEquals(2000, $order->subtotal);
        $this->assertEquals(70, $order->shipping_charge);
        $this->assertEquals(2070, $order->total);
        $this->assertCount(1, $order->items);
        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertSame(2, $product->fresh()->sold_count);
        $this->assertNotNull(Cart::first()->converted_at);
        $this->assertDatabaseHas('stock_movements', ['order_id' => $order->id, 'quantity' => -2]);
    }

    public function test_free_shipping_threshold_and_coupon(): void
    {
        $product = $this->product(['regular_price' => 3500, 'stock_quantity' => 5]);
        $item = app(CartService::class)->add($product, null, 1);
        app(CartService::class)->applyCoupon('WELCOME10');

        $this->checkout($item->cart->token)->set('payment_method', 'cod')->call('placeOrder')->assertHasNoErrors();

        $order = Order::sole();
        $this->assertEquals(350, $order->discount);
        $this->assertEquals(0, $order->shipping_charge, 'over ৳3,000 after discount ships free inside Dhaka');
        $this->assertEquals(3150, $order->total);
        $this->assertSame(1, $order->coupon->used_count);
    }

    public function test_bkash_requires_transaction_id_and_creates_pending_payment(): void
    {
        // bKash ships inactive until the business adds its own number.
        PaymentMethod::where('code', 'bkash')->update(['is_active' => true, 'account_number' => '01811000000']);
        $product = $this->product(['stock_quantity' => 5]);
        $item = app(CartService::class)->add($product, null, 1);

        $this->checkout($item->cart->token)->set('payment_method', 'bkash')->call('placeOrder')
            ->assertHasErrors('transaction_id');
        $this->assertSame(0, Order::count());

        $this->checkout($item->cart->token)->set('payment_method', 'bkash')->set('transaction_id', 'ABC123XYZ')
            ->call('placeOrder')->assertHasNoErrors();

        $order = Order::sole();
        $this->assertSame(PaymentStatus::PendingVerification, $order->payment_status);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'transaction_id' => 'ABC123XYZ', 'status' => 'pending']);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $item = app(CartService::class)->add($this->product(), null, 1);

        $this->checkout($item->cart->token)->set('phone', '12345')->call('placeOrder')->assertHasErrors('phone');
        $this->assertSame(0, Order::count());
    }

    public function test_out_of_stock_at_checkout_is_blocked(): void
    {
        $product = $this->product(['stock_quantity' => 2]);
        $item = app(CartService::class)->add($product, null, 2);
        $product->update(['stock_quantity' => 1]);

        $this->checkout($item->cart->token)->set('payment_method', 'cod')->call('placeOrder')->assertHasErrors('checkout');
        $this->assertSame(0, Order::count());
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_confirmation_page_requires_signed_url(): void
    {
        $item = app(CartService::class)->add($this->product(), null, 1);
        $this->checkout($item->cart->token)->set('payment_method', 'cod')->call('placeOrder');
        $order = Order::sole();

        $this->get(route('orders.confirmation', $order))->assertForbidden();
        $this->get(\URL::temporarySignedRoute('orders.confirmation', now()->addHour(), ['order' => $order->order_number]))
            ->assertOk()->assertSee($order->order_number);
    }

    public function test_engraving_from_product_page_creates_engraving_request_with_order(): void
    {
        $product = $this->product(['regular_price' => 2000, 'engraving_price' => 300, 'stock_quantity' => 5]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('engrave', true)
            ->set('engravingType', 'initials')
            ->set('engravingText', 'A.R.K')
            ->call('add')
            ->assertHasNoErrors();

        $cart = Cart::sole();
        $this->assertSame('A.R.K', $cart->items()->first()->engraving['text']);

        $this->checkout($cart->token)->set('payment_method', 'cod')->call('placeOrder')->assertHasNoErrors();

        $order = Order::with('engravingRequests')->sole();
        $this->assertEquals(300, $order->engraving_total);
        $this->assertEquals(2370, $order->total);
        $this->assertCount(1, $order->engravingRequests);
        $this->assertSame(EngravingStatus::Pending, $order->engravingRequests->first()->status);
        $this->assertSame('A.R.K', $order->engravingRequests->first()->text);
    }

    public function test_engraving_requires_text(): void
    {
        Livewire::test(AddToCart::class, ['product' => $this->product()])
            ->set('engrave', true)
            ->set('engravingType', 'name')
            ->set('engravingText', '')
            ->call('add')
            ->assertHasErrors('engravingText');
    }

    public function test_cancelling_an_order_restocks_items(): void
    {
        $product = $this->product(['stock_quantity' => 5]);
        $item = app(CartService::class)->add($product, null, 2);
        $this->checkout($item->cart->token)->set('payment_method', 'cod')->call('placeOrder');

        $order = Order::sole();
        app(OrderService::class)->changeStatus($order, OrderStatus::Cancelled, 'Customer request');

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', ['order_id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_order_tracking_requires_matching_phone(): void
    {
        $item = app(CartService::class)->add($this->product(), null, 1);
        $this->checkout($item->cart->token)->set('payment_method', 'cod')->call('placeOrder');
        $order = Order::sole();

        $this->post('/track-order', ['order_number' => $order->order_number, 'phone' => '01712345678'])
            ->assertOk()->assertSee('Order '.$order->order_number);

        $this->post('/track-order', ['order_number' => $order->order_number, 'phone' => '01899999999'])
            ->assertOk()->assertSee('No order matches');
    }
}
