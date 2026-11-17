# Shagun Enterprises — makhana trading website

A complete PHP website for a makhana (fox nuts) grading, packing and distribution
business in Purnea, Bihar. Built to run on ordinary shared hosting: no build step,
no Node, no Composer, no external services.

---

## What is included

**Public site**

| Page | URL | Purpose |
| --- | --- | --- |
| Home | `/` | Brand, four grades, trading strengths, process, rate snapshot, partners, CTA |
| Grades | `/products` | Catalogue with side-by-side comparison table |
| Grade detail | `/product/premium-select` | Specs, packing options, related grades (one page per grade) |
| Market | `/market` | Indicative wholesale rate table + live quantity estimator |
| Bulk order | `/bulk-order` | Full quote-request form with estimate, sampling and WhatsApp fallbacks |
| About | `/about` | Origin, grading discipline, partner list, registered details |
| Distributors | `/distributors` | Existing partners + territory application pitch |
| Contact | `/contact` | Address, map, phone, email and a short enquiry form |
| Track | `/track` | Buyer self-service status lookup by reference + mobile |
| FAQ | `/faq` | Grouped buyer questions (also emitted as FAQPage structured data) |
| Privacy / Terms | `/privacy`, `/terms` | Plain-language policy and terms of trade |
| 404 | any | Branded dead end with escape routes |

**Trading features**

- Quote requests for **bulk purchase**, **samples**, **distributor applications** and general enquiries.
- Server-side validation with per-field errors, honeypot spam trap, CSRF protection and per-IP rate limiting.
- Indicative order value calculated from grade rate × quantity, shown live in the form and stored with the order.
- New-request email to the business **and** an acknowledgement email to the buyer (with the reference number).
- Admin panel: overview dashboard, filterable order queue, order detail with status workflow, internal notes,
  CSV export, rate/MOQ editor, credential management.
- Buyer tracking page: order status timeline plus any note from the desk.
- WhatsApp deep links pre-filled with the reference and requirement on every page.

**Technical**

- PDO (SQLite by default, MySQL supported) with prepared statements everywhere.
- Self-hosted Fraunces + Inter webfonts, CSS and JS with cache-busting query strings.
- SEO: canonical URLs, Open Graph/Twitter cards, JSON-LD (Organization, Product, ItemList, FAQPage),
  dynamic `sitemap.xml`, `robots.txt`, semantic headings.
- Security headers, HTTPS-ready config, no `display_errors` in production.

---

## Requirements

- PHP **8.0+** (tested on 8.3), with `PDO` and either `pdo_sqlite` or `pdo_mysql`.
- Apache with `mod_rewrite` (for pretty URLs) — or Nginx with the equivalent rules (see below).
- Writable `data/` directory (the SQLite database and mail log live there).
- `mail()` available, or switch mail to log mode while you configure SMTP/SPF.

---

## Install

1. **Upload** the contents of this folder to your web root (`public_html/` or `htdocs/`),
   keeping the folder structure intact.
2. **Create the config**: copy `config.sample.php` to `config.php` and edit:
   - `site.url` — full site URL, no trailing slash (used for canonical, sitemap, OG tags).
   - `site.phone`, `site.whatsapp`, `site.email`, `site.address`, `site.gstin`, `site.pan`.
   - `mail.*` — where new-order notifications go.
3. **Permissions**: make `data/` writable by the web server user (usually `755` on the folder,
   `644` on files; `chown` to the web user if your host needs it).
4. **Run the installer**: open `https://your-domain/install.php`
   - it checks the environment, creates the tables, seeds the four grades and partner list,
   - and lets you set the admin username and password.
5. **Delete `install.php`** from the server. (The installer also writes `data/installed.lock`
   and refuses to run again while that file exists.)
6. Sign in at `/admin/`, then check `/products`, `/market` and `/bulk-order`.
   Submit one test order and confirm the email arrives — if it does not, see *Email* below.

