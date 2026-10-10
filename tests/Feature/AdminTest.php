<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->staff('admin'));
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Heritage Card Holder',
                'slug' => 'heritage-card-holder',
                'sku' => 'LX-HCH-01',
                'category_id' => $category->id,
                'regular_price' => 1290,
                'sale_price' => 1090,
                'stock_quantity' => 15,
                'leather_type' => 'Vegetable-tanned leather',
                'warranty_months' => 12,
                'rfid_protection' => true,
                'status' => ProductStatus::Active,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('sku', 'LX-HCH-01')->sole();
        $this->assertSame('heritage-card-holder', $product->slug);
        $this->assertSame(15, $product->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => 15, 'type' => 'restock']);
    }

    public function test_product_form_validates_required_and_unique_fields(): void
    {
        $this->actingAs($this->staff('admin'));
        $existing = $this->product();

        Livewire::test(CreateProduct::class)
            ->fillForm(['name' => '', 'slug' => $existing->slug, 'sku' => $existing->sku, 'regular_price' => 1000, 'sale_price' => 1500])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required', 'slug' => 'unique', 'sku' => 'unique', 'sale_price']);
    }

    public function test_admin_can_adjust_stock(): void
    {
        $this->actingAs($this->staff('admin'));
        $product = $this->product(['stock_quantity' => 3]);

        Livewire::test(EditProduct::class, ['record' => $product->slug])
            ->callAction('adjust_stock', ['type' => 'restock', 'quantity' => 7, 'note' => 'New batch'])
            ->assertHasNoActionErrors();

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'quantity' => 7, 'balance_after' => 10]);
    }

    public function test_support_role_cannot_create_products(): void
    {
        $this->actingAs($this->staff('support'));

        Livewire::test(ListProducts::class)->assertOk();
        $this->get('/admin/products/create')->assertForbidden();
    }

    public function test_admin_can_change_order_status(): void
    {
        $this->actingAs($this->staff('admin'));
        $product = $this->product(['stock_quantity' => 5]);
        app(CartService::class)->add($product, null, 1);
        $order = app(CheckoutService::class)->placeOrder([
            'customer_name' => 'Test', 'phone' => '01712345678', 'address' => 'Road 1', 'city' => 'Dhaka',
            'shipping_zone_id' => ShippingZone::value('id'), 'payment_method' => 'cod',
        ]);

        Livewire::test(ViewOrder::class, ['record' => $order->order_number])
            ->callAction('change_status', ['status' => 'shipped', 'courier_name' => 'Steadfast', 'tracking_number' => 'SF123'])
            ->assertHasNoActionErrors();

        $order->refresh();
        $this->assertSame(OrderStatus::Shipped, $order->status);
        $this->assertSame('SF123', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->staff());
        $this->product();

        foreach (['/admin', '/admin/products', '/admin/products/create', '/admin/orders', '/admin/categories', '/admin/coupons',
            '/admin/customers', '/admin/engraving-requests', '/admin/nfc-profiles', '/admin/warranty-registrations',
            '/admin/corporate-inquiries', '/admin/home-sections', '/admin/pages', '/admin/posts', '/admin/faqs',
            '/admin/shipping-zones', '/admin/payment-methods', '/admin/users', '/admin/roles', '/admin/reports',
            '/admin/website-settings', '/admin/seo-settings', '/admin/stock-movements', '/admin/payments', '/admin/carts'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
