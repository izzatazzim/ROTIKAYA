# Visual Overhaul — Production-Grade Design Pass

**Date:** 2026-06-02
**Goal:** Make the system look like real production SaaS (Stripe/Linear/Notion), not a student project,
while keeping all 91 automated tests green.

## Design tokens (`resources/css/app.css`)

| Token | Value |
|-------|-------|
| Background | `#0a0a0a` |
| Surface (card) | `#141414` |
| Surface (elevated) | `#1c1c1c` |
| Border subtle | `rgba(255,255,255,0.06)` |
| Border defined | `rgba(255,255,255,0.10)` |
| Text primary / secondary / tertiary | `#fafafa` / `#a1a1a1` / `#6b6b6b` |
| Brand red / dark | `#dc2626` / `#991b1b` (Tailwind `red-600`/`red-700`) |
| Success / Warning / Error / Info | `#10b981` / `#f59e0b` / `#ef4444` / `#3b82f6` |
| Font | Inter (sans + display), JetBrains Mono (numbers/IDs) |

## System rules applied globally

- **Radius:** cards `rounded-xl` (12px), buttons/inputs `rounded-lg` (8px), pills `rounded-md`/`rounded-full`.
- **Removed:** hover-lift effects, card drop-shadows at rest, red gradient cards, the red gradient login card, oversized `font-display font-bold` page titles, saturated `/30` badges.
- **Typography:** page titles `text-2xl font-semibold tracking-tight`; section titles `text-base font-semibold`; body 14px; labels `text-xs font-medium text-gray-400`.
- **Tables:** transparent header, `text-xs uppercase tracking-wider text-gray-500`, no header fill; subtle row borders `white/[0.04]`, hover `white/[0.02]`.
- **Status pills:** `bg-{c}-500/10 text-{c}-400 ring-1 ring-{c}-500/20` (paid/overdue/partial/due-soon/awaiting).
- **Buttons:** primary `bg-red-600 hover:bg-red-700`; secondary `bg-white/5 border-white/10`; tertiary text-only; destructive red text + confirm modal.
- **Inputs:** `bg-white/[0.02] border-white/10`, focus `border-red-500 ring-1 ring-red-500/30`.

## Before → After (key pages)

1. **Login** — radial-red gradient + `#1e1e1e` card → pure-black bg, `#141414` card, refined inputs/button.
2. **Sidebar** — raised `#0f0f0f`, solid-red active pill → flat `#0a0a0a`, active `bg-red-600/10 text-red-400 border-l-2 border-red-500`, neutral hovers, gray avatars.
3. **Dashboard** — red gradient revenue card + 4 hover-lift cards + 3px red gridline chart → flat identical KPI cards (label top, mono number, icon @ 40% opacity), revenue marked with a red accent dot, chart with no gridlines / hidden y-axis / 2px line, payments list with initial avatars.
4. **Invoices list** — red table header + `rounded-lg` tabs → header-less uppercase-gray table, `rounded-full` filter chips (active = red tint), subtle rows.
5. **Settings / Reports / Users / Sales** — `#1e1e1e`/`#262626`/`rounded-2xl` surfaces → `#141414` + `white/[0.06]` + `rounded-xl`; oversized field labels normalised; role pills changed to subtle ring-pills (sales → gray, per spec).

## Files touched (visual)

`resources/css/app.css`, `resources/views/layouts/app.blade.php`, `auth/login.blade.php`,
`auth/forgot-password.blade.php`, `auth/reset-password.blade.php`, `dashboard/{_content,admin,accountant,sales}.blade.php`,
`invoices/{index,show}.blade.php`, `sales/index.blade.php`, `clients/index.blade.php`,
`reports/{index,client-statement}.blade.php`, `users/{index,edit}.blade.php`,
`settings/index.blade.php`, `permissions/index.blade.php`, `payments/index.blade.php`,
`components/empty-state.blade.php` — **~20 files.** PDF/print templates intentionally untouched.
