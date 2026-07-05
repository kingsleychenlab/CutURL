# ✂️ CutURL

**Local-first Laravel URL shortener using JSON storage.**

CutURL is a small, polished, open-source URL shortener that runs entirely on
your own machine. It turns long URLs into short ones and stores everything in a
single local JSON file — **no account, no cloud, no database.**

> [!IMPORTANT]
> **CutURL is not a public hosted shortener by default.** The short links it
> generates (e.g. `http://localhost:8000/abc123`) only work while the Laravel
> app is running locally on your machine. They are perfect for local testing,
> development, and self-hosting — but they are **not** shareable with the world
> unless *you* deploy CutURL to a server yourself (see
> [Limitations](#-limitations)).

---

## ✨ Features

- 🔗 **Shorten any URL** with a clean, validated form.
- ✏️ **Custom aliases** — pick your own short code (`/my-link`) or let CutURL
  generate a unique 6-character one.
- ⏳ **Optional expiration dates** — links stop working after a date you choose.
- 📊 **Dashboard** — see every link with its destination, click count, status
  (active / expired), creation date and expiry.
- 🔍 **Search & filter** — find links by original URL or short code (works both
  server-side *and* instantly in the browser).
- 📈 **Click tracking** — every redirect increments a local click counter.
- 📋 **Copy to clipboard** with a satisfying "Copied ✓" confirmation.
- 🗑️ **Delete** individual links or **clear all** links (with confirmation).
- 🧯 **Clean error pages** for expired links, unsafe links, and 404s.
- 🌗 **Light / dark theme** toggle (remembers your choice).
- 🔒 **Safe by design** — server-side URL validation, `http(s)`-only redirects,
  CSRF protection, rate limiting, and reserved-word protection.
- 🗄️ **Zero database** — everything lives in one JSON file you can read, back
  up, or delete.

---

## 🧰 Tech stack

| Layer      | Choice                                             |
|------------|----------------------------------------------------|
| Backend    | **Laravel 13** / **PHP 8.3+**                       |
| Templating | **Blade**                                          |
| Frontend   | **Plain HTML + CSS** (no Tailwind / Bootstrap)     |
| Scripting  | **Vanilla JavaScript** (no React / build step)     |
| Storage    | **Local JSON file** (no MySQL / PostgreSQL / SQLite) |
| Tests      | **PHPUnit** (Feature + Unit)                        |

---

## 🖼️ Screenshots

> _Add your own screenshots here once you run the app locally._

| Homepage | Dashboard |
|----------|-----------|
| `docs/screenshot-home.png` | `docs/screenshot-dashboard.png` |

---

## 🚀 Installation

**Requirements:** PHP 8.3+ and [Composer](https://getcomposer.org/).
No Node.js, npm, or database required.

```bash
# 1. Clone the repository
git clone https://github.com/your-username/cuturl.git
cd cuturl

# 2. Install PHP dependencies
composer install

# 3. Create your environment file and app key
cp .env.example .env
php artisan key:generate
```

> On macOS you can install PHP + Composer with Homebrew:
> `brew install php composer`

---

## ▶️ Running locally

```bash
php artisan serve
```

Then open **http://localhost:8000** in your browser.

- Homepage (create links): `http://localhost:8000/`
- Dashboard (manage links): `http://localhost:8000/dashboard`
- A short link looks like: `http://localhost:8000/abc123`

Visiting a short link redirects to the original URL and increments its click
count inside the JSON file.

---

## 🧪 Running tests

```bash
php artisan test
```

The suite covers homepage loading, URL validation, custom aliases, duplicate
alias rejection, redirects, click counting, expired links, 404s, deletion,
clearing all links, and automatic creation of the JSON storage file. Tests run
against a throwaway JSON file, so **your real links are never touched.**

---

## 🗂️ How local JSON storage works

CutURL persists everything to a single file:

```
storage/app/cuturl/links.json
```

- The folder and file are **created automatically** the first time the app reads
  or writes a link — there is nothing to set up.
- Writes are pretty-printed and use an exclusive file lock (`LOCK_EX`).
- If the file is missing, empty, or contains **malformed JSON**, CutURL logs a
  warning and degrades gracefully to an empty list instead of crashing.
- The file is **git-ignored** so your personal links never end up in version
  control.

Each link is stored as:

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

---

## 💡 Example usage

1. Start the server: `php artisan serve`.
2. Go to `http://localhost:8000` and paste a long URL, e.g.
   `https://en.wikipedia.org/wiki/URL_shortening`.
3. _(Optional)_ Add a custom alias like `wiki` and/or an expiration date.
4. Click **Shorten URL** — you get `http://localhost:8000/wiki`.
5. Click **Copy**, then open the short link in a new tab — it redirects, and the
   click count on the **Dashboard** goes up.

---

## 📁 Folder structure

```
CutURL/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LinkController.php        # home, shorten, dashboard, delete, clear
│   │   │   └── RedirectController.php    # /{code} → redirect / expired / 404
│   │   └── Requests/
│   │       └── ShortenLinkRequest.php    # URL + alias validation
│   ├── Providers/
│   │   └── AppServiceProvider.php        # rate limiter + service binding
│   └── Services/
│       └── LinkStorageService.php        # all JSON read/write logic (the core)
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
│       ├── expired.blade.php             # expired link
│       └── invalid.blade.php             # unsafe / non-http destination
├── routes/web.php                        # all routes (redirect route is last)
├── storage/app/cuturl/                   # ← links.json lives here (auto-created)
└── tests/
    ├── Feature/                          # HTTP-level tests
    └── Unit/                             # LinkStorageService tests
```

---

## 🧠 Technical decisions

- **JSON file instead of a database.** The brief is a *local-first* tool, so a
  database (even SQLite) would be overkill and add setup friction. A single JSON
  file is transparent, portable, diff-able, and requires zero configuration.
- **A dedicated `LinkStorageService`.** All file access is funnelled through one
  class. Controllers stay thin, the JSON shape stays consistent, and the storage
  layer could later be swapped for SQLite without touching the controllers.
- **No Eloquent / migrations.** There is no database connection to configure.
  Sessions and cache are set to the `file` driver in `.env.example` so the app
  never requires a database for *anything*.
- **Catch-all redirect route is declared last** and constrained to
  `[A-Za-z0-9_-]+`, so it can never shadow real routes (`/dashboard`,
  `/shorten`, …) or static assets.
- **Reserved words** (`dashboard`, `shorten`, `links`, `admin`, `api`, …) are
  rejected as aliases to avoid route collisions.
- **Defense in depth on redirects.** URLs are validated on input *and* the
  scheme is re-checked at redirect time, so a hand-edited `javascript:` entry in
  the JSON file will never be followed.
- **FormRequest validation** keeps controller code clean and gives precise,
  user-friendly error messages.

---

## ⚠️ Limitations

- **Local by default.** Short links resolve only while `php artisan serve` (or
  your own deployment) is running. Closing the server makes the links stop
  working. They are **not** public URLs unless you deploy CutURL yourself.
- **Single-file storage.** JSON is ideal for personal / local use but is not
  built for high write concurrency or millions of links. For that scale, use the
  planned optional SQLite mode.
- **No authentication.** By design — anyone who can reach the app can create and
  delete links. Do **not** expose it publicly without adding your own auth layer.
- **Click analytics are basic** (a counter + last-clicked timestamp), not a full
  analytics pipeline.

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

## 📄 License

Released under the [MIT License](LICENSE). Free to use, modify, and share.
