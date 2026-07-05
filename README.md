# ✂️ CutURL

**Local-first Laravel URL shortener using JSON storage.**

CutURL is a small, polished, open-source URL shortener that runs entirely on
**your own machine**. It turns long URLs into short, memorable ones and stores
everything in a single local JSON file — **no account, no cloud, no database, no
build step.** Clone it, run one command, and you have a working shortener at
`http://localhost:8000`.

It was built to be genuinely useful for local development (sharing tidy links
across your own tools and notes, testing redirect behaviour, seeding demos)
while staying small enough to read end-to-end in a single sitting.

<p>
  <img alt="PHP 8.3+" src="https://img.shields.io/badge/PHP-8.3%2B-777bb4">
  <img alt="Laravel 13" src="https://img.shields.io/badge/Laravel-13-ff2d20">
  <img alt="No database" src="https://img.shields.io/badge/database-none%20(JSON)-2ea44f">
  <img alt="Tests" src="https://img.shields.io/badge/tests-26%20passing-2ea44f">
  <img alt="License MIT" src="https://img.shields.io/badge/license-MIT-blue">
</p>

> [!IMPORTANT]
> **CutURL is not a public hosted shortener by default.** The short links it
> generates (e.g. `http://localhost:8000/abc123`) only work while the Laravel
> app is running locally on your machine. They are perfect for local testing,
> development, and self-hosting — but they are **not** shareable with the wider
> internet unless *you* choose to deploy CutURL to a server yourself (see
> [Limitations](#️-limitations)).

---

## 📑 Table of contents

- [Why CutURL?](#-why-cuturl)
- [Features](#-features)
- [Tech stack](#-tech-stack)
- [Screenshots](#️-screenshots)
- [Installation](#-installation)
- [Running locally](#️-running-locally)
- [Running tests](#-running-tests)
- [How it works (architecture)](#-how-it-works-architecture)
- [How local JSON storage works](#️-how-local-json-storage-works)
- [Routes](#-routes)
- [Configuration](#️-configuration)
- [Security](#-security)
- [Example usage](#-example-usage)
- [Folder structure](#-folder-structure)
- [Technical decisions](#-technical-decisions)
- [Limitations](#️-limitations)
- [Future improvements](#-future-improvements)
- [Contributing](#-contributing)
- [License](#-license)

---

## 🤔 Why CutURL?

Most URL shorteners are SaaS products: you sign up, your links live on someone
else's servers, and your click data is tracked in the cloud. CutURL takes the
opposite stance.

- **Local-first.** Your links live in a plain JSON file on your disk. You can
  open it, edit it, back it up, or delete it — no export tool required.
- **Zero infrastructure.** No MySQL, no PostgreSQL, not even SQLite. No message
  queue, no Redis, no Node build pipeline. If you have PHP, you can run it.
- **Readable.** The whole app is a handful of small, well-commented files. It's
  a great reference for how to build a clean Laravel feature without reaching
  for a database.
- **Private by construction.** Nothing leaves your machine. There is no
  telemetry and no third-party analytics.

If you want a hosted, multi-user, cloud shortener, CutURL is intentionally *not*
that — and the README is honest about it.

---

## ✨ Features

- 🔗 **Shorten any URL** through a clean, server-validated form.
- ✏️ **Custom aliases** — choose your own short code (`/my-link`) or let CutURL
  generate a unique 6-character one for you.
- ⏳ **Optional expiration dates** — a link stops redirecting after a moment you
  pick, and shows a friendly "expired" page instead.
- 📊 **Dashboard** — every link in one table: destination, short code, click
  count, active/expired status, creation date, and expiry.
- 🔍 **Search & filter** — find links by original URL or short code. Works both
  **server-side** (bookmarkable `?q=` URLs) *and* **instantly in the browser**
  as you type, with no page reload.
- 📈 **Click tracking** — every successful redirect increments a per-link
  counter and records a `last_clicked_at` timestamp.
- 📋 **Copy to clipboard** with a satisfying "Copied ✓" confirmation animation,
  using the modern Clipboard API with a legacy fallback.
- 🗑️ **Delete** individual links, or **clear all** links — both guarded by a
  JavaScript confirmation dialog.
- 🧯 **Clean, on-brand error pages** for expired links, unsafe links, and 404s —
  never a raw stack trace.
- 🌗 **Light / dark theme** toggle that remembers your preference in
  `localStorage` and respects your OS setting on first visit.
- 🔒 **Safe by design** — server-side URL validation, `http(s)`-only redirects,
  CSRF protection, per-IP rate limiting, and reserved-word protection.
- 🗄️ **Zero database** — everything lives in one human-readable JSON file.
- ♿ **Accessible & responsive** — semantic HTML, skip link, ARIA labels, and a
  layout that works from phone to desktop.

---

## 🧰 Tech stack

| Layer      | Choice                                                     |
|------------|------------------------------------------------------------|
| Backend    | **Laravel 13** on **PHP 8.3+**                             |
| Templating | **Blade** (server-rendered HTML)                          |
| Frontend   | **Plain HTML + CSS** — no Tailwind, no Bootstrap          |
| Scripting  | **Vanilla JavaScript** — no React, no bundler, no build   |
| Storage    | **Local JSON file** — no MySQL / PostgreSQL / SQLite      |
| Validation | Laravel **FormRequest**                                    |
| Tests      | **PHPUnit** — Feature (HTTP) + Unit (storage)             |
| Tooling    | **Laravel Pint** for code style                           |

There is deliberately **no `package.json` build requirement** — the CSS and JS
are served as static files from `public/`, so `php artisan serve` is all you
ever need to run.

---

## 🖼️ Screenshots

> _Add your own screenshots here once you run the app locally._

| Homepage | Dashboard |
|----------|-----------|
| `docs/screenshot-home.png` | `docs/screenshot-dashboard.png` |

---

## 🚀 Installation

**Requirements:** PHP **8.3+** and [Composer](https://getcomposer.org/).
No Node.js, no npm, no database server.

```bash
# 1. Clone the repository
git clone https://github.com/your-username/cuturl.git
cd cuturl

# 2. Install PHP dependencies
composer install

# 3. Create your environment file and generate an app key
cp .env.example .env
php artisan key:generate
```

> **macOS:** you can install PHP + Composer in one line with Homebrew:
> ```bash
> brew install php composer
> ```
> **Ubuntu/Debian:** `sudo apt install php-cli php-mbstring composer unzip`

That's the entire setup. There is nothing to migrate and no services to start.

---

## ▶️ Running locally

```bash
php artisan serve
```

Then open **http://localhost:8000** in your browser.

| Page                      | URL                               |
|---------------------------|-----------------------------------|
| Homepage (create links)   | `http://localhost:8000/`          |
| Dashboard (manage links)  | `http://localhost:8000/dashboard` |
| A generated short link     | `http://localhost:8000/abc123`    |

Visiting a short link redirects to the original URL and increments its click
count inside the JSON file. To stop the server, press `Ctrl+C`.

Want a different port? `php artisan serve --port=9000` (the short-link base URL
follows automatically).

---

## 🧪 Running tests

```bash
php artisan test
```

Expected output: **26 passing tests, 170 assertions.**

The suite is split into HTTP-level *Feature* tests and storage-level *Unit*
tests, and covers:

| # | Behaviour tested                                   |
|---|----------------------------------------------------|
| 1 | Homepage loads                                     |
| 2 | A valid URL can be shortened                       |
| 3 | An invalid URL is rejected                         |
| 4 | A `javascript:` / non-http URL is rejected         |
| 5 | An empty URL is rejected                           |
| 6 | A custom alias becomes the short code              |
| 7 | A duplicate custom alias is rejected               |
| 8 | Invalid alias characters are rejected              |
| 9 | Reserved words (e.g. `dashboard`) are rejected     |
| 10 | A short code redirects to its original URL        |
| 11 | The click count increments after a redirect       |
| 12 | Expired links do **not** redirect (410 page)      |
| 13 | An unknown short code returns a 404 page           |
| 14 | A hand-edited unsafe stored URL is not followed    |
| 15 | Deleting a link works                             |
| 16 | Clearing all links works                          |
| 17 | The JSON file is created automatically if missing |
| 18 | Malformed JSON degrades gracefully (no crash)     |

Tests run against an **isolated, throwaway JSON file** created per test, so your
real links in `storage/app/cuturl/links.json` are never touched.

---

## 🏗 How it works (architecture)

CutURL keeps a strict separation between HTTP handling and storage. There is no
model layer — instead a single **`LinkStorageService`** owns every read and
write to the JSON file, so the data shape stays consistent everywhere.

```
Browser ──▶ routes/web.php ──▶ Controller ──▶ LinkStorageService ──▶ links.json
                                   │
                                   └── ShortenLinkRequest (validation)
```

**Creating a short link** (`POST /shorten`):

1. `ShortenLinkRequest` validates the URL (must be non-empty `http`/`https`) and
   the optional alias (allowed characters, not reserved, not already taken).
2. `LinkController@shorten` calls `LinkStorageService::create()`, which either
   uses your alias or generates a collision-free 6-character code.
3. The new link is appended to `links.json` and flashed back to the homepage,
   which renders the result card with a copy button.

**Following a short link** (`GET /{code}`):

1. The catch-all route (declared **last**, constrained to `[A-Za-z0-9_-]+`)
   hands off to `RedirectController`.
2. Unknown code → clean **404** page. Expired link → clean **410 "expired"**
   page (no redirect, no click counted).
3. As a final safety net, the stored URL's scheme is re-checked; anything other
   than `http`/`https` shows the **"invalid link"** page instead of redirecting.
4. Otherwise the click count is incremented and the user is redirected to the
   original URL.

---

## 🗂️ How local JSON storage works

CutURL persists everything to a single file:

```
storage/app/cuturl/links.json
```

- The folder and file are **created automatically** the first time the app reads
  or writes a link — there is nothing to set up, and a fresh clone just works.
- Writes are **pretty-printed** and use an **exclusive file lock** (`LOCK_EX`)
  so concurrent local requests don't corrupt the file.
- If the file is missing, empty, or contains **malformed JSON**, CutURL logs a
  warning and **degrades gracefully to an empty list** instead of crashing the
  whole application.
- The file is **git-ignored**, so your personal links never end up in version
  control (the folder itself is kept via a `.gitignore` inside it).

Each link is stored exactly like this:

```json
{
    "id": "81c6b310-b082-409a-9cdd-b2e3fdc2bae3",
    "original_url": "https://laravel.com/docs",
    "short_code": "docs",
    "custom_alias": "docs",
    "click_count": 2,
    "expires_at": null,
    "last_clicked_at": "2026-07-05T18:55:07+00:00",
    "created_at": "2026-07-05T18:55:07+00:00",
    "updated_at": "2026-07-05T18:55:07+00:00"
}
```

| Field             | Meaning                                                        |
|-------------------|----------------------------------------------------------------|
| `id`              | UUID, used for delete operations                               |
| `original_url`    | The destination the short link redirects to                    |
| `short_code`      | The path that resolves the link (`/docs`)                      |
| `custom_alias`    | The alias you chose, or `null` if auto-generated               |
| `click_count`     | Number of successful redirects                                 |
| `expires_at`      | ISO-8601 expiry, or `null` for a link that never expires       |
| `last_clicked_at` | ISO-8601 timestamp of the most recent redirect, or `null`      |
| `created_at` / `updated_at` | ISO-8601 lifecycle timestamps                        |

---

## 🌐 Routes

| Method   | Path            | Name             | Purpose                                  |
|----------|-----------------|------------------|------------------------------------------|
| `GET`    | `/`             | `home`           | Homepage with the shortening form        |
| `POST`   | `/shorten`      | `shorten`        | Create a short link (rate-limited)       |
| `GET`    | `/dashboard`    | `dashboard`      | List / search all stored links           |
| `DELETE` | `/links/{id}`   | `links.destroy`  | Delete a single link                     |
| `DELETE` | `/links`        | `links.clear`    | Delete **all** links                     |
| `GET`    | `/{code}`       | `redirect`       | Resolve a short code (catch-all, last)   |

---

## ⚙️ Configuration

All CutURL-specific settings live in **`config/cuturl.php`**:

| Key                   | Default | Description                                        |
|-----------------------|---------|----------------------------------------------------|
| `storage_path`        | `storage/app/cuturl/links.json` | Where links are stored     |
| `code_length`         | `6`     | Length of auto-generated short codes               |
| `code_alphabet`       | `a–z A–Z 0–9` | Character set for generated codes            |
| `alias_pattern`       | `/^[A-Za-z0-9_-]+$/` | Allowed custom-alias characters       |
| `alias_max_length`    | `64`    | Maximum custom-alias length                        |
| `reserved_codes`      | `dashboard, shorten, links, admin, login, register, api, assets, storage, up` | Codes that can never be used |
| `shorten_rate_limit`  | `20`    | Max shorten requests per minute, per IP            |

Sessions and cache use the `file` driver (`.env.example`) so the app never
touches a database for anything.

---

## 🔐 Security

CutURL is designed to be safe to run locally, with defense in depth:

- **Server-side URL validation.** Only non-empty `http://` / `https://` URLs
  with a host are accepted. `javascript:`, `data:`, `file:` and friends are
  rejected at the form.
- **Redirect-time re-check.** Even if `links.json` were edited by hand to contain
  an unsafe scheme, the redirect controller re-validates and refuses to follow it
  — you get the "invalid link" page instead.
- **Output escaping.** All dynamic values are rendered through Blade's escaped
  `{{ }}` syntax, preventing stored-XSS via a crafted URL.
- **CSRF protection** on every state-changing form (`shorten`, delete, clear).
- **Rate limiting** on `POST /shorten` (per IP, configurable).
- **Reserved words** can't be claimed as aliases, so short codes never shadow app
  routes or static assets.
- **No secrets in the repo.** `.env` (with your `APP_KEY`) and `links.json` are
  git-ignored; only the safe `.env.example` template is committed.

> Note: CutURL has **no authentication** by design. Anyone who can reach the app
> can create and delete links. Do not expose it on a public network without
> adding your own auth layer.

---

## 💡 Example usage

1. Start the server: `php artisan serve`.
2. Go to `http://localhost:8000` and paste a long URL, e.g.
   `https://en.wikipedia.org/wiki/URL_shortening`.
3. _(Optional)_ Add a custom alias like `wiki`, and/or an expiration date.
4. Click **Shorten URL** — you get `http://localhost:8000/wiki`.
5. Hit **Copy** (watch it flash "Copied ✓"), then open the link in a new tab —
   it redirects, and the click count on the **Dashboard** goes up.
6. On the dashboard, type in the search box to filter instantly, or delete links
   you no longer need.

---

## 📁 Folder structure

```
CutURL/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LinkController.php        # home, shorten, dashboard, delete, clear
│   │   │   └── RedirectController.php    # /{code} → redirect / expired / invalid / 404
│   │   └── Requests/
│   │       └── ShortenLinkRequest.php    # URL + alias validation
│   ├── Providers/
│   │   └── AppServiceProvider.php        # rate limiter + service binding
│   └── Services/
│       └── LinkStorageService.php        # ALL JSON read/write logic (the core)
├── config/
│   └── cuturl.php                        # storage path, code length, reserved words
├── public/
│   ├── css/app.css                       # plain CSS (themed, responsive)
│   └── js/app.js                         # vanilla JS (copy, confirm, filter, theme)
├── resources/views/
│   ├── layouts/app.blade.php             # shared layout / navbar / footer
│   ├── home.blade.php                    # homepage + shorten form + result card
│   ├── dashboard.blade.php               # links table + search + clear all
│   └── errors/
│       ├── 404.blade.php                 # unknown short code
│       ├── expired.blade.php             # expired link (HTTP 410)
│       └── invalid.blade.php             # unsafe / non-http destination
├── routes/web.php                        # all routes (redirect route is LAST)
├── storage/app/cuturl/                   # ← links.json lives here (auto-created)
└── tests/
    ├── Feature/                          # HTTP-level tests
    └── Unit/                             # LinkStorageService tests
```

---

## 🧠 Technical decisions

- **JSON file instead of a database.** The brief is a *local-first* tool, so a
  database (even SQLite) would add setup friction for no benefit at this scale. A
  single JSON file is transparent, portable, diff-able, and needs zero config.
- **A dedicated `LinkStorageService`.** All file access is funnelled through one
  class. Controllers stay thin, the JSON shape stays consistent, and the storage
  layer could later be swapped for SQLite **without touching the controllers**.
- **No Eloquent / migrations.** There is no database connection to configure.
  Sessions and cache use the `file` driver so the app never needs a DB — this is
  what makes "clone and run" genuinely true.
- **Catch-all redirect route declared last** and constrained to
  `[A-Za-z0-9_-]+`, so it can never shadow real routes (`/dashboard`,
  `/shorten`, …) or static assets.
- **Reserved words** are rejected as aliases to avoid route collisions.
- **Defense in depth on redirects.** URLs are validated on input *and* the scheme
  is re-checked at redirect time, so a hand-edited `javascript:` entry in the
  JSON file is never followed.
- **FormRequest validation** keeps controllers clean and yields precise,
  user-friendly error messages.
- **No front-end build step.** Static CSS/JS in `public/` means one command to
  run and nothing to compile — deliberately beginner-friendly and dependency-light.

---

## ⚠️ Limitations

- **Local by default.** Short links resolve only while `php artisan serve` (or
  your own deployment) is running. Closing the server makes them stop working.
  They are **not** public URLs unless you deploy CutURL yourself.
- **Single-file storage.** JSON is ideal for personal / local use but is not
  built for high write concurrency or millions of links. For that scale, use the
  planned optional SQLite mode.
- **No authentication.** By design — see [Security](#-security).
- **Basic analytics** — a click counter and a last-clicked timestamp, not a full
  analytics pipeline with charts and referrers.

---

## 🔭 Future improvements

- [ ] Optional **SQLite** storage mode for larger datasets.
- [ ] **QR code** generation for each short link.
- [ ] **Import / export** links (JSON / CSV).
- [ ] **Password-protected** links.
- [ ] **Analytics charts** (clicks over time, referrers).
- [ ] **Browser extension** to shorten the current tab.
- [ ] **Docker** support for one-command setup.
- [ ] A **public deployment guide** (Nginx / Caddy + a real domain).

---

## 🤝 Contributing

Contributions are welcome! This project is intentionally small and readable, so
it's a friendly place to make your first open-source PR.

1. Fork the repo and create a branch: `git checkout -b my-feature`.
2. Make your change and keep the style consistent: `./vendor/bin/pint`.
3. Make sure tests pass: `php artisan test`.
4. Open a pull request describing what and why.

Please open an issue first for larger changes so we can discuss the approach.

---

## 📄 License

Released under the [MIT License](LICENSE). Free to use, modify, and share.
