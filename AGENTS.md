# LeatherX — notes for AI agents

Laravel 13 eCommerce (Blade + Livewire 4 storefront, Filament 5 admin). Read `README.md` first.

- Run `php artisan test` and `vendor/bin/pint` before committing.
- Filament 5 injects closure parameters **by name**: use `$query`, `$record`, `$state`, `$get`, `$set`, `$data` —
  never short names like `$q` or `$r` (they arrive as `null`).
- Blade icons: use `<x-lx-icon name="..." />` (`<x-icon>` belongs to blade-icons used by Filament).
- Cache stores only allow-listed classes (`config/cache.php` → `serializable_classes`). Add a model there before
  caching it through `StorefrontCache`.
- Stock changes must go through `App\Services\InventoryService` so they are logged in `stock_movements`.
- Order status changes must go through `App\Services\OrderService` (restocks on cancel/return).
- Product route key is `slug`; order route key is `order_number`; NFC profile route key is `slug`.
- Do not add fake integrations: payment/courier/SMS are manual until real credentials exist.