Default admin credentials are whatever you set in step 4. Change them any time under
**Admin → Account**.

---

## Configuration reference (`config.php`)

```php
'site' => [
    'url'         => 'https://example.com',        // no trailing slash
    'phone'       => '+91 82109 72729',            // display format
    'phone_raw'   => '+918210972729',
    'whatsapp'    => '918210972729',               // digits only, country code included
    'email'       => 'sandeepsk1994.sk@gmail.com',
    'address'     => 'At + Post – Harda Bazar, …',
    'gstin'       => '10GEIPK2689D1ZL',
    'pan'         => 'GEIPK2689D',
    'hours'       => 'Mon – Sat, 9:30 AM – 7:00 PM IST',
    'map_embed'   => 'https://maps.google.com/maps?q=…&output=embed',
    'map_link'    => 'https://www.google.com/maps/search/?api=1&query=…',
],

'db' => [
    'driver' => 'sqlite',   // or 'mysql'
    'path'   => __DIR__ . '/data/app.sqlite',
    // mysql: host, port, name, user, pass, charset
],

'mail' => [
    'enabled'   => true,
    'mode'      => 'mail',  // 'mail' sends, 'log' appends to data/mail.log
    'from'      => 'orders@example.com',
    'from_name' => 'Shagun Enterprises Website',
    'to'        => 'sandeepsk1994.sk@gmail.com',
],

'security' => [
    'rate_limit_per_hour' => 6,     // max quote requests per IP per hour
    'session_name'        => 'shagun_sess',
    'force_https'         => false, // set true once SSL is active
],

'features' => [
    'market_rates'  => true,
    'online_orders' => true,        // set false to pause the order form
],
```

### Database import (MySQL)

`database.sql` in the project root is a ready-to-import MySQL file: it creates the five
tables and loads the four grades, the distributor list and the site settings (including a
default admin login). To use it:

1. Create the database in your hosting panel (cPanel → *MySQL Databases*) and add your
   user to it with **All Privileges**.
2. Put the credentials in `config.php` (`db.driver = 'mysql'`, `host`, `name`, `user`, `pass`).
3. In phpMyAdmin, **select the database first**, then *Import* → choose `database.sql` → *Go*.
4. Verify with `SELECT COUNT(*) FROM products;` (expect 4), then open `/admin/` and sign in
   with the login written at the top of `database.sql`. Change it immediately under
   **Admin → Account**.

Importing is safe to repeat: tables use `CREATE TABLE IF NOT EXISTS` and rows use
`ON DUPLICATE KEY UPDATE`, so existing orders are never deleted.

Alternatively, leave the database empty and open `/install.php` once — it creates the tables
from `data/schema.mysql.sql`, seeds the same content and lets you choose your own admin
password.

### Switching to MySQL

1. Create the database and a user in your hosting panel.
2. In `config.php` set `db.driver = 'mysql'` and fill `host`, `port`, `name`, `user`, `pass`.
3. Import `data/schema.mysql.sql` (or delete `data/app.sqlite` + `data/installed.lock`
   and re-run `install.php`, which applies the right schema automatically).
4. Seed once by running the installer.

### Email

The site uses PHP's `mail()`. For reliable delivery:

- Send from an address on your own domain, not from Gmail.
- Publish SPF and DKIM records for that domain with your host.
- Use `mode => 'log'` while testing — every message is appended to `data/mail.log`.
- If `mail()` is disabled by your host, replace `mailer_send()` in `includes/mailer.php`
  with an SMTP call.

---

## Using the admin panel

- **Overview** (`/admin/`) — counts of new, quoted, confirmed and completed requests,
  pipeline value, latest requests and a monthly volume list.
- **Orders** (`/admin/orders.php`) — filter by text, status or type; open a request;
  export the current filter to CSV for Excel.
