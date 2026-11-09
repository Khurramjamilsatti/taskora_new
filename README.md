# Taskora — Services Platform (Core PHP + MySQL)

A full services marketplace website with an animated public site, a customer and provider portal, and a CMS admin dashboard.

**Stack:** Core PHP (no framework) · MySQL / MariaDB · HTML5 · CSS3 · JavaScript · jQuery · AJAX
**Tested on:** XAMPP with PHP 7.3 and MariaDB 10.1. It also runs on PHP 7.2–8.x and MySQL 5.7+.

---

## 1. Install on XAMPP

### Step 1: Copy the files
Put the `taskora` folder in your XAMPP web folder:

```
C:\xampp\htdocs\taskora\
```

### Step 2: Start the servers
Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**.

### Step 3: Create the database (choose ONE option)

**Option A: Import the SQL file (recommended)**
1. Open <http://localhost/phpmyadmin>
2. Click the **Import** tab.
3. Choose `taskora/database.sql`, then click **Import** (or **Go**).
   The file creates the `taskora` database by itself, so you don't need to create it first.

**Option B: One-click installer**
1. Open <http://localhost/taskora/install.php>
2. Click **Install now**.

> **Warning:** both options replace any existing Taskora tables.

### Step 4: Open the site

| What | URL |
|---|---|
| Website | <http://localhost/taskora/> |
| Admin CMS | <http://localhost/taskora/admin/> |
| Customer / Provider portal | <http://localhost/taskora/account/login.php> |

### Default logins

| Role | Email | Password |
|---|---|---|
| Admin | `admin@taskora.digital` | `Admin@123` |
| Demo customer | `customer@taskora.digital` | `Demo@123` |
| Demo provider | `provider@taskora.digital` | `Demo@123` |

**Change the admin password straight away:** Admin → **Admin Users** → *Change my password*. The dashboard shows a red warning until you do.

### Database settings
If your MySQL user or password isn't the XAMPP default (`root` with no password), edit `config.php`:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'taskora');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## 2. What's included

### Public website
- **Home page** with 20 animated sections:
  - Hero with an app mock-up, stats, and Who We Are
  - Customer journey (9 steps)
  - Services directory (16 categories, live search)
  - Market Store (price table, audiences, categories, how it works)
  - Knowledge base and cost estimator (AJAX)
  - Planning tools and customer dashboard preview
  - Ratings and testimonials slider, career path and level ladder
  - Business solutions (tabs) and community marketplace
  - Trust & safety and who we serve
  - Business model (animated donut chart), become a professional, vision, and app download
- **Catalogue page:** 15 groups and 300+ specialisations in searchable accordions, the 16 verification checks, a sample verified profile, and the quality promise.
- **Service pages:** one SEO page per category (`service.php?slug=…`) with Service schema markup.
- **Forms:** Booking, Company registration, Feedback, Complaint, Refund and Insurance claim, all submitted by AJAX with validation and a reference number.
- **Contact page**, **About / Privacy / Terms** pages, **sitemap** (`sitemap.php`) and `robots.txt`.
- **Animations:**
  - Preloader with a logo draw, scroll progress bar, split-word heading reveals and staggered scroll reveals
  - Animated counters, magnetic buttons, spotlight hover cards and a custom cursor (desktop only)
  - Marquees, a parallax background, a drawn flow line, an animated donut, and word-by-word highlighting on scroll
  - Back-to-top button with a progress ring and a WhatsApp button
  - All motion respects the visitor's "reduce motion" setting.

### Customer and provider portal (`/account/`)
- Sign-up and sign-in for **customers** and **professionals**. New professionals start as *pending* until an admin verifies them.
- Full booking lifecycle, the same as the reference app:
  **Requested → Provider joined → Quotation → Deal locked → In progress → Completed → Feedback**
- Quotes and counter-offers with a full price history.
- **In-booking chat** that unlocks once the deal is locked (refreshes automatically).
- Detailed ratings (overall plus 5 criteria), notifications, profile, password change and a dark mode.
- Every step is checked on the server: a user can only take actions allowed for their role and the booking's current status. Two providers can't claim the same job.

