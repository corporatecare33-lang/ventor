<?php

namespace Database\Seeders;

use App\Enums\BannerPosition;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\HomeSection;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        Banner::firstOrCreate(['position' => BannerPosition::Hero, 'title' => 'Leather, crafted for the way you carry today'], [
            'eyebrow' => 'Handcrafted · NFC enabled',
            'subtitle' => 'Full-grain leather wallets and bags with RFID protection, a built-in NFC digital profile and free laser engraving on selected pieces.',
            'button_text' => 'Shop the collection', 'button_link' => '/shop',
            'secondary_button_text' => 'What is LeatherX Tap?', 'secondary_button_link' => '/leatherx-tap',
            'is_active' => true,
        ]);

        $sections = [
            'trust_badges' => ['Why LeatherX', null, ['items' => [
                ['icon' => 'shield', 'title' => 'RFID Protection', 'text' => 'Blocks unauthorised scans of your contactless cards.'],
                ['icon' => 'signal', 'title' => 'NFC Enabled', 'text' => 'Share your digital profile with a single tap.'],
                ['icon' => 'badge', 'title' => 'Leather Warranty', 'text' => 'Up to 2 years on stitching and hardware.'],
                ['icon' => 'truck', 'title' => 'Fast Delivery', 'text' => '24–48 hours inside Dhaka, nationwide in 2–5 days.'],
            ]]],
            'tap_intro' => ['What is LeatherX Tap?', 'A leather wallet with a tiny NFC chip inside. Tap it on any modern phone and your digital business profile opens instantly — no app needed.', ['items' => [
                ['icon' => 'signal', 'title' => 'Tap to share', 'text' => 'Hold the wallet near a phone; your profile opens in the browser.'],
                ['icon' => 'phone', 'title' => 'Edit any time', 'text' => 'Update phone, email, socials and website from your account.'],
                ['icon' => 'card', 'title' => 'QR backup', 'text' => 'Every profile also has a QR code for older phones.'],
            ], 'button_text' => 'Explore LeatherX Tap', 'button_link' => '/leatherx-tap']],
            'series' => ['Shop by series', 'Every series is cut from genuine leather and finished by hand.', ['items' => []]],
            'comparison' => ['Find your fit', 'Compare our most popular wallets side by side.', ['items' => []]],
            'engraving' => ['Laser engraving in four steps', 'Make it personal — names, initials, a short quote or your company logo.', ['items' => [
                ['icon' => 'pen', 'meta' => '01', 'title' => 'Choose your piece', 'text' => 'Pick any product marked “Engraving available”.'],
                ['icon' => 'sparkles', 'meta' => '02', 'title' => 'Add your text', 'text' => 'Enter a name, initials, quote or logo instruction on the product page.'],
                ['icon' => 'check', 'meta' => '03', 'title' => 'We confirm', 'text' => 'Our team checks spelling and placement before engraving.'],
                ['icon' => 'gift', 'meta' => '04', 'title' => 'Delivered gift-ready', 'text' => 'Engraved, boxed and shipped within 2–3 working days.'],
            ], 'button_text' => 'Learn about engraving', 'button_link' => '/engraving']],
            'corporate' => ['Corporate gifting, made premium', 'Branded leather gifts for employees, clients and events — with your logo laser-engraved and bulk pricing from 20 pieces.', ['items' => [
                ['icon' => 'building', 'meta' => '20+', 'title' => 'Minimum order'],
                ['icon' => 'sparkles', 'meta' => 'Free', 'title' => 'Logo engraving on bulk orders'],
                ['icon' => 'gift', 'meta' => 'Custom', 'title' => 'Gift boxes & cards'],
                ['icon' => 'truck', 'meta' => 'Nationwide', 'title' => 'Delivery to every office'],
            ], 'button_text' => 'Request a quote', 'button_link' => '/corporate']],
            'reviews' => ['What our customers say', null, ['items' => []]],
            'delivery_payment' => ['Shop with confidence', null, ['items' => [
                ['icon' => 'cash', 'title' => 'Cash on Delivery', 'text' => 'Pay when you receive your order, anywhere in Bangladesh.'],
                ['icon' => 'phone', 'title' => 'bKash & Nagad', 'text' => 'Pay in advance from your mobile wallet.'],
                ['icon' => 'truck', 'title' => 'Inside & Outside Dhaka', 'text' => 'Courier delivery to all 64 districts.'],
                ['icon' => 'check', 'title' => '7-day easy exchange', 'text' => 'Exchange unused items within 7 days.'],
            ]]],
            'faq' => ['Frequently asked questions', null, ['items' => []]],
        ];
        $i = 0;
        foreach ($sections as $key => [$title, $subtitle, $content]) {
            HomeSection::firstOrCreate(['key' => $key], ['title' => $title, 'subtitle' => $subtitle, 'content' => $content, 'is_active' => true, 'sort_order' => $i++]);
        }

        $faqs = [
            ['Ordering', 'How do I place an order?', 'Add products to your cart, go to checkout, enter your delivery details and choose a payment method. Our team will call to confirm before dispatch.', true],
            ['Delivery', 'How long does delivery take?', 'Inside Dhaka 1–2 working days, Dhaka sub-areas 2–3 days and outside Dhaka 2–5 days.', true],
            ['Payment', 'Which payment methods do you accept?', 'Cash on delivery, bKash and Nagad. For bKash/Nagad, send money to our number and enter the transaction ID at checkout.', true],
            ['LeatherX Tap', 'Does the NFC wallet need an app or battery?', 'No. The NFC chip is passive — it needs no battery, and modern phones open your profile in the browser without any app.', true],
            ['LeatherX Tap', 'Can I change my profile after buying?', 'Yes. Log in to your account and edit your NFC profile any time; the same chip will show the updated details.', false],
            ['Engraving', 'Can engraved items be returned?', 'Personalised items cannot be returned unless they arrive damaged or with an engraving error on our side.', true],
            ['Warranty', 'What does the warranty cover?', 'Stitching, edges, zips and hardware under normal use. It does not cover wear and tear, water damage or misuse.', false],
            ['Returns', 'What is your exchange policy?', 'Unused, non-personalised items can be exchanged within 7 days of delivery. See our return policy for details.', false],
        ];
        foreach ($faqs as $i => [$category, $q, $a, $home]) {
            Faq::firstOrCreate(['question' => $q], ['category' => $category, 'answer' => $a, 'show_on_home' => $home, 'sort_order' => $i]);
        }

        $pages = [
            'about' => ['About LeatherX', '<h2>Crafted to be carried</h2><p>LeatherX started with a simple idea: everyday leather goods should be honest, durable and a little smarter. We work with full-grain leather, hand-burnish every edge and add useful technology — RFID shielding and NFC digital profiles — without losing the warmth of real leather.</p><h2>Our promise</h2><ul><li>Genuine leather only</li><li>Hand-finished in small batches</li><li>Warranty on every piece</li><li>Real people on support</li></ul>'],
            'return-policy' => ['Return & Exchange Policy', '<p>Unused, non-personalised products can be exchanged within <strong>7 days</strong> of delivery. Items must be returned in original packaging.</p><h3>Not eligible</h3><ul><li>Engraved / personalised items (unless faulty)</li><li>Products with signs of use</li></ul><h3>How to request</h3><p>Contact us with your order number. Once we receive and check the item, we will send the replacement or refund to your original payment method.</p>'],
            'privacy-policy' => ['Privacy Policy', '<p>We collect only the information needed to process your orders and provide support: name, phone, email and delivery address. We never sell your data.</p><p>NFC profile information is public only for profiles you activate, and you can edit or deactivate it at any time.</p><p>Payments made via bKash/Nagad are verified manually using the transaction ID you provide; we never ask for your PIN.</p>'],
            'terms-and-conditions' => ['Terms & Conditions', '<p>By placing an order on LeatherX you agree to provide accurate delivery information and to receive a confirmation call. Prices and availability may change without notice. Orders may be cancelled if payment cannot be verified or contact details are invalid.</p>'],
            'delivery-policy' => ['Delivery Policy', '<ul><li><strong>Inside Dhaka:</strong> 1–2 working days</li><li><strong>Dhaka sub-areas:</strong> 2–3 working days</li><li><strong>Outside Dhaka:</strong> 2–5 working days</li></ul><p>Delivery charges are shown at checkout. Engraved items need 2–3 extra working days.</p>'],
        ];
        foreach ($pages as $slug => [$title, $content]) {
            Page::firstOrCreate(['slug' => $slug], ['title' => $title, 'content' => $content, 'is_active' => true]);
        }

        $posts = [
            ['how-to-care-for-a-leather-wallet', 'How to care for your leather wallet', 'Care guide', 'Simple habits that keep full-grain leather looking better every year.', '<p>Full-grain leather is tough, but a little care goes a long way.</p><h2>Keep it dry</h2><p>If your wallet gets wet, let it air-dry away from direct heat.</p><h2>Condition twice a year</h2><p>Use a neutral leather conditioner on a soft cloth.</p><h2>Don’t overstuff</h2><p>Carry what you need — your wallet will keep its shape for years.</p>'],
            ['what-is-an-nfc-wallet', 'What is an NFC smart wallet?', 'LeatherX Tap', 'How a tiny passive chip turns your wallet into a digital business card.', '<p>NFC (Near Field Communication) is the same technology used in contactless payments. LeatherX Tap wallets contain a small passive chip that stores a link to your digital profile.</p><p>When someone taps the wallet with their phone, your profile opens instantly — no app needed.</p>'],
            ['why-rfid-protection-matters', 'Why RFID protection matters', 'Guides', 'Contactless cards are convenient; RFID shielding keeps them private.', '<p>RFID-blocking lining stops nearby readers from scanning contactless cards in your wallet without your knowledge.</p>'],
        ];
        foreach ($posts as $i => [$slug, $title, $category, $excerpt, $body]) {
            Post::firstOrCreate(['slug' => $slug], [
                'title' => $title, 'category' => $category, 'excerpt' => $excerpt, 'body' => $body,
                'is_published' => true, 'published_at' => now()->subDays(($i + 1) * 5),
            ]);
        }
    }
}
