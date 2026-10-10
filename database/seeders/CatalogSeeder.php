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
 * Starter catalogue built around the product photography in public/images/products.
 * Names, materials and colours follow what each photo shows. Prices, stock and capacities
 * are starting values for the business to review in Admin → Catalog → Products.
 */
class CatalogSeeder extends Seeder
{
    /** Colour library — hex values are the swatches shown on product pages. */
    private const COLORS = [
        'Midnight Black' => '#1c1a19', 'Carbon Black' => '#232325', 'Graphite' => '#3d3e40', 'Espresso Brown' => '#4a2f22',
        'Chestnut Brown' => '#6e3b1f', 'Whiskey Brown' => '#8a4f24', 'Cognac Tan' => '#a0612b', 'Burgundy' => '#6b1f2a',
        'Forest Green' => '#2f4a3a', 'Navy Blue' => '#22304a', 'Slate Grey' => '#7b7f83', 'Ivory' => '#ece4d4',
    ];

    public function run(): void
    {
        foreach (array_keys(self::COLORS) as $i => $name) {
            ProductOption::updateOrCreate(['type' => OptionType::Color, 'name' => $name], ['value' => self::COLORS[$name], 'sort_order' => $i]);
        }
        foreach (['Full-grain cowhide', 'Crazy horse leather', 'Saffiano leather', 'Microfiber leather'] as $i => $name) {
            ProductOption::updateOrCreate(['type' => OptionType::Material, 'name' => $name], ['sort_order' => $i]);
        }

        $categories = [
            'smart-wallets' => ['LeatherX Tap Smart Wallets', 'NFC contact profile · RFID-blocking · Engravable', 'Leather wallets and card holders with a built-in NFC chip. Tap one on a phone and your LeatherX Tap contact profile opens in the browser — no app needed.', 'chestnut-leather-smart-bifold-wallet.png'],
            'classic-wallets' => ['Classic Wallets', 'Bifold wallets in full-grain and crazy-horse leather', 'Bifold wallets with RFID-blocking linings, in smooth full-grain and waxy crazy-horse leather.', 'bifold-wallets-four-colours.png'],
            'card-holders' => ['Card Holders', 'Slim holders for a lighter pocket', 'Slim card holders for the cards you use every day.', 'black-saffiano-card-holder.png'],
            'bags' => ['Bags', 'Totes, office bags, crossbody bags and backpacks', 'Structured bags for work, travel and every day.', 'ivory-black-tan-work-tote.png'],
            'passport-holders' => ['Passport & Travel', 'Passport holders and travel sets', 'Passport holders and coordinated travel sets with room for cards and boarding passes.', 'burgundy-passport-holder.png'],
            'key-holders' => ['Key Holders', 'Key loops and key organisers', 'Key loops and organisers that keep keys flat, quiet and away from your phone screen.', 'cognac-leather-key-loop.png'],
            'smart-accessories' => ['Smart Accessories', 'Trackers and tracker holders', 'Card trackers, tracker holders and smart key holders that help you keep track of your essentials.', 'slim-wallet-tracker-card.png'],
            'accessories' => ['Desk & Everyday Accessories', 'Valet trays, cable organisers and card tools', 'Leather pieces for the desk, the bedside table and the bag.', 'whiskey-leather-valet-tray.png'],
            'corporate-gifts' => ['Corporate Gift Sets', 'Boxed sets for teams and clients', 'Coordinated leather sets for employee and client gifting, with optional logo engraving.', 'heritage-leather-gift-set.png'],
        ];
        $categoryIds = [];
        $sort = 0;
        foreach ($categories as $slug => [$name, $tagline, $description, $image]) {
            $category = Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'tagline' => $tagline, 'description' => $description,
                'is_featured' => true, 'is_active' => true, 'sort_order' => $sort++,
            ]);
            $this->attachImage($category, 'image', 'products/'.$image, $name);
            $categoryIds[$slug] = $category->id;
        }

        foreach ($this->products() as $index => $p) {
            $sku = 'LX-'.strtoupper(Str::substr(Str::slug($p['category'], ''), 0, 3)).'-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $features = array_values(array_filter([
                $p['rfid'] ? 'RFID-blocking lining for contactless cards' : null,
                $p['nfc'] ? 'Built-in NFC chip that opens your LeatherX Tap contact profile on any NFC phone' : null,
                $p['cards'] ? 'Holds up to '.$p['cards'].' cards' : null,
                $p['engraving'] ? 'Can be personalised with laser engraving' : null,
                $p['warranty'] ? $p['warranty'].'-month LeatherX warranty' : null,
            ]));

            $product = Product::updateOrCreate(['slug' => Str::slug($p['name'])], [
                'category_id' => $categoryIds[$p['category']],
                'name' => $p['name'],
                'sku' => $sku,
                'short_description' => $p['summary'],
                'description' => '<p>'.e($p['summary']).'</p>'.($features ? '<ul>'.collect($features)->map(fn ($f) => '<li>'.e($f).'</li>')->implode('').'</ul>' : ''),
                'regular_price' => $p['price'],
                'sale_price' => $p['sale'] ?? null,
                'stock_quantity' => 0,
                'low_stock_threshold' => 5,
                'leather_type' => $p['leather'],
                'material' => $p['material'] ?? $p['leather'],
                'rfid_protection' => $p['rfid'],
                'nfc_enabled' => $p['nfc'],
                'warranty_months' => $p['warranty'],
                'card_capacity' => $p['cards'],
                'cash_capacity' => $p['cash'] ?? null,
                'engraving_available' => $p['engraving'],
                'engraving_price' => $p['engraving'] ? 300 : 0,
                'specifications' => $p['specs'] ?? null,
                'status' => ProductStatus::Active,
                'is_featured' => $p['featured'] ?? false,
                'sort_order' => $index,
                'seo_title' => $p['name'].($p['nfc'] ? ' — NFC Smart Wallet' : ''),
            ]);

            if ($product->wasRecentlyCreated) {
                foreach ($p['images'] as $i => $image) {
                    $this->attachImage($product, 'images', 'products/'.$image[0], $image[1], $image[2] ?? null, $image[3] ?? null);
                }

                foreach ($p['colors'] as $v => $colorName) {
                    $product->variants()->create([
                        'color_id' => ProductOption::where('type', OptionType::Color)->where('name', $colorName)->value('id'),
                        'sku' => $sku.'-'.strtoupper(Str::substr(Str::slug($colorName, ''), 0, 4)),
                        'stock_quantity' => [15, 10, 8, 6, 4][$v] ?? 5,
                        'sort_order' => $v,
                    ]);
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

    /** Copy an image from public/images into the media library (WebP sizes are generated). */
    private function attachImage($model, string $collection, string $path, string $alt, ?string $focus = null, ?string $color = null): void
    {
        $file = public_path('images/'.$path);

        if (app()->runningUnitTests() || ! is_file($file) || $model->getMedia($collection)->contains('file_name', basename($file))) {
            return;
        }

        $model->addMedia($file)
            ->preservingOriginal()
            ->usingName($alt)
            ->withCustomProperties(array_filter(['alt' => $alt, 'focus' => $focus, 'color' => $color]))
            ->toMediaCollection($collection);
    }

    /** @return array<int, array<string, mixed>> */
    private function products(): array
    {
        return [
            // LeatherX Tap smart wallets
            ['category' => 'smart-wallets', 'name' => 'Chestnut Smart Bifold Wallet', 'price' => 3990, 'sale' => 3490, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => true, 'warranty' => 24, 'cards' => 8, 'cash' => 12, 'engraving' => true, 'featured' => true,
                'colors' => ['Chestnut Brown', 'Midnight Black'],
                'summary' => 'A full-grain chestnut bifold with an RFID-blocking lining and a built-in NFC chip that shares your contact profile with one tap.',
                'images' => [['chestnut-leather-smart-bifold-wallet.png', 'Chestnut Smart Bifold Wallet in chestnut brown full-grain leather']]],
            ['category' => 'smart-wallets', 'name' => 'Tap Pop-up Card Wallet', 'price' => 3490, 'sale' => 2990, 'leather' => 'Microfiber leather',
                'material' => 'Microfiber leather with an aluminium card case', 'rfid' => true, 'nfc' => true, 'warranty' => 12, 'cards' => 6, 'cash' => 5, 'engraving' => true, 'featured' => true,
                'colors' => ['Cognac Tan', 'Midnight Black', 'Espresso Brown', 'Navy Blue', 'Slate Grey'],
                'summary' => 'A compact wallet with a pop-up aluminium card case, a cash and card fold, and an NFC chip for your LeatherX Tap profile.',
                'images' => [['pop-up-wallet-colours.png', 'Tap Pop-up Card Wallet in brown, black, tan and grey with cards fanned out'], ['pop-up-wallet-collection.png', 'Tap Pop-up Card Wallet collection in five colours on a wooden desk']]],
            ['category' => 'smart-wallets', 'name' => 'Tap Carbon Pop-up Card Holder', 'price' => 2790, 'leather' => 'Carbon-texture leather',
                'material' => 'Carbon-texture leather with an aluminium card case', 'rfid' => true, 'nfc' => true, 'warranty' => 12, 'cards' => 6, 'engraving' => false, 'featured' => true,
                'colors' => ['Carbon Black'],
                'summary' => 'A slim carbon-texture card holder: press the trigger and your cards slide out in a stack. The NFC chip shares your contact profile.',
                'images' => [['black-carbon-card-holder.png', 'Tap Carbon Pop-up Card Holder held in hand with cards pushed out'], ['slim-black-carbon-card-holder.png', 'Tap Carbon Pop-up Card Holder standing on a desk']]],

            // Classic wallets
            ['category' => 'classic-wallets', 'name' => 'Heritage Bifold with ID Window', 'price' => 2690, 'sale' => 2390, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 9, 'cash' => 15, 'engraving' => true, 'featured' => true,
                'colors' => ['Espresso Brown'],
                'summary' => 'A classic bifold in espresso full-grain leather with a clear ID window, nine card slots and a full-length note compartment.',
                'images' => [['heritage-wallet-id-window.png', 'Heritage Bifold with ID Window open on slate, showing card slots and ID window']]],
            ['category' => 'classic-wallets', 'name' => 'Crazy Horse Bifold Wallet', 'price' => 2490, 'leather' => 'Crazy horse leather',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 8, 'cash' => 12, 'engraving' => true, 'featured' => true,
                'colors' => ['Cognac Tan', 'Whiskey Brown'],
                'summary' => 'Waxy crazy-horse leather that darkens and marks with use, giving every wallet its own patina. ID window and eight card slots.',
                'images' => [['tan-crazy-horse-wallet.png', 'Crazy Horse Bifold Wallet in tan, open with cards and notes', null, 'Cognac Tan'], ['whiskey-crazy-horse-wallet.png', 'Crazy Horse Bifold Wallet in whiskey brown, closed, showing the leather grain', null, 'Whiskey Brown']]],
            ['category' => 'classic-wallets', 'name' => 'Classic Bifold Wallet', 'price' => 2290, 'sale' => 1990, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 8, 'cash' => 10, 'engraving' => true, 'featured' => true,
                'colors' => ['Midnight Black', 'Cognac Tan', 'Forest Green', 'Burgundy'],
                'summary' => 'Our simplest bifold in smooth full-grain leather, available in four colours.',
                'images' => [['bifold-wallets-four-colours.png', 'Classic Bifold Wallet in black, tan, forest green and burgundy']]],
            ['category' => 'classic-wallets', 'name' => 'Burgundy Wallet & Card Holder Set', 'price' => 3290, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 9, 'cash' => 12, 'engraving' => true,
                'colors' => ['Burgundy'],
                'summary' => 'A burnished burgundy bifold paired with a matching card holder — a ready-made gift.',
                'specs' => ['In the set' => 'Bifold wallet and card holder'],
                'images' => [['burgundy-leather-wallet-set.png', 'Burgundy bifold wallet open beside a matching closed wallet']]],

            // Card holders
            ['category' => 'card-holders', 'name' => 'Saffiano Card Holder', 'price' => 1190, 'sale' => 990, 'leather' => 'Saffiano leather',
                'rfid' => true, 'nfc' => false, 'warranty' => 12, 'cards' => 5, 'engraving' => true, 'featured' => true,
                'colors' => ['Midnight Black'],
                'summary' => 'A slim card holder in scratch-resistant saffiano leather with four slots and a centre pocket.',
                'images' => [['black-saffiano-card-holder.png', 'Saffiano Card Holder in black with cards in the slots']]],

            // Bags
            ['category' => 'bags', 'name' => 'Ivory Work Tote', 'price' => 6990, 'sale' => 6490, 'leather' => null,
                'material' => 'Ivory body with black and tan trim', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false, 'featured' => true,
                'colors' => ['Ivory'],
                'summary' => 'A structured work tote in ivory with black and tan trim, top handles and a detachable shoulder strap.',
                'images' => [['ivory-black-tan-work-tote.png', 'Ivory Work Tote with black and tan trim and a shoulder strap']]],
            ['category' => 'bags', 'name' => 'Ivory Office Bag Set', 'price' => 7990, 'leather' => null,
                'material' => 'Ivory body with black trim and tan handles', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Ivory'],
                'summary' => 'An office handbag with tan handles and a matching wristlet pouch.',
                'specs' => ['In the set' => 'Handbag and wristlet pouch'],
                'images' => [['ivory-black-office-bag-set.png', 'Ivory Office Bag Set: handbag with tan handles and a matching pouch']]],
            ['category' => 'bags', 'name' => 'Black Crossbody Bag', 'price' => 4990, 'leather' => 'Leather',
                'material' => 'Black leather with a tan flap', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Midnight Black'],
                'summary' => 'A compact crossbody bag in black leather with a tan flap and an adjustable strap.',
                'images' => [['black-leather-crossbody-bag.png', 'Black Crossbody Bag with tan flap and adjustable strap']]],
            ['category' => 'bags', 'name' => 'City Backpack', 'price' => 5990, 'leather' => null,
                'material' => 'Cream body with black and cognac trim', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Ivory'],
                'summary' => 'A city backpack in cream with black and cognac trim, a top carry handle and a zipped front pocket.',
                'images' => [['cream-black-cognac-city-backpack.png', 'City Backpack in cream with black and cognac trim']]],

            // Passport & travel
            ['category' => 'passport-holders', 'name' => 'Burgundy Passport Holder', 'price' => 1890, 'sale' => 1690, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 12, 'cards' => 4, 'engraving' => true, 'featured' => true,
                'colors' => ['Burgundy'],
                'summary' => 'A burnished burgundy passport holder with a flap pocket for boarding passes and slots for cards.',
                'images' => [['burgundy-passport-holder.png', 'Burgundy Passport Holder with burnished edges']]],
            ['category' => 'passport-holders', 'name' => 'Leather Travel Set', 'price' => 3990, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 12, 'cards' => 4, 'engraving' => true,
                'colors' => ['Chestnut Brown'],
                'summary' => 'A coordinated travel set: passport holder, luggage tag and key holder in chestnut leather.',
                'specs' => ['In the set' => 'Passport holder, luggage tag, key holder'],
                'images' => [['leather-travel-set.png', 'Leather Travel Set laid out on a desk: passport holder, luggage tag and key holder']]],

            // Key holders
            ['category' => 'key-holders', 'name' => 'Cognac Key Loop', 'price' => 690, 'leather' => 'Full-grain cowhide',
                'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => true,
                'colors' => ['Cognac Tan'],
                'summary' => 'A cognac leather loop with a brass snap and split ring — easy to clip onto a bag strap or belt loop.',
                'images' => [['cognac-leather-key-loop.png', 'Cognac Key Loop with brass snap and key']]],
            ['category' => 'key-holders', 'name' => 'Key Organiser', 'price' => 1290, 'leather' => null,
                'material' => 'Graphite finish with stainless steel posts', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Graphite'],
                'summary' => 'Stacks up to six keys into one flat bundle that does not scratch your phone, with a ring for a car key.',
                'images' => [['graphite-key-organiser.png', 'Key Organiser in graphite holding several keys beside a car key']]],

            // Smart accessories
            ['category' => 'smart-accessories', 'name' => 'Slim Wallet Tracker Card', 'price' => 2490, 'leather' => null,
                'material' => 'Card-thin tracker', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false, 'featured' => true,
                'colors' => ['Midnight Black'],
                'summary' => 'A card-thin tracker that sits in a wallet card slot and shows your wallet’s location in a phone app. Ask our team to confirm compatibility with your phone before ordering.',
                'images' => [['slim-wallet-tracker-card.png', 'Slim Wallet Tracker Card beside a phone showing its location on a map']]],
            ['category' => 'smart-accessories', 'name' => 'Leather Tracker Holder', 'price' => 790, 'leather' => 'Full-grain cowhide',
                'material' => 'Leather with a brass key ring', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => true,
                'colors' => ['Midnight Black'],
                'summary' => 'A black leather case for a round, coin-sized tracker, with a brass ring for keys or bags. Tracker not included.',
                'images' => [['black-leather-tracker-holder-brass-ring.png', 'Black Leather Tracker Holder with a brass ring']]],
            ['category' => 'smart-accessories', 'name' => 'Matte Black Smart Key Holder', 'price' => 2290, 'leather' => null,
                'material' => 'Matte black finish with a D-ring', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Midnight Black'],
                'summary' => 'A slim matte black key holder with a D-ring that keeps keys stacked in one tidy bar.',
                'images' => [['matte-black-smart-key-holder.png', 'Matte Black Smart Key Holder with D-ring']]],

            // Desk & everyday accessories
            ['category' => 'accessories', 'name' => 'Whiskey Leather Valet Tray', 'price' => 1490, 'leather' => 'Full-grain cowhide',
                'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => true,
                'colors' => ['Whiskey Brown'],
                'summary' => 'A snap-cornered leather tray for your watch, wallet, keys and earbuds at the end of the day.',
                'images' => [['whiskey-leather-valet-tray.png', 'Whiskey Leather Valet Tray holding a watch, wallet and keys']]],
            ['category' => 'accessories', 'name' => 'Leather Cable Organiser Set', 'price' => 990, 'leather' => 'Full-grain cowhide',
                'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => false,
                'colors' => ['Midnight Black', 'Cognac Tan', 'Burgundy'],
                'summary' => 'Snap-fastened leather straps that keep charging cables and earphone wires coiled in your bag.',
                'specs' => ['In the set' => 'Four cable straps'],
                'images' => [['leather-cable-organiser-set.png', 'Leather Cable Organiser Set holding coiled charging cables']]],
            ['category' => 'accessories', 'name' => 'Multitool Card with Leather Sleeve', 'price' => 890, 'leather' => 'Full-grain cowhide',
                'material' => 'Stainless steel card with a leather sleeve', 'rfid' => false, 'nfc' => false, 'warranty' => 12, 'cards' => null, 'engraving' => true,
                'colors' => ['Espresso Brown'],
                'summary' => 'A credit-card-sized stainless steel multitool that lives in your wallet, with a leather sleeve to protect cards and pockets.',
                'images' => [['multitool-card-leather-sleeve.png', 'Stainless steel multitool card beside its brown leather sleeve']]],

            // Corporate gift sets
            ['category' => 'corporate-gifts', 'name' => 'Heritage Gift Set', 'price' => 4990, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 9, 'engraving' => true, 'featured' => true,
                'colors' => ['Burgundy'],
                'summary' => 'A burgundy bifold wallet, card holder and key loop, presented together as one gift.',
                'specs' => ['In the set' => 'Bifold wallet, card holder, key loop'],
                'images' => [['heritage-leather-gift-set.png', 'Heritage Gift Set: burgundy wallet, card holder and key loop']]],
            ['category' => 'corporate-gifts', 'name' => 'Executive Gift Set', 'price' => 14990, 'leather' => 'Full-grain cowhide',
                'rfid' => true, 'nfc' => false, 'warranty' => 24, 'cards' => 8, 'engraving' => true,
                'colors' => ['Midnight Black'],
                'summary' => 'A black leather briefcase with a matching wallet and card holder, for senior colleagues and clients.',
                'specs' => ['In the set' => 'Briefcase, bifold wallet, card holder'],
                'images' => [['../banners/executive-gift-set.png', 'Executive Gift Set: black leather briefcase, wallet and card holder on an office desk', '58% 60%']]],
        ];
    }
}