### Admin CMS (`/admin/`)
| Menu | What you can do |
|---|---|
| Dashboard | Totals, a 14-day bookings chart, top categories, latest activity |
| Bookings | Filter and search, assign a provider, change the status, add notes, read the chat log, export to CSV |
| Form Submissions | Read every form, set the status, add notes, reply by email or WhatsApp |
| Customers & Providers | **Verify** new professionals, suspend or delete accounts |
| Subscribers | Newsletter list and CSV export |
| Page Sections | **Edit every heading, paragraph, list, number and card** on the Home and Catalogue pages, or hide a section |
| Service Categories | Add, edit or delete categories, services, icons and SEO fields |
| Catalogue Groups | Edit the specialisations shown on the Catalogue page |
| Testimonials / Pages | Manage reviews, and the About, Privacy and Terms pages |
| Settings & SEO | Logo and favicon upload, phone, WhatsApp, email, address, cities, social and app links, SEO title, description and keywords, Google Analytics ID |
| Admin Users | Add or remove admins and change your password |

---

## 3. Editing content (quick guide)

- **Change a heading:** Admin → Page Sections → *Edit*. A new line in the title starts a new line on the site. The **Emphasis phrase** is the part shown in gold italics, and it must match the end of the title exactly.
- **Lists** (for example the ticker or the guides) are edited as **one item per line**.
- **Cards and tables** (for example prices or steps) use the **row editor**. Use *Add row*, ↑ to move a row up, and 🗑 to delete it. Where a field says so, separate multiple items with commas.
- **Logo:** Settings → *Logo image*. Leave it empty to use the built-in Taskora logo.
- **Service cities:** Settings → *Service cities*, one per line. They are used by every form and account.

---

## 4. Folder structure

```
taskora/
├── index.php            Home page
├── catalogue.php        Master catalogue
├── service.php          Category detail pages (SEO)
├── forms.php            Booking / company / feedback / complaint / refund / insurance
├── contact.php  page.php  sitemap.php  robots.txt
├── install.php          One-click installer (delete on a live server)
├── database.sql         Full database dump
├── config.php           Database settings
├── ajax/                estimate.php, form.php, subscribe.php
├── account/             Customer & provider portal
├── admin/               CMS dashboard
├── includes/            bootstrap (DB, helpers, security), layout, icons, form definitions
├── install/             schema.sql + seed.php (used by install.php)
├── assets/css/          style.css (website), dashboard.css (portal + admin)
├── assets/js/           main.js (animations), dashboard.js, admin.js
├── assets/vendor/       jQuery 3.7.1 (stored locally, no CDN needed)
└── uploads/             Logo and favicon uploads (script running is blocked here)
```

---

## 5. Security features
- PDO prepared statements everywhere (protects against SQL injection)
- CSRF tokens on every form and AJAX request
- Passwords stored with `password_hash()` (bcrypt)
- All output escaped (protects against XSS)
- Login rate-limiting and a session ID refresh after login
- Hidden honeypot field on public forms to catch spam bots
- Image uploads checked by file type and content, and scripts can't run in `/uploads/`
- `config.php`, `/includes/` and `/install/` can't be opened from a browser (`.htaccess`)

---

## 6. Going live checklist
1. Upload the files, then import `database.sql` with your host's phpMyAdmin.
2. Update `config.php` with the live database details, and set `APP_DEBUG` to `false`.
3. **Delete `install.php`** and the `install/` folder.
4. Change all default passwords, and delete or suspend the demo customer and provider.
5. Settings → enter the real phone, WhatsApp, email, address, social links and app store links.
6. In `robots.txt`, change `Sitemap: /sitemap.php` to the full URL, for example `https://yourdomain.com/sitemap.php`.
7. Turn on HTTPS. Email alerts for new form submissions use PHP `mail()`, so make sure your host supports it.

---

## 7. Content note
The structure, service categories, catalogue, pricing figures and colour palette follow the reference site (taskora.digital). The headings, descriptions and sample testimonials were rewritten. If you own the original copy, paste it in through **Admin → Page Sections**.