- **Order detail** (`/admin/order.php`) — full requirement, one-click WhatsApp/call/email,
  quick status buttons, and a status + internal-notes form. Notes are visible to the buyer
  on the tracking page, so keep internal pricing out of them.
- **Rates & grades** (`/admin/products.php`) — set the indicative ₹/kg rate, MOQ, pack
  sizes and badge per grade, and take a grade offline.
- **Account** (`/admin/account.php`) — change the admin username and password.

Order lifecycle: `new → contacted → quoted → confirmed → dispatched → completed`,
with `closed` / `rejected` as terminal states. The public tracking page draws the timeline
from the current status.

---

## Customising the site

- **Rates and copy** for grades live in the database. Edit them in *Admin → Rates & grades*,
  or reseed from `data/seed.php` (which holds the grade descriptions, features and the
  partner list used by the installer).
- **Adding a grade**: insert a row into `products` (slug, name, grade_size, suta,
  description, features JSON, moq_kg, indicative_rate, pack_sizes, image, badge, sort_order)
  and drop a photo in `assets/img/`. Pages and sitemaps pick it up automatically.
- **Colours and type** are CSS custom properties at the top of `assets/css/main.css`
  (`--moss`, `--sage`, `--gold`, `--paper`, …). Fonts are declared in `assets/css/fonts.css`.
- **Photos**: replace anything in `assets/img/`. See `CREDITS.md` for the licences of the
  bundled photography.

---

## Security notes

- `config.php`, `includes/`, `data/` and `.sql/.log/.lock/.md` files are blocked in `.htaccess`.
  On Nginx add equivalent `location` denies.
- All output is escaped; all SQL is parameterised.
- CSRF tokens on every state-changing form, honeypot field and per-IP rate limiting on the
  public order and tracking endpoints.
- Admin passwords are stored with `password_hash()`; sessions are regenerated on login.
- `display_errors` is off; errors go to the PHP error log.
- Keep `data/` outside the document root if your host allows it, otherwise rely on the
  `.htaccess` deny rules and verify with `https://your-domain/data/app.sqlite` (must 403/404).

