<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'site_name' => 'LeatherX',
            'site_tagline' => 'Handcrafted leather. Smarter carry.',
            'footer_about' => 'LeatherX makes full-grain leather wallets, NFC smart wallets, bags and accessories, finished by hand and personalised with laser engraving.',
            'copyright_text' => '© '.date('Y').' LeatherX. All rights reserved.',
            'contact_phone' => '+880 1XXX-XXXXXX',
            'contact_whatsapp' => '8801XXXXXXXXX',
            'contact_email' => 'hello@leatherx.test',
            'contact_address' => 'Dhaka, Bangladesh',
            'business_hours' => 'Sat–Thu, 10:00 AM – 8:00 PM',
            'announcement_enabled' => true,
            'announcement_text' => 'Free delivery inside Dhaka on orders over ৳3,000 · Cash on delivery available',
            'announcement_link' => '/shop',
            'trusted_customers' => 'Trusted by thousands of customers across Bangladesh',
            'checkout_note' => 'Our team will call to confirm your order before dispatch.',
            'seo_title_suffix' => ' | LeatherX',
            'seo_home_title' => 'LeatherX — Premium Leather Wallets, NFC Smart Wallets & Bags in Bangladesh',
            'seo_default_description' => 'Shop full-grain leather wallets, LeatherX Tap NFC smart wallets, card holders, bags and corporate gifts. RFID protection, laser engraving, warranty and cash on delivery across Bangladesh.',
            'seo_keywords' => 'leather wallet bangladesh, nfc wallet, smart wallet, rfid wallet, leather bag, card holder, corporate gift',
            'seo_allow_indexing' => true,
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
