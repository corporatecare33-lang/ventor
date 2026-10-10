<?php

namespace Tests\Feature;

use App\Enums\NfcProfileStatus;
use App\Enums\OrderStatus;
use App\Livewire\WarrantyForm;
use App\Models\NfcProfile;
use App\Models\Order;
use App\Models\ShippingZone;
use App\Models\User;
use App\Models\WarrantyRegistration;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    private function placeOrder(array $productAttributes = [], ?User $user = null): Order
    {
        if ($user) {
            $this->actingAs($user);
        }
        app(CartService::class)->add($this->product($productAttributes), null, 1);

        $order = app(CheckoutService::class)->placeOrder([
            'customer_name' => 'Ashik', 'phone' => '01712345678', 'address' => 'Road 1', 'city' => 'Dhaka',
            'shipping_zone_id' => ShippingZone::value('id'), 'payment_method' => 'cod',
        ]);
        app(CartService::class)->forget();

        return $order;
    }

    public function test_standalone_engraving_request(): void
    {
        $product = $this->product();

        $this->post('/engraving', [
            'customer_name' => 'Rafi', 'phone' => '01912345678', 'product_id' => $product->id,
            'type' => 'name', 'text' => 'Rafi Ahmed', 'font' => 'Script', 'position' => 'Front center', 'quantity' => 1,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('engraving_requests', ['text' => 'Rafi Ahmed', 'status' => 'pending', 'order_id' => null]);

        $this->post('/engraving', ['customer_name' => 'Rafi', 'phone' => '01912345678', 'type' => 'logo', 'quantity' => 1])
            ->assertSessionHasErrors('logo_instruction');
    }

    public function test_warranty_registration_for_delivered_order(): void
    {
        $order = $this->placeOrder(['warranty_months' => 24]);
        app(OrderService::class)->changeStatus($order, OrderStatus::Delivered);
        $item = $order->items()->first();

        Livewire::test(WarrantyForm::class)
            ->set('order_number', strtolower($order->order_number))
            ->set('phone', '01712345678')
            ->call('findOrder')
            ->assertHasNoErrors()
            ->set('customer_name', 'Ashik')
            ->set('order_item_id', $item->id)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSee('Submitted successfully');

        $record = WarrantyRegistration::sole();
        $this->assertSame($order->id, $record->order_id);
        $this->assertTrue($record->isActive());
        $this->assertSame(now()->addMonths(24)->toDateString(), $record->expires_at->toDateString());
    }

    public function test_warranty_claim_requires_issue_details_and_matching_phone(): void
    {
        $order = $this->placeOrder();
        app(OrderService::class)->changeStatus($order, OrderStatus::Delivered);

        Livewire::test(WarrantyForm::class)
            ->set('order_number', $order->order_number)->set('phone', '01799999999')
            ->call('findOrder')->assertHasErrors('order_number');

        Livewire::test(WarrantyForm::class)
            ->set('type', 'claim')
            ->set('order_number', $order->order_number)->set('phone', '01712345678')->call('findOrder')
            ->set('customer_name', 'Ashik')->set('order_item_id', $order->items()->first()->id)
            ->call('submit')->assertHasErrors('issue_details');

        $this->assertSame(0, WarrantyRegistration::count());
    }

    public function test_warranty_not_available_before_delivery(): void
    {
        $order = $this->placeOrder();

        Livewire::test(WarrantyForm::class)
            ->set('order_number', $order->order_number)->set('phone', '01712345678')
            ->call('findOrder')->assertHasErrors('order_number');
    }

    public function test_customer_with_nfc_order_can_create_profile_and_public_page_works(): void
    {
        $user = $this->customer();
        $this->placeOrder(['nfc_enabled' => true], $user);

        $this->actingAs($user)->post('/account/nfc', [
            'name' => 'Ashik Rahman', 'designation' => 'Founder', 'company' => 'LeatherX',
            'phone' => '01712345678', 'website' => 'https://example.com', 'linkedin' => 'https://linkedin.com/in/ashik',
        ])->assertRedirect(route('account.nfc.index'));

        $profile = NfcProfile::sole();
        $this->assertSame(NfcProfileStatus::Active, $profile->status);

        $this->get(route('nfc.show', $profile))->assertOk()->assertSee('Ashik Rahman')->assertSee('noindex');
        $this->assertSame(1, $profile->fresh()->views_count);

        $this->get(route('nfc.vcard', $profile))->assertOk()
            ->assertHeader('Content-Type', 'text/vcard; charset=utf-8')
            ->assertSee('FN:Ashik Rahman');

        $this->get(route('nfc.qr', $profile))->assertOk()->assertHeader('Content-Type', 'image/svg+xml');

        $profile->update(['status' => NfcProfileStatus::Suspended]);
        $this->get(route('nfc.show', $profile))->assertNotFound();
    }

    public function test_nfc_profile_requires_nfc_purchase_and_ownership(): void
    {
        $owner = $this->customer();
        $other = $this->customer(['email' => 'other@example.com']);

        $this->actingAs($other)->post('/account/nfc', ['name' => 'No Order'])->assertForbidden();

        $this->placeOrder(['nfc_enabled' => true], $owner);
        $this->actingAs($owner)->post('/account/nfc', ['name' => 'Owner']);
        $profile = NfcProfile::sole();

        $this->actingAs($other)->get(route('account.nfc.edit', $profile))->assertNotFound();
        $this->actingAs($other)->put(route('account.nfc.update', $profile), ['name' => 'Hacked'])->assertNotFound();
        $this->assertSame('Owner', $profile->fresh()->name);
    }

    public function test_corporate_inquiry(): void
    {
        $this->post('/corporate', [
            'company_name' => 'Acme Ltd', 'contact_person' => 'Mitu', 'email' => 'mitu@acme.test', 'phone' => '01712345678',
            'quantity' => 50, 'branding_required' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('corporate_inquiries', ['company_name' => 'Acme Ltd', 'branding_required' => true, 'status' => 'new']);
    }

    public function test_invoice_is_only_visible_to_owner_or_staff(): void
    {
        $owner = $this->customer();
        $order = $this->placeOrder([], $owner);
        auth()->logout();

        $this->get(route('orders.invoice', $order))->assertForbidden();
        $this->actingAs($this->customer(['email' => 'x@example.com']))->get(route('orders.invoice', $order))->assertForbidden();
        $this->actingAs($owner)->get(route('orders.invoice', $order))->assertOk()->assertSee($order->order_number);
        $this->actingAs($this->staff('support'))->get(route('orders.invoice', $order))->assertOk();
    }
}
