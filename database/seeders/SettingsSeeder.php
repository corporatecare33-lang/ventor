<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Only brand copy and SEO defaults are seeded. Contact details, social links and
        // payment numbers are left empty on purpose: they must be entered by the business
        // in Admin → Settings, and the storefront hides anything that is not configured.
        $defaults = [
            'site_name' => 'LeatherX',
            'site_tagline' => 'Leather goods for everyday carry',
            'footer_about' => 'LeatherX designs leather wallets, card holders, bags and LeatherX Tap NFC wallets, with laser engraving and delivery across Bangladesh.',
            // Matches the Inside Dhaka free-delivery threshold seeded in StoreSetupSeeder.
            'announcement_enabled' => true,
            'announcement_text' => 'Free delivery inside Dhaka on orders over ৳3,000 — cash on delivery across Bangladesh.',
            'announcement_link' => '/delivery-policy',
            'checkout_note' => 'We call every customer to confirm the order before it is dispatched.',
            'seo_title_suffix' => ' | LeatherX',
            'seo_home_title' => 'LeatherX — Leather Wallets, NFC Smart Wallets & Bags in Bangladesh',
            'seo_default_description' => 'Leather wallets, card holders, bags and LeatherX Tap NFC wallets with RFID-blocking linings and laser engraving. Cash on delivery across Bangladesh.',
            'seo_allow_indexing' => true,
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
