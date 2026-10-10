# Deploying LeatherX to cPanel / shared hosting

This guide targets a typical Bangladeshi shared host (cPanel + LiteSpeed/Apache + MySQL).
The same steps work on a VPS — there you can also run a real queue worker with Supervisor.

---

## 1. Hosting requirements

| Item | Requirement |
| --- | --- |
| PHP | **8.3 or newer** (cPanel → *Select PHP Version* / *MultiPHP Manager*) |
| PHP extensions | `ctype`, `curl`, `dom`, `fileinfo`, `intl` (**required by Filament**), `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `gd` (image conversions), `exif` |
| Database | MySQL 8 (or MariaDB 10.6+) |
| Access | cPanel **Terminal** or SSH (needed for `php artisan` commands) |
| Cron | One cron job (see step 7) |

If your host has no Terminal/SSH, ask support to enable it — Laravel deployments without it are painful.

---

## 2. Build on your computer

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # creates public/build (CSS, JS, self-hosted fonts)
```

Zip the project **without** `node_modules`, `.env`, `storage/logs/*` and `database/database.sqlite`.

> Alternative: if the host has Git + Composer, clone the repo on the server and run `composer install --no-dev`
> there. You still need to build assets locally (or in CI) because shared hosts rarely have Node.js.

---

## 3. Upload the files

Keep the application **outside** `public_html` so `.env` and source code are never web-accessible:

```
/home/CPANELUSER/leatherx/        <- whole project here
/home/CPANELUSER/public_html/     <- web root
```

Choose **one** of these:

**Option A (recommended): point the domain to `/public`**
cPanel → *Domains* → edit the domain → set *Document Root* to `leatherx/public`.

**Option B: copy `public/` into `public_html`**
1. Copy everything inside `leatherx/public/` (including `.htaccess` and `build/`) into `public_html/`.
2. Edit `public_html/index.php` and change the two paths:
   ```php
   require __DIR__.'/../leatherx/vendor/autoload.php';
   $app = require_once __DIR__.'/../leatherx/bootstrap/app.php';
   ```
   and the maintenance line to `__DIR__.'/../leatherx/storage/framework/maintenance.php'`.
3. In `leatherx/bootstrap/app.php` nothing changes. When you rebuild assets later, copy `public/build` again.

---

## 4. Database

cPanel → *MySQL® Databases*:
1. Create a database, e.g. `cpuser_leatherx`.
2. Create a user with a strong password.
3. Add the user to the database with **ALL PRIVILEGES**.

---

## 5. Environment file

```bash
cd ~/leatherx
cp .env.example .env
nano .env
```

Production values:

```dotenv
APP_NAME=LeatherX
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Dhaka

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=cpuser_leatherx
DB_USERNAME=cpuser_leatherx
DB_PASSWORD=********

CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database
MEDIA_DISK=public
LOG_STACK=daily
LOG_LEVEL=warning

MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=orders@yourdomain.com
MAIL_PASSWORD=********
MAIL_FROM_ADDRESS=orders@yourdomain.com

ADMIN_NAME="Your Name"
ADMIN_EMAIL=you@yourdomain.com
ADMIN_PASSWORD=choose-a-strong-password
```

> Never set `APP_DEBUG=true` on the live site — it exposes secrets in error pages.

---

## 6. Install

```bash
cd ~/leatherx
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force        # roles, admin user, settings, shipping, payment methods, sample catalogue
php artisan storage:link           # product images (Option B: see troubleshooting)
php artisan optimize               # config, routes, views, events
php artisan filament:optimize      # Filament components & icons
chmod -R 775 storage bootstrap/cache
```

