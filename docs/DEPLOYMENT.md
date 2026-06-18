# Deployment Guide — Rotikaya Media Sales Tracking System

This document explains how the system is deployed to a live, public URL for the
FYP2 evaluation, how to redo the deployment from scratch, and how to operate it
(migrations, demo data, rollback).

---

## 1. Chosen platform and why

**Platform: [Railway](https://railway.com) — Docker build + a persistent volume for SQLite.**

Three-sentence rationale:

1. Railway gives the easiest reliable path for a full PHP/Laravel app: it builds
   straight from a `Dockerfile`, exposes a public HTTPS URL automatically, keeps
   the container running (no slow "cold starts" like Render's free tier), and all
   environment variables are managed in a simple web dashboard.
2. We deploy with **SQLite on a Railway persistent volume** rather than a managed
   MySQL/Postgres because it requires **zero code or credential changes** (the app
   already uses SQLite), keeps everything in **one service**, and the volume makes
   the database file survive every redeploy.
3. The trade-off is that SQLite is single-writer (fine for a single-evaluator demo
   but not high concurrency) and Railway is not unlimited-free — it provides a
   one-time trial credit and then a low-cost Hobby plan (~US$5/month), which
   comfortably covers an evaluation window; if you ever need true multi-user scale,
   switch `DB_*` to a Railway managed MySQL (template lines are included in
   `.env.production.example`) with no code changes.

> Why a `Dockerfile` instead of Railway's auto-builder? It **guarantees** the PHP
> extensions DomPDF/Excel need (`gd`, `zip`, `mbstring`, `bcmath`, …) are present,
> and it runs database migration + one-time seeding + `storage:link` in a single
> deterministic startup script (`docker/entrypoint.sh`) — nothing is left to chance.

### SQLite vs hosted database — recommendation

| | SQLite on a volume (chosen) | Managed MySQL on Railway |
|---|---|---|
| Code changes | None | None (env only) |
| Services to manage | 1 | 2 (app + DB) |
| Persistence | Volume at `/data` | Native to DB service |
| Concurrency | Single writer (OK for demo) | Many writers |
| Setup effort | Lowest | Slightly more (wire `DB_*`) |

**Use SQLite for the evaluation.** Only switch to managed MySQL if your supervisor
specifically asks for a client/server database or you expect concurrent users.

---

## 2. What was added to the repository for deployment

All of these are **deployment configuration only** — no application feature or
business logic was changed.

| File | Purpose |
|---|---|
| `Dockerfile` | Two-stage build: Node compiles Vite assets, then a `php:8.3-cli` image installs PHP extensions, Composer deps, and the app. |
| `docker/entrypoint.sh` | Runs on every container start: creates the SQLite file on the volume, runs `migrate --force`, seeds demo data **once**, runs `storage:link`, caches config/routes/views, then starts the web server on Railway's `$PORT`. |
| `.dockerignore` | Keeps secrets (`.env`), local DB, `vendor/`, `node_modules/` and dev junk out of the image. |
| `railway.json` | Tells Railway to use the `Dockerfile` and health-check `/up`. |
| `.env.production.example` | Template listing every environment variable to set in Railway (no real secrets). |
| `bootstrap/app.php` | Added `trustProxies(at: '*')` so the app generates correct `https://` URLs behind Railway's proxy. |
| `docs/DEPLOYMENT.md` | This guide. |

Local development is unaffected: your `.env` still uses
`database/database.sqlite`, file sessions, and `php artisan serve` exactly as before.

---

## 3. Environment variables (set these in Railway)

Set these under **your service → Variables**. The full annotated list is in
`.env.production.example`. The important ones:

| Variable | Value | What it does |
|---|---|---|
| `APP_NAME` | `"Rotikaya Media Sales Tracking System"` | App display name. |
| `APP_ENV` | `production` | Production mode. |
| `APP_DEBUG` | `false` | **Security:** hides stack traces / sensitive errors. Must be `false`. |
| `APP_KEY` | `base64:....` | Encryption key. Generate locally (see below) — **do not** reuse the dev key or commit it. |
| `APP_URL` | `https://<your>.up.railway.app` | Your live URL (set after the domain is generated, then redeploy). |
| `DB_CONNECTION` | `sqlite` | Use SQLite. |
| `DB_DATABASE` | `/data/database.sqlite` | Path **on the persistent volume** — survives redeploys. |
| `SESSION_DRIVER` | `database` | Sessions stored in the DB. |
| `CACHE_STORE` | `database` | Cache stored in the DB. |
| `QUEUE_CONNECTION` | `database` | Queue stored in the DB (no worker needed for the demo). |
| `FILESYSTEM_DISK` | `local` | Default file disk. |
| `MAIL_MAILER` | `log` | Emails are written to logs, **not** sent (demo). |
| `MAIL_FROM_ADDRESS` | `hello@rotikaya.com` | From address on generated mail. |
| `WHATSAPP_DRIVER` | `simulator` | WhatsApp is simulated — **no real messages are sent.** Required for the demo. |
| `WHATSAPP_SIMULATE_MODE` | `always_success` | Simulator always "succeeds". |
| `COMPANY_NAME` | `"Rotikaya Media Sdn Bhd"` | Shown on invoices/reminders. |

Generate the production `APP_KEY` on your machine and copy the output:

```bash
php artisan key:generate --show
# prints e.g.  base64:Xy9....=   <- paste this as APP_KEY in Railway
```

---

## 4. Step-by-step: deploy from scratch (beginner-friendly)

> ⚠️ The git remote currently points at the upstream Laravel skeleton
> (`https://github.com/laravel/laravel.git`), which you cannot push to. You must
> create **your own** GitHub repository first (Step 1).

### Step 1 — Put the code in your own GitHub repo
1. Create a new **empty** repo on GitHub, e.g. `rotikaya-sales-system` (private is fine).
2. In the project folder, point `origin` at your repo and push the current branch:
   ```bash
   git remote set-url origin https://github.com/<your-username>/rotikaya-sales-system.git
   git add .
   git commit -m "Add Railway deployment config"
   git push -u origin 13.x
   ```
   *(Your working branch is `13.x`. You can also rename it to `main` if you prefer.)*

### Step 2 — Create the Railway project
3. Go to <https://railway.com>, sign up / log in (use "Login with GitHub").
4. Click **New Project → Deploy from GitHub repo**, authorize Railway, and pick
   your repository.
5. Railway reads `railway.json`, sees the `Dockerfile`, and starts the first build.
   *(It's normal for this first build to finish before you've added the volume and
   variables — you'll redeploy after the next steps.)*

### Step 3 — Add the persistent volume (so data survives)
6. Open your service → **Variables/Settings** area → **+ Create → Volume** (or
   the service's **"Volumes"** section).
7. Set the **mount path** to exactly:
   ```
   /data
   ```
   This is where the SQLite database lives. **Do not** mount it at `/app/...`.

### Step 4 — Add the environment variables
8. In your service → **Variables**, add the variables from
   [Section 3](#3-environment-variables-set-these-in-railway) /
   `.env.production.example`. The critical ones: `APP_KEY` (generated in Step from
   Section 3), `APP_ENV=production`, `APP_DEBUG=false`, `DB_CONNECTION=sqlite`,
   `DB_DATABASE=/data/database.sqlite`.
   - You can paste many at once using Railway's **"Raw Editor"** in the Variables tab.
   - Leave `APP_URL` as a placeholder for now.

### Step 5 — Generate the public domain
9. Go to **Settings → Networking → Public Networking → Generate Domain**.
10. Railway gives you a URL like `https://rotikaya-sales-system-production.up.railway.app`.
11. Copy it, set the `APP_URL` variable to that full `https://...` URL.

### Step 6 — Deploy
12. Click **Deploy** (or push any commit). Watch the **Deploy Logs**. On first boot
    you should see migrations run and `Seeding demo data (first boot)...`.
13. When the health check on `/up` passes, open your `APP_URL` in a browser.
14. Log in with a demo account (see [Section 6](#6-demo-login-credentials)).

✅ Done. Share the `APP_URL` with your supervisor.

---

## 5. Running migrations & seeders on the live site

You normally **don't need to do anything** — `docker/entrypoint.sh` handles it
automatically on each deploy:

- `php artisan migrate --force` runs on **every** boot (only pending migrations apply).
- `php artisan db:seed --force` runs **once**, guarded by a marker file
  `/data/.seeded` on the volume, so redeploys never duplicate the demo data.

To run a command manually (e.g. re-seed or inspect data), use Railway's shell:

1. Service → the **"⋮"** menu / **Command Palette** → **Shell** (or install the
   [Railway CLI](https://docs.railway.com/guides/cli) and run `railway shell`).
2. Then run, for example:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force      # only if you intend to (re)create demo data
   php artisan migrate:status
   ```

**To completely reset the demo data:** in the Railway shell delete the database and
marker, then redeploy:
```bash
rm -f /data/database.sqlite /data/.seeded
```
The next boot recreates and re-seeds a fresh database.

---

## 6. Demo login credentials

All seeded accounts use the password **`password`**.

| Role | Email | Password | Can access |
|---|---|---|---|
| **Admin** | `admin@rotikaya.com` | `password` | Everything: users, settings, permissions, backups, reports |
| **Accountant** | `accountant@rotikaya.com` | `password` | Invoices, payments, reminders, reports, client statements |
| **Sales staff** | `sales@rotikaya.com` | `password` | Sales entry, clients, invoice viewing |
| **Sales staff** | `ali@rotikaya.com` | `password` | (second sales user, for demo variety) |

> For a real production system you would change these passwords immediately. For
> the evaluation they are intentionally simple and shared with the supervisor.

---

## 7. Live URL

```
LIVE URL:  https://__________________________.up.railway.app
```
*(Fill this in after Step 5 above.)*

Health check endpoint (should return HTTP 200 “OK”): `…/up`

---

## 8. Known production limitations

These are intentional for the FYP demo and documented for transparency:

- **WhatsApp is simulated** (`WHATSAPP_DRIVER=simulator`) — no real WhatsApp
  messages are sent; sends are logged in-app as if successful.
- **Email uses the `log` driver** — invoice/reminder emails are written to the
  application log, **not** delivered to real inboxes. Switch `MAIL_MAILER=smtp`
  with real credentials to send for real.
- **Uploaded files are ephemeral.** Only the SQLite database (on the `/data`
  volume) persists across redeploys. Contract uploads and *stored* invoice PDFs
  live on the container's temporary filesystem and are cleared on redeploy.
  *On-demand PDF/report/statement downloads are unaffected* — they are generated
  and streamed live, so they always work.
- **Scheduled jobs are not running.** The daily tasks (`invoices:update-statuses`,
  `reminders:send`, `backup:database`) need a cron worker, which isn't configured.
  They aren't needed for evaluation; reminders can still be triggered manually from
  the UI. To enable them later, add a Railway **Cron** service running
  `php artisan schedule:run`.
- **Built-in PHP web server.** The container serves via `php artisan serve`, which
  is reliable for a single-evaluator demo but not tuned for high concurrent traffic.
- **SQLite single-writer.** Fine for one evaluator; switch to managed MySQL (env
  template provided) if concurrent multi-user load is required.

---

## 9. Rollback instructions

**Fastest (Railway dashboard):**
1. Open your service → **Deployments** tab.
2. Find the last known-good deployment in the list.
3. Click its **"⋮"** menu → **Redeploy** (or **Rollback / Restore**). Railway
   instantly re-serves that previous build. Your data on the `/data` volume is
   unaffected.

**Via git (if a bad commit caused the issue):**
```bash
git revert <bad-commit-sha>     # or: git reset --hard <good-sha> && git push --force
git push
```
The push triggers a fresh Railway deploy of the corrected code.

**Database recovery:**
- The SQLite file persists on the volume, so a code rollback does not lose data.
- The app also has an in-app backup feature (Admin → Settings → Backup) that
  produces downloadable SQL backups.
- To fully reset demo data, see the reset command in
  [Section 5](#5-running-migrations--seeders-on-the-live-site).

---

## 10. Pre-deploy verification (performed locally)

Before deploying, the app was run in **production mode** locally
(`APP_ENV=production`, `APP_DEBUG=false`) with config/route/view caches enabled:

| Check | Result |
|---|---|
| `php artisan config:cache && route:cache && view:cache` | ✅ all cached, `env=production debug=false` |
| `GET /up` (health) | ✅ 200 |
| `GET /login` | ✅ 200 (form renders) |
| `GET /` | ✅ 302 → `/dashboard` |
| `POST /login` (seeded admin) | ✅ 302 → `/dashboard` (auth + sessions work) |
| `GET /dashboard` (authenticated) | ✅ 200 |
| `GET /reports/download/financial-summary` | ✅ 200, `application/pdf`, valid `%PDF` |
| `GET /reports/download/cash-flow` | ✅ 200, `application/pdf` |
| `php artisan test` | ✅ 91 passed, 520 assertions |

Caches were then cleared and the dev `.env` restored, so local development
continues to work normally. No production-only breakage was found (no hardcoded
`localhost`, no missing-env or asset-path issues with `APP_URL` set correctly and
proxies trusted).