**Backups**: the SQLite database is `data/app.sqlite` — copy it regularly (or use the
host's backup tool). With MySQL, export the database. Also keep `config.php` and `assets/`.

---

## Local development

Any PHP 8 server works. With the CLI:

```bash
php -S 127.0.0.1:8080 -t . router.php
```

If your CLI PHP has no `php.ini` (common on Windows), add the extension explicitly:

```bash
php -d extension=pdo_sqlite -S 127.0.0.1:8080 -t . router.php
```

`router.php` mimics the Apache rewrite rules so pretty URLs, `/sitemap.xml` and the admin
directory index work locally. It is not used in production.

---

## File layout

```
config.sample.php      configuration template (copy to config.php)
.cpanel.yml            cPanel Git Version Control deployment tasks
install.php            one-time installer — delete after use
router.php             local dev router (php -S only)
sitemap.php            dynamic XML sitemap served as /sitemap.xml
api/orders.php         public quote-request endpoint (JSON or form post)
admin/
  index.php            sign-in + overview dashboard
  orders.php           filterable order queue
  order.php            order detail, status and notes
  products.php         rates, MOQ and packing editor
  account.php          admin credentials
  export.php           CSV export
  logout.php
includes/
  bootstrap.php        config, session, headers, error handling
  db.php               PDO connection and schema installer
  functions.php        escaping, money, CSRF, flash, rate limits, catalog helpers
  mailer.php           order notification + buyer acknowledgement
  polyfill.php         mbstring fallbacks
  header.php           <head>, topbar, navigation
  footer.php           footer and scripts
assets/
  css/main.css         design system and all page styles
  css/fonts.css        self-hosted @font-face rules
  js/main.js           nav, reveals, FAQ, estimator, AJAX form
  img/                 photography and logo files
  fonts/               Fraunces + Inter woff2
  favicon/             favicon.ico, apple-touch-icon, manifest
data/
  schema.sqlite.sql
  schema.mysql.sql
  seed.php             catalogue + partner seed data
```

---

## Deploying with cPanel Git Version Control

The repository ships a `.cpanel.yml`, so cPanel's *Git Version Control* can deploy it
(<https://docs.cpanel.net/knowledge-base/web-services/guide-to-git-deployment/>).

**First deployment**

1. **cPanel → Files → Git Version Control → Create**.
2. *Repository Path*: `/home/USER/public_html` — the clone target must be the domain's
   document root, because the app uses absolute paths (`/assets/...`, `/api/...`) and ships
   its own `.htaccess`. The directory must be **empty**; if `public_html` already has files,
   back them up, move them out, then clone.
3. *Repository URL*: `https://github.com/sharmaispinku/shagun-fox-nuts.git`, *Branch*: `main`.
4. If you later make the repository **private**, cPanel needs a deploy key: create one under
   *cPanel → Security → SSH Access → Manage SSH Keys*, then paste the public key into
   *GitHub → repo → Settings → Deploy keys* (tick *Allow write access* if you want the
   server to push as well as pull).
5. When the clone finishes, finish the app setup **once**:
   - *File Manager* → copy `config.sample.php` to `config.php`, edit the database block
     (the MySQL credentials are not in the repository, by design).
   - Import `database.sql` through phpMyAdmin, or open `/install.php` once.
   - Create an empty file `data/installed.lock` (File Manager → + File). This neutralises
     `install.php` permanently without deleting it, so future pulls never conflict.
   - Confirm `data/` is writable (typically `755`).
6. Open the domain, sign in at `/admin/` and change the default password.

**Every update after that**

1. Work locally, then `git add`, `git commit`, `git push`.
2. cPanel → *Git Version Control* → **Manage** → pick the repository → **Pull or Deploy** tab:
   - **Update from Remote** — fetches and applies the new commit to the cPanel working copy.
   - **Deploy HEAD Commit** — runs the `.cpanel.yml` tasks (here: ensure `data/` exists).
3. Reload the page. `assets/*.css|js|img` are versioned with `filemtime`, so no cache purge is needed.

**Rules that keep pulls clean**

- Never edit files on the server — change them locally and push. A dirty working tree blocks
  *Update from Remote*; if it happens, restore with `git checkout -- .` in *Terminal*.
- `config.php`, `database.sql`, `data/*.sqlite` and `data/installed.lock` are git-ignored.
  Pulls never overwrite them, which is why your live credentials and orders survive deploys.
- Database changes (new grades, schema edits) are **not** applied by Git. Re-import the
  changed SQL, or edit rates in *Admin → Rates & grades*.
- After deleting a file locally, `git push` removes it on the server on the next pull — check
  the diff before you deploy.

**Optional: scheduled pulls.** If you would rather deploy on a timer, add a cron job
(*cPanel → Cron Jobs*) such as:

```bash
/usr/bin/git -C /home/USER/public_html pull --ff-only origin main
```

Manual deploys through the Git Version Control screen are safer; use cron only if you accept
automatic updates.

---

## Nginx (optional)

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ ^/(includes|data)/ { deny all; }
location ~* \.(sql|log|lock|md)$ { deny all; }
location /sitemap.xml { rewrite ^ /sitemap.php last; }
location /product/([a-z0-9-]+)/ { rewrite ^ /product/$1.php?slug=$1 last; }
location ~* \.(css|js|jpg|png|webp|woff2|ico)$ { expires 1y; add_header Cache-Control "public, immutable"; }
```

---

## Photography credits

See `CREDITS.md`. All bundled photography comes from Wikimedia Commons under CC0,
CC BY or CC BY-SA licences. Replace with your own product photography before launch —
the images are representative of makhana and *Euryale ferox*, not of specific Shagun lots.