<?php

namespace Database\Seeders;

use App\Enums\OptionType;
use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample catalogue (no images — upload real photos from the admin panel).
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            'Midnight Black' => '#1c1a19', 'Espresso Brown' => '#4a2f22', 'Cognac Tan' => '#a0612b',
            'Saddle Brown' => '#7b4a26', 'Forest Green' => '#2f4a3a', 'Navy Blue' => '#22304a', 'Burgundy' => '#6b1f2a',
        ];
        $materials = ['Full-grain Cowhide', 'Crazy Horse Leather', 'Nappa Leather', 'Saffiano Leather', 'Vegetable-tanned Leather'];
        $sizes = ['Standard', 'Slim', '14 inch', '16 inch'];

        $sort = 0;
        foreach ($colors as $name => $hex) {
            ProductOption::updateOrCreate(['type' => OptionType::Color, 'name' => $name], ['value' => $hex, 'sort_order' => $sort++]);
        }
        foreach ($materials as $i => $name) {
            ProductOption::updateOrCreate(['type' => OptionType::Material, 'name' => $name], ['sort_order' => $i]);
        }
        foreach ($sizes as $i => $name) {
            ProductOption::updateOrCreate(['type' => OptionType::Size, 'name' => $name], ['sort_order' => $i]);
        }
        $color = fn (string $name) => ProductOption::where('type', OptionType::Color)->where('name', $name)->value('id');

        $categories = [
            'smart-wallets' => ['LeatherX Tap Smart Wallets', 'NFC contact profile · RFID-blocking · Engravable', 'Leather wallets and cards with a built-in NFC chip. Tap one on a phone and your LeatherX Tap contact profile opens in the browser — no app needed.'],
            'classic-wallets' => ['Atelier Classic Wallets', 'Bifold, trifold and zip wallets · RFID-blocking', 'Bifold, trifold and long zip wallets in full-grain, crazy-horse and vegetable-tanned leather.'],
            'card-holders' => ['Card Holders', 'Slim holders for four to eight cards', 'Minimal card sleeves and magnetic card holders for a lighter pocket.'],
            'bags' => ['Bags', 'Briefcases, backpacks, totes and crossbody bags', 'Leather bags for the office, for travel and for every day.'],
            'passport-holders' => ['Passport & Travel', 'Passport covers and travel organisers', 'Passport holders and travel organisers with room for cards, boarding passes and cash.'],
            'key-holders' => ['Key Holders', 'Compact leather key organisers', 'Key organisers that keep keys quiet, flat and away from your phone screen.'],
            'corporate-gifts' => ['Corporate Gift Sets', 'Boxed sets for teams and clients', 'Boxed leather sets for employee and client gifting, with optional logo engraving.'],
        ];
        $categoryIds = [];
        $i = 0;
        foreach ($categories as $slug => [$name, $tagline, $description]) {
            $categoryIds[$slug] = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'tagline' => $tagline, 'description' => $description,
                'is_featured' => true, 'is_active' => true, 'sort_order' => $i++,
            ])->id;
        }

        $products = [
            // [category, name, price, sale, specs[leather, rfid, nfc, warranty, cards, cash, engraving], colors, featured]
            ['smart-wallets', 'Tap Pro Bifold NFC Wallet', 3990, 3490, ['Full-grain cowhide', true, true, 24, 10, 15, true], ['Midnight Black', 'Espresso Brown', 'Cognac Tan'], true],
            ['smart-wallets', 'Tap Slim Card Wallet', 2990, 2690, ['Nappa leather', true, true, 24, 8, 5, true], ['Midnight Black', 'Navy Blue'], true],
            ['smart-wallets', 'Tap Pop-up Aluminium Wallet', 4490, null, ['Saffiano leather + aluminium cage', true, true, 24, 12, 6, true], ['Midnight Black', 'Forest Green', 'Burgundy'], true],
            ['smart-wallets', 'Tap Metal Business Card', 1990, 1790, ['Brushed stainless steel', false, true, 12, null, null, true], [], false],
            ['classic-wallets', 'Atelier Classic Bifold', 2490, 2190, ['Full-grain cowhide', true, false, 24, 8, 12, true], ['Espresso Brown', 'Cognac Tan', 'Midnight Black'], true],
            ['classic-wallets', 'Atelier Crazy Horse Trifold', 2790, null, ['Crazy horse leather', true, false, 24, 12, 15, true], ['Saddle Brown', 'Espresso Brown'], false],
            ['classic-wallets', 'Atelier Long Wallet with Zip', 3290, 2990, ['Vegetable-tanned leather', true, false, 24, 14, 20, true], ['Midnight Black', 'Burgundy'], false],
            ['card-holders', 'Minimal Card Sleeve', 1190, 990, ['Nappa leather', true, false, 12, 6, 3, true], ['Midnight Black', 'Cognac Tan', 'Forest Green', 'Navy Blue'], true],
            ['card-holders', 'Magnetic Card Holder', 1490, null, ['Full-grain cowhide', true, false, 12, 8, 4, true], ['Espresso Brown', 'Midnight Black'], false],
            ['bags', 'Executive Laptop Briefcase', 12990, 11490, ['Full-grain cowhide', false, false, 24, null, null, true], ['Espresso Brown', 'Midnight Black'], true],
            ['bags', 'Commuter Leather Backpack', 9990, null, ['Crazy horse leather', false, false, 24, null, null, false], ['Saddle Brown', 'Midnight Black'], false],
            ['bags', 'Signature Tote for Women', 7490, 6490, ['Saffiano leather', false, false, 12, null, null, true], ['Burgundy', 'Cognac Tan', 'Midnight Black'], true],
            ['bags', 'Everyday Crossbody Bag', 5490, null, ['Nappa leather', false, false, 12, null, null, false], ['Cognac Tan', 'Navy Blue'], false],
            ['passport-holders', 'Voyager Passport Holder', 1890, 1690, ['Full-grain cowhide', true, false, 12, 4, 6, true], ['Espresso Brown', 'Navy Blue', 'Midnight Black'], false],
            ['passport-holders', 'Voyager Travel Organiser', 2690, null, ['Vegetable-tanned leather', true, false, 12, 8, 10, true], ['Saddle Brown'], false],
            ['key-holders', 'Compact Key Organiser', 890, 790, ['Full-grain cowhide', false, false, 12, null, null, true], ['Midnight Black', 'Cognac Tan'], false],
            ['corporate-gifts', 'Executive Gift Box (Wallet + Card Holder + Key Holder)', 4990, null, ['Full-grain cowhide', true, false, 24, null, null, true], [], true],
        ];

        foreach ($products as $index => [$cat, $name, $price, $sale, $spec, $productColors, $featured]) {
            [$leather, $rfid, $nfc, $warranty, $cards, $cash, $engraving] = $spec;
            $slug = Str::slug($name);
            $sku = 'LX-'.strtoupper(Str::substr(Str::slug($cat, ''), 0, 3)).'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);

            $features = array_filter([
                $rfid ? 'RFID-blocking lining protects contactless cards' : null,
                $nfc ? 'Built-in NFC chip opens your LeatherX Tap digital profile — share contacts with one tap' : null,
                $cards ? "Holds up to {$cards} cards" : null,
                $engraving ? 'Personalise with laser engraving (name, initials or logo)' : null,
                $warranty ? $warranty.'-month LeatherX warranty' : null,
            ]);

            $product = Product::updateOrCreate(['slug' => $slug], [
                'category_id' => $categoryIds[$cat],
                'name' => $name,
                'sku' => $sku,
                'short_description' => ucfirst(strtolower($leather)).($rfid ? ' with an RFID-blocking lining' : '').($nfc ? ' and a built-in NFC chip that opens your LeatherX Tap contact profile' : '').'.',
                'description' => '<p>'.e($name).' in '.e(strtolower($leather)).'.</p><ul>'.collect($features)->map(fn ($f) => '<li>'.e($f).'</li>')->implode('').'</ul>',
                'regular_price' => $price,
                'sale_price' => $sale,
                'stock_quantity' => 0,
                'low_stock_threshold' => 5,
                'leather_type' => $leather,
                'material' => $leather,
                'rfid_protection' => $rfid,
                'nfc_enabled' => $nfc,
                'warranty_months' => $warranty,
                'card_capacity' => $cards,
                'cash_capacity' => $cash,
                'engraving_available' => $engraving,
                'engraving_price' => $engraving ? 300 : 0,
                'specifications' => null,
                'status' => ProductStatus::Active,
                'is_featured' => $featured,
                'sort_order' => $index,
                'seo_title' => "{$name} — ".($nfc ? 'NFC Smart Wallet' : 'Genuine Leather').' | LeatherX',
            ]);

            if ($product->wasRecentlyCreated) {
                if ($productColors) {
                    foreach ($productColors as $v => $colorName) {
                        $product->variants()->create([
                            'color_id' => $color($colorName),
                            'sku' => $sku.'-'.strtoupper(Str::substr(Str::slug($colorName, ''), 0, 4)),
                            'stock_quantity' => [18, 12, 7, 3][$v] ?? 10,
                            'sort_order' => $v,
                        ]);
                    }
                } else {
                    $product->update(['stock_quantity' => 25]);
                }

                $product->refresh();
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => StockMovementType::Restock,
                    'quantity' => $product->stock_quantity,
                    'balance_after' => $product->stock_quantity,
                    'note' => 'Opening stock (seeder)',
                ]);
            }
        }
    }
}
