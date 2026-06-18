# ROTIKAYA — Sales Tracking & Management System

A Laravel 11 web application for Rotikaya Media Sdn Bhd to manage advertising
sales, clients, invoices, payments, and overdue-payment reminders, with
role-based access (admin, accountant, sales staff), PDF/Excel reporting, and a
financial dashboard. Built as a Final Year Project (PSM 2).

## Tech stack

- **Laravel 11** (PHP 8.3), Blade + Tailwind CSS, Vite
- **SQLite** (dev) / SQLite on a persistent volume (production) via Eloquent
- **barryvdh/laravel-dompdf** — invoice / report / statement PDFs
- **maatwebsite/excel** — revenue report XLSX export
- **spatie/laravel-permission** — roles & permissions

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev          # in one terminal
php artisan serve    # in another
```

Demo logins (password `password`): `admin@rotikaya.com`,
`accountant@rotikaya.com`, `sales@rotikaya.com`.

## Tests

```bash
php artisan test
```

## Deployment

See [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) for the full Railway deployment
guide (Docker build + persistent SQLite volume).
