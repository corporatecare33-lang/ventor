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
- Design tokens are in `resources/css/app.css`; use the `ui`, `form` and `product` Blade components instead of
  ad-hoc markup. Type: Inter only (bold, tightly tracked headings). Radius: `rounded-xs` (form controls) / `rounded-sm`
  (small media) / `rounded-card` (product cards, panels) / `rounded-full` (campaign pills). Brass only on dark
  backgrounds; cognac only for large titles on white. The homepage follows ventro.com.bd's structure with our own facts.
- Hero slides are Banners (position Hero); a `|` in the title puts the rest on a brass second line.
- Desktop mega menu groups (Wallets, Bags, Smart Accessories) are category slugs in `App\Support\MegaMenu::GROUPS`;
  product rows load from `/menu/{group}` on first open (cached fragment).
- Never invent business facts (contact details, statistics, testimonials). Hide sections when data is missing.
- The shop assistant (`App\Services\Assistant`) is rule-based with no external AI; every answer must come from store
  data (products, cart, orders, FAQs, zones, payment methods, settings). Add words/spellings in `Lexicon.php`.
- Scroll containers that hold `sr-only` text need `relative`, or the hidden text widens the page on mobile.

