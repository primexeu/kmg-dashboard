# KMG · Zumbrock Dashboard

---

## 📌 Overview

This project implements the **KMG Zumbrock Dashboard** for **KMG Zumbrock** — a German retailer group specializing in kitchens and furniture. The dashboard automates **supplier order confirmation matching** and provides agents with a centralized interface to:

- Track automated matches vs. manual interventions
- Review exceptions and errors
- Monitor processing pipeline tasks
- Manage invoices and documentation

**Tech Stack:**
- **Laravel 11**
- **Filament v3** (Admin Panel)
- **TailwindCSS v3.4.13**
- **Vite** (asset bundler)

---

## 🚀 Getting Started

### Deploying to Render

This repository now includes all artifacts required for a Render deployment (`Dockerfile`, `render-entrypoint.sh`, and `render.yaml`). To launch the stack:

1. Push the repo to a Git remote Render can access.
2. In Render, click **New → Blueprint** and select this repo so it reads `render.yaml`, or create two Docker services manually (one web, one worker) that both build from this repository.
3. Before deploying, add the following environment variables to each service:

| Key | Purpose |
|-----|---------|
| `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=<https://your-domain>` | Standard Laravel app context. |
| `APP_KEY` | Generate once with `php artisan key:generate --show` and paste the value (keep it secret). |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Point to your managed MySQL instance (RDS, Render MySQL, PlanetScale, etc.). |
| `QUEUE_CONNECTION` | `database`, `redis`, etc. Required for the queue worker. |
| `CACHE_DRIVER`, `SESSION_DRIVER` | Usually `file` or `redis`. |
| `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT` | Only if you offload cache/session/queue to Redis. |
| `PYTHON_API_URL` | URL of the Python comparison backend (`https://kmg-backend-t1wf.onrender.com`). |
| `RUN_DB_MIGRATIONS` | Defaults to `true`. Set to `false` if you manage migrations manually. |
| `CONTAINER_ROLE` | Leave unset for the web app. Set to `worker` on the Render Worker service so it runs `queue:work`. |

Optional mail/storage variables (`MAIL_MAILER`, `AWS_*`, etc.) can be added as needed.

Render build + local verification:

```bash
# Build (same command Render runs)
docker build -t kmg-laravel .

# Smoke test locally
docker run --rm -p 8080:8080   -e APP_KEY=$(php artisan key:generate --show)   -e APP_ENV=local   -e DB_CONNECTION=mysql   -e DB_HOST=host   -e DB_DATABASE=db   -e DB_USERNAME=user   -e DB_PASSWORD=pass   kmg-laravel
```

The `render-entrypoint.sh` script handles:
- Creating the storage symlink and caching config/routes/views.
- Generating a temporary `APP_KEY` if you forget to set one (Render should still provide a permanent key).
- Running `php artisan migrate --force` every deploy (toggle with `RUN_DB_MIGRATIONS=false`).
- Switching roles: `CONTAINER_ROLE=worker` makes the container run `php artisan queue:work`; otherwise it boots nginx + PHP-FPM.


### 1️⃣ Install Dependencies

```bash
composer install
npm install
```

### 2️⃣ Configure Environment

1. Copy `.env.example` to `.env`
2. Update your database connection and app URL
3. Generate application key:

```bash
php artisan key:generate
```

### 3️⃣ Run Migrations

Set up all required database tables:

```bash
php artisan migrate
```

**Main Tables:**
| Table | Description |
|-------|-------------|
| `orders` | Purchase orders |
| `order_confirmations` | Supplier confirmations |
| `order_matches` | Matching results |
| `exceptions` | Review queue for mismatches |
| `invoices` | AP integration |
| `documents` | Uploaded files |
| `users` | Admins/agents |

### 4️⃣ Seed Demo Data

Populate your database with realistic demo data:

```bash
php artisan db:seed --class=KmgDemoSeeder
```

**Seeder Includes:**
- 20+ sequential orders (e.g., `ORD-00001`)
- Random supplier confirmations with confidence scores
- Sample matches, exceptions, and invoices

---

## 🖥 Development Workflow

### Start the Backend

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Start the Frontend

```bash
npm run dev
```

**Access the Dashboard:**
[http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 📂 Key Components

### Filament Resources
| Resource | Description |
|----------|-------------|
| **Orders** | CRUD + links to matches/exceptions |
| **Order Confirmations** | Supplier replies |
| **Order Matches** | Result of matching pipeline |
| **Exceptions** | Errors requiring human review |
| **Invoices** | AP management |
| **Users** | Admin accounts |

### Filament Widgets
| Widget | Description |
|--------|-------------|
| **QuickStart** | Welcome + shortcut buttons |
| **IntroCard** | Explanation of OrderMatch workflow |
| **KPI Overview** | Key metrics row |
| **Exception Snapshot** | Recent issues |
| **Throughput Chart** | Auto-matches trend |

---

## 🔍 Global Search

- All resources are globally searchable
- Find orders, confirmations, exceptions, invoices, and users
- Accessible via **Ctrl+K / Cmd+K**

---

## 🌐 Branding
- **Brand Name:** KMG · Zumbrock
- **Logo:** `resources/views/filament/logo.blade.php`
- **Favicon:** `public/images/favicon.svg`
- **Welcome Page:** Gradient hero + "Login to Dashboard" button

---

## 🛠 Troubleshooting

### Seeder Issues
- **Duplicate Order Numbers:** Reset auto-increment after truncating the `orders` table:

```sql
TRUNCATE orders;
```

- **Foreign Key Errors:** Run migrations in order: `orders` → `order_confirmations` → `order_matches` → `exceptions`

- **Search Not Working:** Ensure resources implement `getGloballySearchableAttributes()` and `getGlobalSearchResultUrl()`

---

## 👤 Code Conventions
- Use `OrderMatch` instead of `Match` (reserved PHP keyword)
- Alias `Exception` model as `ExceptionModel` in PHP
- Always use `Auth::id()` for `author_id` / `updated_by`
- Store documentation in DB with author & updater tracking

---

## 📄 License
[MIT](LICENSE)
