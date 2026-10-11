<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Livewire\AssistantChat;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\Setting;
use App\Services\Assistant\Conversation;
use App\Services\Assistant\Query;
use App\Services\Assistant\Reply;
use App\Services\Assistant\ShopAssistant;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    private function ask(string $message, ?Conversation $conversation = null): Reply
    {
        return app(ShopAssistant::class)->reply($message, $conversation ?? Conversation::load());
    }

    private function cartItems()
    {
        return Cart::latest('id')->first()?->items()->get() ?? collect();
    }

    public function test_reads_prices_order_numbers_and_phones_in_english_banglish_and_bangla(): void
    {
        $this->assertSame([null, 3000], [Query::parse('wallets under 3k')->minPrice, Query::parse('wallets under 3k')->maxPrice]);
        $this->assertSame(2500, Query::parse('৳2,500 er moddhe wallet')->maxPrice);
        $this->assertSame(3000, Query::parse('৩০০০ টাকার মধ্যে ওয়ালেট')->maxPrice);
        $this->assertSame([1000, 2000], [Query::parse('between 1000 and 2000')->minPrice, Query::parse('between 1000 and 2000')->maxPrice]);
        $this->assertSame(5000, Query::parse('bags above 5000 tk')->minPrice);

        $query = Query::parse('Where is LX261011AB12C? my phone is 017-1234 5678');
        $this->assertSame('LX261011AB12C', $query->orderNumber);
        $this->assertSame('01712345678', $query->phone);

        $this->assertContains('wallet', Query::parse('ওয়ালেট দেখান')->terms);
        $this->assertSame(1, Query::parse('add the second one')->ordinal);
        $this->assertSame(2, Query::parse('add 2 pcs')->quantity);
        $this->assertSame(['nfc'], Query::parse('tap wallets')->features);
    }

    public function test_finds_products_by_feature_and_price(): void
    {
        $match = $this->product(['name' => 'Tap Card Wallet', 'nfc_enabled' => true, 'regular_price' => 2500]);
        $this->product(['name' => 'Tap Bifold Wallet', 'nfc_enabled' => true, 'regular_price' => 4500]);
        $this->product(['name' => 'Classic Bifold Wallet', 'regular_price' => 1800]);
        $this->product(['name' => 'Hidden Tap Wallet', 'nfc_enabled' => true, 'regular_price' => 2000])->update(['status' => 'draft']);

        $reply = $this->ask('Do you have NFC wallets under 3000?');

        $this->assertSame([$match->id], $reply->products);
        $this->assertStringContainsString('NFC wallet under ৳3,000', $reply->text);
    }

    public function test_relaxes_the_price_when_nothing_matches_and_says_so(): void
    {
        $bag = $this->product(['name' => 'City Backpack Bag', 'regular_price' => 5990]);

        $reply = $this->ask('bag under 1000');

        $this->assertSame([$bag->id], $reply->products);
        $this->assertStringContainsString('at other prices', $reply->text);
    }

    public function test_shows_a_collection_by_category_name(): void
    {
        $category = Category::factory()->create(['name' => 'Smart Accessories', 'slug' => 'smart-accessories', 'description' => 'Trackers and key holders.']);
        $other = Category::factory()->create(['name' => 'Accessories', 'slug' => 'accessories']);
        $tracker = $this->product(['name' => 'Slim Tracker Card', 'category_id' => $category->id]);
        $this->product(['name' => 'Valet Tray', 'category_id' => $other->id]);

        $reply = $this->ask('Show me the Smart Accessories collection.');

        $this->assertSame([$tracker->id], $reply->products);
        $this->assertStringContainsString('**Smart Accessories** collection', $reply->text);
        $this->assertSame(route('categories.show', 'smart-accessories'), collect($reply->actions)->firstWhere('type', 'link')['url']);
    }

    public function test_understands_bangla(): void
    {
        $wallet = $this->product(['name' => 'Classic Bifold Wallet']);
        $bags = Category::factory()->create(['name' => 'Bags', 'slug' => 'bags']);
        $cheap = $this->product(['name' => 'Crossbody Bag', 'category_id' => $bags->id, 'regular_price' => 3000]);
        $this->product(['name' => 'Office Tote', 'category_id' => $bags->id, 'regular_price' => 7000]);

        $this->assertContains($wallet->id, $this->ask('ওয়ালেট দেখান')->products);
        $this->assertSame($cheap->id, $this->ask('কম দামে ব্যাগ')->products[0], 'cheapest bag first; unknown words do not empty the list');
    }

    public function test_prefers_names_over_passing_mentions_and_reports_missing_colours(): void
    {
        $wallet = $this->product(['name' => 'Classic Bifold Wallet']);
        $this->product(['name' => 'Valet Tray', 'short_description' => 'A place for your wallet and keys.']);

        $this->assertSame([$wallet->id], $this->ask('wallets')->products);

        $red = $this->ask('red wallet');
        $this->assertSame([$wallet->id], $red->products);
        $this->assertStringContainsString("couldn't find red wallets", $red->text);
    }

    public function test_greeting_with_a_request_searches_instead_of_greeting(): void
    {
        $holders = Category::factory()->create(['name' => 'Card Holders', 'slug' => 'card-holders']);
        $holder = $this->product(['name' => 'Slim Card Holder', 'category_id' => $holders->id]);

        $this->assertSame([], $this->ask('assalamualaikum')->products);
        $this->assertSame([$holder->id], $this->ask('hello, show me card holders')->products);
    }

    public function test_product_questions_about_warranty_and_material(): void
    {
        $this->product(['name' => 'Classic Bifold Wallet', 'warranty_months' => 24]);

        $this->assertStringContainsString('24-month warranty', $this->ask('warranty of the classic bifold wallet')->text);
        $this->assertStringContainsString('**Full-grain cowhide**', $this->ask('is the leather genuine?')->text);
    }

    public function test_describes_a_named_product_and_adds_it_when_asked(): void
    {
        $holders = Category::factory()->create(['name' => 'Card Holders', 'slug' => 'card-holders']);
        $saffiano = $this->product(['name' => 'Saffiano Card Holder', 'category_id' => $holders->id, 'regular_price' => 1190, 'sale_price' => 990, 'card_capacity' => 6]);
        $this->product(['name' => 'Carbon Card Holder', 'category_id' => $holders->id]);
        $conversation = Conversation::load();

        $details = $this->ask('tell me about the saffiano card holder', $conversation);
        $this->assertSame([$saffiano->id], $details->products);
        $this->assertStringContainsString('৳990', $details->text);
        $this->assertStringContainsString('holds up to 6 cards', $details->text);

        $added = $this->ask('add it to cart', $conversation);
        $this->assertTrue($added->cartUpdated);
        $this->assertSame($saffiano->id, $this->cartItems()->first()->product_id);
    }

    public function test_adds_the_second_product_of_the_last_list(): void
    {
        $first = $this->product(['name' => 'Alpha Wallet', 'is_featured' => true]);
        $second = $this->product(['name' => 'Beta Wallet']);
        $conversation = Conversation::load();

        $list = $this->ask('wallets', $conversation);
        $this->assertSame([$first->id, $second->id], $list->products);

        $this->ask('add the second one', $conversation);
        $this->assertSame($second->id, $this->cartItems()->first()->product_id);
    }

    public function test_asks_for_an_option_then_adds_the_chosen_colour(): void
    {
        $product = $this->product(['name' => 'Tap Pop-up Wallet']);
        $black = ProductOption::create(['type' => 'color', 'name' => 'Midnight Black', 'value' => '#000']);
        $tan = ProductOption::create(['type' => 'color', 'name' => 'Cognac Tan', 'value' => '#a0612b']);
        $product->variants()->create(['color_id' => $black->id, 'sku' => 'V-BLK', 'stock_quantity' => 3]);
        $tanVariant = $product->variants()->create(['color_id' => $tan->id, 'sku' => 'V-TAN', 'stock_quantity' => 4]);
        $conversation = Conversation::load();

        $question = app(ShopAssistant::class)->addToCart($product->id, null, 1, $conversation);
        $this->assertFalse($question->cartUpdated);
        $this->assertCount(2, $question->actions);

        $added = $this->ask('tan please', $conversation);
        $this->assertTrue($added->cartUpdated);
        $this->assertSame($tanVariant->id, $this->cartItems()->first()->product_variant_id);
    }

    public function test_chat_component_adds_to_cart_and_updates_the_counter(): void
    {
        $product = $this->product(['name' => 'Heritage Bifold', 'stock_quantity' => 5]);

        Livewire::test(AssistantChat::class)
            ->call('add', $product->id, null, 2, 'Add Heritage Bifold to cart')
            ->assertDispatched('cart-updated')
            ->assertSee('Added 2 × ')
            ->assertSee('Add Heritage Bifold to cart');

        $this->assertSame(2, (int) $this->cartItems()->sum('quantity'));
    }

    public function test_chat_component_cannot_add_hidden_products(): void
    {
        $draft = Product::factory()->draft()->create();

        Livewire::test(AssistantChat::class)
            ->call('add', $draft->id, null, 1, 'Add it')
            ->assertNotDispatched('cart-updated');

        $this->assertCount(0, $this->cartItems());
    }

    public function test_cart_summary_and_checkout(): void
    {
        $product = $this->product(['name' => 'Classic Bifold Wallet', 'regular_price' => 2000]);
        app(CartService::class)->add($product, null, 2);

        $summary = $this->ask("what's in my cart?");
        $this->assertStringContainsString('2 × Classic Bifold Wallet', $summary->text);
        $this->assertStringContainsString('৳4,000', $summary->text);

        $checkout = $this->ask('checkout');
        $this->assertSame(route('checkout'), $checkout->actions[0]['url']);
    }

    public function test_tracks_an_order_only_with_the_matching_phone(): void
    {
        Order::create([
            'order_number' => 'LX261011AB12C', 'customer_name' => 'Rahim', 'phone' => '01712345678', 'address' => 'House 1',
            'city' => 'Dhaka', 'subtotal' => 2000, 'total' => 2070, 'payment_method' => 'cod',
            'status' => OrderStatus::Shipped, 'courier_name' => 'Pathao', 'tracking_number' => 'PT-99',
        ]);
        $conversation = Conversation::load();

        $askPhone = $this->ask('track LX261011AB12C', $conversation);
        $this->assertStringContainsString('phone number', $askPhone->text);
        $this->assertStringNotContainsString('Shipped', $askPhone->text);

        $wrong = $this->ask('01811111111', $conversation);
        $this->assertStringContainsString("couldn't find", $wrong->text);

        $found = $this->ask('my order LX261011AB12C, phone 01712345678', $conversation);
        $this->assertStringContainsString('**Shipped**', $found->text);
        $this->assertStringContainsString('PT-99', $found->text);
    }

    public function test_answers_store_questions_from_real_data(): void
    {
        $delivery = $this->ask('delivery charge koto?');
        $this->assertStringContainsString('Inside Dhaka', $delivery->text);
        $this->assertStringContainsString('৳70', $delivery->text);

        $payment = $this->ask('Can I pay with bkash?');
        $this->assertStringContainsString('Cash on Delivery', $payment->text);
        $this->assertStringNotContainsString('bKash', $payment->text, 'inactive methods are not offered');

        $contact = $this->ask('how can I contact you?');
        $this->assertStringContainsString('contact page', $contact->text, 'no contact details are invented when none are set');
    }

    public function test_falls_back_without_inventing_an_answer(): void
    {
        $reply = $this->ask('what is the meaning of life');

        $this->assertSame([], $reply->products);
        $this->assertStringContainsString("don't have an answer", $reply->text);
    }

    public function test_widget_is_on_the_storefront_and_can_be_hidden(): void
    {
        $this->get(route('home'))->assertOk()->assertSee('Ask the LeatherX assistant');

        Setting::setMany(['assistant_hidden' => true]);

        $this->get(route('home'))->assertOk()->assertDontSee('Ask the LeatherX assistant');
    }

    public function test_conversation_renders_after_opening(): void
    {
        Livewire::test(AssistantChat::class)
            ->assertDontSee('find products, add them to your cart')
            ->call('load')
            ->assertSee('find products, add them to your cart')
            ->call('send', 'hello')
            ->assertSee('hello');
    }

    public function test_conversation_renders_product_cards_and_options(): void
    {
        $product = $this->product(['name' => 'Tap Pop-up Wallet', 'regular_price' => 2990]);
        $black = ProductOption::create(['type' => 'color', 'name' => 'Midnight Black', 'value' => '#000']);
        $tan = ProductOption::create(['type' => 'color', 'name' => 'Cognac Tan', 'value' => '#a0612b']);
        $product->variants()->create(['color_id' => $black->id, 'sku' => 'V-BLK', 'stock_quantity' => 3]);
        $product->variants()->create(['color_id' => $tan->id, 'sku' => 'V-TAN', 'stock_quantity' => 9]);

        Livewire::test(AssistantChat::class)
            ->call('send', 'wallets')
            ->assertSee('Tap Pop-up Wallet')
            ->assertSee('৳2,990')
            ->assertSee('Add Tap Pop-up Wallet to cart')
            ->call('send', 'add it')
            ->assertSee('Which option would you like')
            ->assertSee('Midnight Black (only 3 left)')
            ->assertSee('Cognac Tan');
    }
}
