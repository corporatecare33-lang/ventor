# LeatherX

Premium eCommerce for leather wallets, LeatherX Tap NFC smart wallets, bags, card holders,
passport and key holders — with laser engraving, warranty and corporate gifting.

**Stack:** Laravel 13 · PHP 8.3+ · MySQL 8 · Blade + Livewire 4 (Alpine.js bundled) · Tailwind CSS 4 · Vite ·
Filament 5 admin · Spatie Permission · Spatie Media Library.

> Filament 5 requires Livewire 4, so the project uses Livewire 4 (the API used here is the same as Livewire 3).

## Quick start (local)

```bash
composer install
npm install && npm run build
cp .env.example .env          # set DB_* (MySQL) or DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate --seed    # prints the super-admin password if ADMIN_PASSWORD is empty
php artisan storage:link
php artisan serve             # storefront: http://localhost:8000  ·  admin: /admin
```

Run the tests: `php artisan test` (SQLite in-memory, no setup needed).

## What's inside

| Area | Where |
| --- | --- |
| Database schema | `database/migrations/2026_10_10_12*` |
| Models & relationships | `app/Models` |
| Business logic (cart, checkout, stock, order status) | `app/Services` |
| Admin panel (26 modules, dashboard, reports, settings, roles) | `app/Filament` |
| Storefront controllers | `app/Http/Controllers` |
| Livewire components (cart, checkout, filters, quick view, warranty) | `app/Livewire`, `resources/views/livewire` |
| Blade pages & UI components | `resources/views` |
| SEO (meta, Open Graph, JSON-LD, sitemap, robots) | `app/Support/Seo.php`, `SeoController` |
| Caching | `app/Support/StorefrontCache.php` (versioned, auto-flushed on admin changes) |
| Seed data (sample catalogue, no images) | `database/seeders` |
| Tests | `tests/Feature` |
| Deployment guide (cPanel / shared hosting) | [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) |

## Admin roles

| Role | Access |
| --- | --- |
| `super_admin` | Everything (bypasses all checks) |
| `admin` | Everything except managing admin users |
| `manager` | Catalog, orders, marketing, services, content; view customers & reports |
| `support` | View catalog/orders/customers; manage engraving, NFC, warranty, inquiries |
| `customer` | Storefront account only — no admin access |

Permissions are `view {area}` / `manage {area}` (see `app/Support/Permissions.php`) and can be edited
in **Admin → Access → Roles & permissions**.

## Integrations status (honest list)

Nothing below is faked. These run as manual, admin-controlled flows until real credentials and integration
code are added:

- **bKash / Nagad** — customer sends money and enters the TrxID; admin verifies it in **Sales → Payments**.
- **Online gateways (SSLCommerz, bKash PGW)** — not connected.
- **Courier (Steadfast, Pathao, RedX)** — courier name + tracking number are entered by admin.
- **SMS** — not connected; email confirmation is sent via the queue.
- **NFC** — software only: each profile has a public URL (`/tap/{slug}`) that the chip / QR code points to.
