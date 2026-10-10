<?php

namespace Database\Seeders;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Seeder;

/**
 * Starting copy for the storefront. Every text here is editable in the admin panel.
 * Policies are sensible drafts and must be reviewed by the business before launch.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $hero = Banner::firstOrCreate(['position' => BannerPosition::Hero], [
            'eyebrow' => 'Wallets · Card holders · Bags · NFC smart wallets',
            'title' => 'Leather goods for everyday carry, made smarter with LeatherX Tap.',
            'subtitle' => 'Leather wallets, card holders and bags with RFID-blocking linings — and LeatherX Tap wallets that open your digital contact card on any phone. Engrave a name or logo, and pay cash on delivery anywhere in Bangladesh.',
            'button_text' => 'Shop the collection',
            'button_link' => '/shop',
            'secondary_button_text' => 'How LeatherX Tap works',
            'secondary_button_link' => '/leatherx-tap',
            'is_active' => true,
        ]);
        $this->attachImage($hero, 'image', 'banners/smart-wallet-warm-light.png', 'Chestnut leather bifold wallet resting on dark leather in warm light', '72% 50%');
        $this->attachImage($hero, 'mobile_image', 'products/chestnut-leather-smart-bifold-wallet.png', 'Chestnut Smart Bifold Wallet on slate');

        $sections = [
            'categories' => ['Shop by category', 'Wallets, card holders, bags and travel pieces — each with the colours and prices you see at checkout.', []],
            'featured' => ['Featured pieces', 'A short edit from across the collection to start with.', []],
            'tap_intro' => ['Your contact card, built into your wallet', 'LeatherX Tap wallets carry a small passive NFC chip. Hold the wallet near a phone and your contact profile opens in the browser — no app and no battery.', [
                'items' => [
                    ['title' => 'Order a LeatherX Tap wallet or card', 'text' => 'Every Tap product includes a personal profile at no extra cost.'],
                    ['title' => 'Set up your profile in your account', 'text' => 'Add your phone, email, company, website and social links. Edit them whenever you like.'],
                    ['title' => 'Tap to share', 'text' => 'The other person sees your profile and can save you as a contact. A QR code works for phones without NFC.'],
                ],
                'button_text' => 'How LeatherX Tap works',
                'button_link' => '/leatherx-tap',
            ]],
            'comparison' => ['Compare our most popular wallets', 'Capacity, protection and warranty side by side.', []],
            'engraving' => ['Laser engraving, checked before it is cut', 'Add a name, initials, a short line or your company logo to any piece marked “Engravable”.', [
                'items' => [
                    ['title' => 'Choose an engravable piece', 'text' => 'Tick “Add laser engraving” on the product page.'],
                    ['title' => 'Enter your text and style', 'text' => 'Pick one of four styles and where it should sit. You see a preview before adding it to the cart.'],
                    ['title' => 'We confirm the details', 'text' => 'Our team checks spelling and placement with you before engraving — personalised items cannot be undone.'],
                    ['title' => 'Delivered ready to give', 'text' => 'Engraved orders need two to three extra working days.'],
                ],
                'button_text' => 'Personalise a product',
                'button_link' => '/engraving',
            ]],
            'corporate' => ['Corporate gifts your team will actually use', 'Leather wallets, card holders and gift sets for employees, clients and events — engraved with your logo and invoiced as one order.', [
                'items' => [
                    ['title' => 'Logo or name engraving', 'text' => 'per piece'],
                    ['title' => 'Mixed products in one order', 'text' => 'wallets, card holders, key holders'],
                    ['title' => 'Delivery to one or several addresses', 'text' => null],
                    ['title' => 'A written quotation before you commit', 'text' => null],
                ],
                'button_text' => 'Request a quotation',
                'button_link' => '/corporate',
            ]],
            'reviews' => ['What customers say', 'Reviews are published after moderation and marked when the reviewer bought the product.', []],
            'faq' => ['Questions before you order', null, []],
        ];
        $images = [
            'tap_intro' => ['banners/nfc-wallet-smartphone.png', 'LeatherX Tap wallet next to a smartphone receiving the contact profile', '45% 50%'],
            'engraving' => ['banners/laser-engraving-wallet.png', 'A laser engraving a monogram onto a tan leather wallet', '45% 60%'],
            'corporate' => ['banners/executive-gift-set.png', 'Executive gift set of a black leather briefcase, wallet and card holder on an office desk', '58% 60%'],
        ];
        $i = 0;
        foreach ($sections as $key => [$title, $subtitle, $content]) {
            $section = HomeSection::firstOrCreate(['key' => $key], ['title' => $title, 'subtitle' => $subtitle, 'content' => $content, 'is_active' => true, 'sort_order' => $i++]);
            if (isset($images[$key])) {
                $this->attachImage($section, 'image', ...$images[$key]);
            }
        }

        $faqs = [
            ['Ordering', 'How do I place an order?', 'Add products to your cart, open the checkout, enter your delivery details and choose a payment method. You do not need an account, and our team calls you to confirm before anything is dispatched.', true],
            ['Delivery', 'How much is delivery and how long does it take?', 'It depends on your area. Charges and delivery times for Inside Dhaka, Dhaka sub-areas and outside Dhaka are shown at checkout and in the delivery policy.', true],
            ['Payment', 'Which payment methods can I use?', 'Cash on delivery is available everywhere we deliver. When bKash or Nagad are offered at checkout, send the order total and enter the Transaction ID — we confirm the order after verifying it.', true],
            ['LeatherX Tap', 'Does the NFC wallet need an app or a battery?', 'No. The chip is passive and powered by the phone that reads it. NFC-enabled Android phones and recent iPhones open your profile in the browser without any app.', true],
            ['LeatherX Tap', 'Can I change my profile after buying?', 'Yes. Sign in, open “NFC profile” in your account and edit it at any time. The chip keeps pointing to the same profile, so it never needs re-programming.', false],
            ['Engraving', 'Can engraved items be returned?', 'Personalised items cannot be returned or exchanged unless they arrive damaged or the engraving differs from what you approved.', true],
            ['Warranty', 'What does the warranty cover?', 'The warranty covers stitching, edges, zips, clasps and the NFC chip under normal use for the period shown on each product. It does not cover natural wear, scratches, water or heat damage.', false],
            ['Returns', 'Can I exchange a product?', 'Unused, non-personalised products can be exchanged under the conditions in our return policy. Contact us with your order number to start.', false],
        ];
        foreach ($faqs as $i => [$category, $q, $a, $home]) {
            Faq::firstOrCreate(['question' => $q], ['category' => $category, 'answer' => $a, 'show_on_home' => $home, 'sort_order' => $i]);
        }

        $pages = [
            'about' => ['About LeatherX', 'What LeatherX makes, who it is for and how orders, engraving and LeatherX Tap work.', '<p>LeatherX makes everyday leather goods — wallets, card holders, bags, passport and key holders — and LeatherX Tap, a range of wallets with a built-in NFC chip that shares your contact details with one tap.</p><h2>What we offer</h2><ul><li>Leather pieces in a choice of colours, most with RFID-blocking linings.</li><li>Laser engraving for names, initials, short lines and company logos.</li><li>LeatherX Tap contact profiles you manage yourself from your account.</li><li>Corporate gifting with one quotation and one invoice for the whole order.</li></ul><h2>How ordering works</h2><p>Order online without an account and pay cash on delivery. We call every customer to confirm the order before it is dispatched, and you can follow the delivery with your order number on the tracking page.</p><h2>After you buy</h2><p>Register your product warranty online, and contact us if anything is not right — the details are on our contact page.</p>'],
            'return-policy' => ['Return & Exchange Policy', 'When and how LeatherX products can be exchanged or returned.', '<h2>Exchanges</h2><p>Unused, non-personalised products in their original packaging can be exchanged within <strong>7 days</strong> of delivery.</p><h2>Not eligible</h2><ul><li>Engraved or otherwise personalised items, unless they arrive damaged or the engraving differs from what you approved.</li><li>Products that show signs of use.</li></ul><h2>Damaged or incorrect items</h2><p>If your order arrives damaged or is not what you ordered, contact us within 48 hours of delivery with your order number and photos. We will replace the item or refund it.</p><h2>How to request an exchange</h2><p>Contact us with your order number. Once we receive and inspect the item, we send the replacement or refund the amount to your original payment method.</p>'],
            'privacy-policy' => ['Privacy Policy', 'What personal information LeatherX collects and how it is used.', '<h2>What we collect</h2><p>Your name, phone number, email address and delivery address when you place an order or create an account, and the details you choose to add to a LeatherX Tap profile.</p><h2>How we use it</h2><ul><li>To confirm, deliver and support your orders.</li><li>To send order and warranty updates.</li><li>To show your LeatherX Tap profile to people who tap your wallet — only while the profile is active.</li></ul><h2>Payments</h2><p>For bKash and Nagad we only store the Transaction ID you enter so we can verify the payment. We never ask for your PIN or OTP.</p><h2>Your choices</h2><p>You can edit your account details and Tap profile at any time, delete your Tap profile, or contact us to close your account.</p>'],
            'terms-and-conditions' => ['Terms & Conditions', 'The terms that apply when you order from LeatherX.', '<h2>Orders</h2><p>An order is accepted once our team has confirmed it with you by phone. We may cancel an order if the contact details are invalid or a payment cannot be verified.</p><h2>Prices</h2><p>Prices are shown in Bangladeshi Taka and include the product price only; delivery and engraving charges are shown separately at checkout.</p><h2>Personalised items</h2><p>Engraving is produced exactly as approved, so please check spelling carefully. See the return policy for personalised items.</p><h2>LeatherX Tap profiles</h2><p>You are responsible for the information you publish on your profile. We may suspend profiles that are misleading or abusive.</p>'],
            'delivery-policy' => ['Delivery Policy', 'Delivery areas, charges and timelines for LeatherX orders.', '<p>We deliver across Bangladesh through courier partners. Charges and estimated delivery times for your area are calculated at checkout before you place the order.</p><h2>Timelines</h2><ul><li>Orders are dispatched after our confirmation call.</li><li>Engraved items need two to three extra working days before dispatch.</li><li>You can follow your order with its number on the tracking page.</li></ul><h2>Cash on delivery</h2><p>Please keep the exact amount ready. You may inspect the outer packaging before paying the courier.</p>'],
        ];
        foreach ($pages as $slug => [$title, $description, $content]) {
            Page::firstOrCreate(['slug' => $slug], ['title' => $title, 'seo_description' => $description, 'content' => $content, 'is_active' => true]);
        }

        $posts = [
            ['how-to-care-for-a-leather-wallet', 'How to care for a leather wallet', 'Care guide', 'Simple habits that keep leather looking good for years.', '<p>Leather is durable, but a little care keeps it supple and stops it cracking.</p><h2>Keep it dry</h2><p>If your wallet gets wet, blot it with a soft cloth and let it air-dry at room temperature. Never use a hair dryer or leave it in the sun — fast drying cracks leather.</p><h2>Condition it a few times a year</h2><p>Apply a small amount of neutral leather conditioner with a soft cloth, work it in with circular movements and buff off the excess.</p><h2>Don’t overfill it</h2><p>Carrying more cards than a wallet is designed for stretches the pockets permanently. Check the card capacity on the product page.</p>'],
            ['what-is-an-nfc-wallet', 'What is an NFC wallet, and how does LeatherX Tap work?', 'LeatherX Tap', 'How a small passive chip turns a wallet into a digital contact card.', '<p>NFC (Near Field Communication) is the short-range radio technology behind contactless payments. A LeatherX Tap wallet contains a small passive NFC tag that stores one thing: the web address of your profile.</p><h2>What happens when someone taps</h2><p>Their phone reads the address and opens your profile in the browser. From there they can call you, message you on WhatsApp, visit your website or save you as a contact.</p><h2>Is it safe?</h2><p>The tag only holds a link — no payment or personal data. You decide what appears on your profile and can switch it off from your account.</p>'],
            ['why-rfid-protection-matters', 'Does an RFID-blocking wallet make a difference?', 'Guides', 'What RFID-blocking linings do for contactless cards.', '<p>Contactless bank cards respond to readers held very close to them. An RFID-blocking lining stops those signals from reaching the card while it is inside your wallet.</p><p>It is a simple layer that adds peace of mind when you carry several contactless cards in crowded places.</p>'],
        ];
        foreach ($posts as $i => [$slug, $title, $category, $excerpt, $body]) {
            Post::firstOrCreate(['slug' => $slug], [
                'title' => $title, 'category' => $category, 'excerpt' => $excerpt, 'body' => $body,
                'is_published' => true, 'published_at' => now()->subDays(($i + 1) * 5),
            ]);
        }
    }

    /** Copy a photo from public/images into the media library (WebP sizes are generated). */
    private function attachImage($model, string $collection, string $path, string $alt, ?string $focus = null): void
    {
        $file = public_path('images/'.$path);

        if (app()->runningUnitTests() || ! is_file($file) || $model->hasMedia($collection)) {
            return;
        }

        $model->addMedia($file)
            ->preservingOriginal()
            ->usingName($alt)
            ->withCustomProperties(array_filter(['alt' => $alt, 'focus' => $focus]))
            ->toMediaCollection($collection);
    }
}
