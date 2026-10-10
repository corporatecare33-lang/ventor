<?php

namespace Database\Seeders;

use App\Enums\CouponType;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class StoreSetupSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Inside Dhaka', 'description' => 'Dhaka city corporation areas', 'charge' => 70, 'free_shipping_threshold' => 3000, 'min_days' => 1, 'max_days' => 2],
            ['name' => 'Dhaka Sub-area', 'description' => 'Savar, Gazipur, Narayanganj, Keraniganj', 'charge' => 100, 'free_shipping_threshold' => 5000, 'min_days' => 2, 'max_days' => 3],
            ['name' => 'Outside Dhaka', 'description' => 'All other districts', 'charge' => 130, 'free_shipping_threshold' => 5000, 'min_days' => 2, 'max_days' => 5],
        ];
        foreach ($zones as $i => $zone) {
            ShippingZone::updateOrCreate(['name' => $zone['name']], [...$zone, 'sort_order' => $i]);
        }

        // bKash / Nagad stay inactive until the business enters its own merchant number in the admin.
        $methods = [
            ['code' => 'cod', 'name' => 'Cash on Delivery', 'instructions' => 'Pay the courier in cash when your order arrives.', 'requires_transaction_id' => false, 'is_active' => true],
            ['code' => 'bkash', 'name' => 'bKash (Send Money)', 'instructions' => 'Send the order total to our bKash number, then enter the Transaction ID (TrxID) from the confirmation SMS. Your order is confirmed once we verify the payment.', 'requires_transaction_id' => true, 'is_active' => false],
            ['code' => 'nagad', 'name' => 'Nagad (Send Money)', 'instructions' => 'Send the order total to our Nagad number, then enter the Transaction ID from the confirmation SMS. Your order is confirmed once we verify the payment.', 'requires_transaction_id' => true, 'is_active' => false],
        ];
        foreach ($methods as $i => $method) {
            PaymentMethod::firstOrCreate(['code' => $method['code']], [...$method, 'sort_order' => $i]);
        }

        Coupon::updateOrCreate(['code' => 'WELCOME10'], [
            'description' => '10% off your first order (max ৳500)',
            'type' => CouponType::Percent, 'value' => 10, 'max_discount' => 500, 'min_order_amount' => 1500, 'is_active' => true,
        ]);
        Coupon::updateOrCreate(['code' => 'FREESHIP'], [
            'description' => 'Free delivery on orders over ৳2,000',
            'type' => CouponType::Fixed, 'value' => 0, 'min_order_amount' => 2000, 'free_shipping' => true, 'is_active' => true,
        ]);
    }
}
