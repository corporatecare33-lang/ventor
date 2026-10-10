<?php

namespace Tests\Feature;

use App\Models\Review;
use Database\Seeders\ContentSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
        $this->seed([SettingsSeeder::class, ContentSeeder::class]);
    }

    public function test_homepage_loads_with_featured_series(): void
    {
        $product = $this->product(['is_featured' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($product->category->name)
            ->assertSee('application/ld+json', false);
    }

    public function test_product_listing_loads(): void
    {
        $visible = $this->product();
        $draft = $this->product(['status' => 'draft']);

        $this->get('/shop')->assertOk()->assertSee($visible->name)->assertDontSee($draft->name);
    }

    public function test_shop_search_filters_products(): void
    {
        $match = $this->product(['name' => 'Saddle Brown Trifold']);
        $other = $this->product(['name' => 'Executive Briefcase']);

        $this->get('/shop?q=Trifold')->assertOk()->assertSee($match->name)->assertDontSee($other->name);
    }

    public function test_category_page_loads(): void
    {
        $product = $this->product();

        $this->get(route('categories.show', $product->category))->assertOk()->assertSee($product->name);
    }

    public function test_product_details_page_loads_with_schema_and_reviews(): void
    {
        $product = $this->product(['sale_price' => 1500]);
        Review::create(['product_id' => $product->id, 'name' => 'Rahim', 'rating' => 5, 'body' => 'Beautiful stitching and quality.', 'is_approved' => true]);
        Review::create(['product_id' => $product->id, 'name' => 'Hidden', 'rating' => 1, 'body' => 'Pending moderation review.', 'is_approved' => false]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('৳1,500')
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('Beautiful stitching')
            ->assertDontSee('Pending moderation review');

        $this->assertSame(1, $product->fresh()->reviews_count);
    }

    public function test_draft_product_is_hidden_from_customers(): void
    {
        $product = $this->product(['status' => 'draft']);

        $this->get(route('products.show', $product))->assertNotFound();
        $this->actingAs($this->staff())->get(route('products.show', $product))->assertOk();
    }

    public function test_content_pages_load(): void
    {
        foreach (['/about', '/contact', '/faq', '/blog', '/reviews', '/leatherx-tap', '/engraving', '/warranty', '/corporate',
            '/return-policy', '/privacy-policy', '/terms-and-conditions', '/delivery-policy', '/track-order', '/cart'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_sitemap_and_robots(): void
    {
        $product = $this->product();

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', $product), false);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin')->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_contact_form_stores_message_and_blocks_honeypot(): void
    {
        $this->post('/contact', ['name' => 'Karim', 'email' => 'k@example.com', 'message' => 'Do you ship to Sylhet?'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('contact_messages', ['email' => 'k@example.com']);

        $this->post('/contact', ['name' => 'Bot', 'email' => 'b@example.com', 'message' => 'Spam spam spam', 'website' => 'x'])
            ->assertSessionHasErrors('website');
    }
}