Don't want sample products? Seed only the essentials instead of the full `db:seed`:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=SettingsSeeder --force
php artisan db:seed --class=StoreSetupSeeder --force
php artisan db:seed --class=ContentSeeder --force
```

Then sign in at **https://yourdomain.com/admin**, change the password from the profile menu, and fill in
**Settings → Website settings** (logo, phone, WhatsApp, address, social links) and **Settings → SEO**.

---

## 7. Cron job (queue + scheduled tasks)

cPanel → *Cron Jobs* → *Once per minute*:

```
* * * * * cd /home/CPANELUSER/leatherx && php artisan schedule:run >> /dev/null 2>&1
```

Every minute this processes queued jobs (order emails, image conversions) and once a day it prunes abandoned
guest carts. Use the full PHP path if needed, e.g. `/usr/local/bin/php` or `/opt/cpanel/ea-php83/root/usr/bin/php`.

---

## 8. SSL

cPanel → *SSL/TLS Status* → run **AutoSSL**. Make sure `APP_URL` starts with `https://`.
Optionally force HTTPS by adding to the top of `public/.htaccess` (inside `<IfModule mod_rewrite.c>`):

```apache
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## 9. Updating the site later

```bash
php artisan down
# upload new files (or git pull), then:
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize && php artisan filament:optimize
php artisan up
```

Upload a fresh `public/build` whenever CSS/JS changed.

---

## 10. Performance checklist

Already built in:

- Storefront fragments (homepage, menus, related products, sitemap) are cached and flushed automatically when
  admins edit products, categories, banners, settings, etc. (`App\Support\StorefrontCache`).
- Settings load from a single cached array.
- Product lists use selected columns, eager loading and pagination (no N+1 queries).
- Images: Media Library creates WebP thumbnails (480px) and large (1200px) versions; cards use `loading="lazy"`,
  the first image uses `fetchpriority="high"`.
- Fonts are self-hosted (no Google/Bunny requests). CSS ≈17 KB gzip; no extra JS beyond Livewire/Alpine.
- `public/.htaccess` enables gzip and long-term caching for hashed assets.

On the server:

- Always run `php artisan optimize` after deploying.
- Enable **OPcache** in *Select PHP Version → Options*.
- If the host offers **LiteSpeed Cache** or Cloudflare, cache static assets only — do **not** full-page cache
  `/cart`, `/checkout`, `/account` or `/livewire/*`.
- Upload product photos at ~1200×1200 px; the system creates smaller versions automatically.

---

## 11. Third-party integrations (not connected yet)

These are **manual / admin-controlled** today. Credentials go in `.env` (placeholders are listed in
`.env.example`) and need integration code before they do anything.

| Service | Current behaviour | What is needed later |
| --- | --- | --- |
| bKash / Nagad | Customer sends money, enters TrxID; admin verifies in **Sales → Payments** | Merchant API credentials (bKash PGW / Nagad), callback route that verifies and marks the `Payment` as verified |
| SSLCommerz (cards) | Not available | Store ID + password, IPN route |
| Steadfast / Pathao / RedX | Admin types courier + tracking number on the order | API key/secret, a "Create parcel" action on the order |
| SMS (order confirmation, OTP) | Not sent — email only | SMS gateway API key + sender ID; add to `App\Jobs\SendOrderPlacedNotification` |
| NFC chips | Software profile at `/tap/{slug}`; chips/QR codes are programmed with that URL using any NFC writer app | Nothing — no hardware API required |

---

## 12. Troubleshooting

| Problem | Fix |
| --- | --- |
| 500 error right after upload | Check `storage/logs/laravel-*.log`; usually wrong DB credentials, missing `APP_KEY`, or `storage` not writable (`chmod -R 775 storage bootstrap/cache`). |
| `storage:link` not allowed (Option B) | Create the link manually: `ln -s /home/CPANELUSER/leatherx/storage/app/public /home/CPANELUSER/public_html/storage` |
| Admin login page loads but buttons do nothing | ModSecurity can block `/livewire/update`. Ask the host to whitelist it, or disable the rule for the domain. |
| Images upload but thumbnails missing | Enable the `gd` extension, make sure the cron job runs (large conversions are queued). |
| Styles missing | `public/build` was not uploaded (or not copied into `public_html/build` with Option B). |
| `intl` extension error in admin | Enable `intl` in *Select PHP Version → Extensions*. |
| Changes in admin not visible | They flush the cache automatically; if you edited the database directly run `php artisan cache:clear`. |
