# User Acceptance Testing (UAT) Plan — Rotikaya Media Sales Tracking & Management System

This document is the script and record for User Acceptance Testing. UAT is the
final check where an end user (or an evaluator acting as one) performs real
business tasks and confirms the system is acceptable for use. The tester follows
each scenario, marks **Pass/Fail**, notes any issue, and signs off at the end.

---

## 1. What you (the student) must prepare before the session

- [ ] **Live system reachable** at the deployment URL (or run locally with `php artisan serve`).
- [ ] **Demo data loaded** so screens are populated (see §3).
- [ ] **Demo accounts work** for all three roles (see §3).
- [ ] **Printed or shared copy of this plan** for the tester to fill in.
- [ ] **A second device/browser** ready if you want to show two roles side by side.
- [ ] **Note-taking ready** for the Defect Log (§6).
- [ ] Decide who the tester is: supervisor/evaluator, or a stand-in for Rotikaya
      staff (e.g., an accountant and a salesperson persona).

### How to run the session
1. Briefly explain the system's purpose (manage advertising sales → invoices →
   payments → reminders → reports, with role-based access).
2. Hand the tester the credentials for the role being tested.
3. Let the tester drive; you only assist if they're stuck. Record results live.
4. After each role, log any defects. At the end, complete the sign-off (§7).

---

## 2. Test environment

| Item | Value |
|---|---|
| URL | `https://rotikaya-production.up.railway.app` (or `http://127.0.0.1:8000` locally) |
| Browser | Latest Chrome / Edge |
| Build / commit | _fill in (e.g., `git rev-parse --short HEAD`)_ |
| Test date | _fill in_ |
| Tester name & role | _fill in_ |

---

## 3. Demo accounts & data

All accounts use password **`password`**.

| Role | Email | Can do |
|---|---|---|
| Admin | `admin@rotikaya.com` | Everything: users, settings, permissions, backups, reports |
| Accountant | `accountant@rotikaya.com` | Invoices, payments, reminders, reports, statements |
| Sales staff | `sales@rotikaya.com` | Record sales, manage clients, view invoices |

**Load demo data** (≈2 years of sales/invoices/payments for full-looking screens):
```bash
php artisan db:seed --class=DemoHistorySeeder --force
```
(Run once. On Railway, run it from the service Shell.)

---

## 4. Acceptance criteria (how we decide UAT passed)

- **All "Critical" scenarios (marked 🔴) must Pass.**
- No more than **2 minor (cosmetic) defects** open.
- No data loss, no security bypass (a role cannot access another role's pages).
- The system is **acceptable** when the tester signs §7.

---

## 5. UAT test scenarios

Tester: perform the steps, then tick **Pass** or **Fail** and add a comment.
"Ref" links to the requirement / use case / STD test case.

### A. Authentication & role-based access

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-A1 | 🔴 | Valid login | Open `/login`, sign in as admin | Lands on dashboard; name/role shown | FR004, UC007 | ☐P ☐F | |
| UAT-A2 | 🔴 | Invalid login | Enter wrong password | Stays on login with a clear error; no access | NFR002 | ☐P ☐F | |
| UAT-A3 | 🔴 | Role-based menu | Log in as each role in turn | Each role sees only its allowed menu items | FR004, UC007 | ☐P ☐F | |
| UAT-A4 | 🔴 | Access control (negative) | As `sales`, type `/users` in the URL | Access denied / redirected — cannot reach admin pages | NFR002 | ☐P ☐F | |
| UAT-A5 | 🟡 | Logout | Click logout | Returns to login; back button does not re-enter | UC007 | ☐P ☐F | |

### B. Sales & clients (Sales staff / Admin)

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-B1 | 🔴 | Record a sale | Sales → New Sale, fill client/campaign/amount, save | Sale saved and listed | FR001, UC001, TC001_01 | ☐P ☐F | |
| UAT-B2 | 🔴 | Validation guard | Submit a sale with a missing required field | Blocked with a clear field error; nothing saved | UC001, TC001_02 | ☐P ☐F | |
| UAT-B3 | 🟡 | Add a client | Clients → add a new client | Client saved and selectable on a new sale | FR001, UC001 | ☐P ☐F | |

### C. Invoices (Accountant / Admin)

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-C1 | 🔴 | Generate invoice | From a **completed** sale, generate an invoice | Invoice created with number, dates, amount | FR002, UC003, TC002_01 | ☐P ☐F | |
| UAT-C2 | 🔴 | Download invoice PDF | Open the invoice, download/view the PDF | PDF downloads; layout is professional; **no LHDN QR, no real bank account** shown | FR002, UC003 | ☐P ☐F | |
| UAT-C3 | 🟡 | Send invoice | Click "Send" on an invoice | Send is recorded (WhatsApp simulated); status/dispatch updates | FR003, UC004 | ☐P ☐F | |
| UAT-C4 | 🟡 | Sales can view only | As `sales`, open invoices list | Can view invoices but cannot generate/send | FR004 | ☐P ☐F | |

### D. Payments & status lifecycle (Accountant / Admin)

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-D1 | 🔴 | Record full payment | On an unpaid invoice, record full payment | Invoice status becomes **paid**; balance = 0 | FR002, UC004, TC002_03 | ☐P ☐F | |
| UAT-D2 | 🟡 | Record partial payment | Record a part payment | Status becomes **partial**; balance reduced | UC004 | ☐P ☐F | |
| UAT-D3 | 🟡 | Trigger reminders | Reminders → trigger for overdue invoices | Overdue invoices get a reminder record (WhatsApp sim, email fallback) | FR003, UC004, TC002_04 | ☐P ☐F | |

### E. Dashboard & reports (Accountant / Admin)

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-E1 | 🔴 | Dashboard figures | Open dashboard | Totals/charts reflect the demo data (sales, overdue, collection) | FR005, UC002 | ☐P ☐F | |
| UAT-E2 | 🟡 | Dashboard filters | Apply a date range / client filter | Figures update; an empty range shows a clear "no data" state | FR005, TC001_03, TC001_04 | ☐P ☐F | |
| UAT-E3 | 🔴 | Revenue report XLSX | Reports → export revenue as Excel | XLSX downloads and opens with Summary/Invoices/Payments/Sales | FR005, UC005, TC002_05 | ☐P ☐F | |
| UAT-E4 | 🔴 | Financial report PDF | Reports → download a financial summary PDF | PDF downloads; figures correct; clean layout | FR005, UC005 | ☐P ☐F | |
| UAT-E5 | 🟡 | Client statement PDF | Reports → client statement → pick a client → export | Statement PDF lists that client's invoices/balances | FR005, UC006 | ☐P ☐F | |
| UAT-E6 | 🟡 | No-data guard | Request an export for a range with no data | Blocked with a clear message (not a broken file) | UC005, TC002_06 | ☐P ☐F | |

### F. Admin controls (Admin only)

| ID | Pri | Scenario | Steps | Expected result | Ref | Pass/Fail | Comment |
|---|---|---|---|---|---|---|---|
| UAT-F1 | 🔴 | Create user | Users → add a user with a role | User created and can log in | FR004, UC007, TC003_01 | ☐P ☐F | |
| UAT-F2 | 🟡 | Duplicate email guard | Create a user with an existing email | Blocked with a clear validation error | UC007, TC003_02 | ☐P ☐F | |
| UAT-F3 | 🟡 | Update settings | Settings → change payment term / reminder intervals, save | Saved; invalid values rejected | UC008, TC003_03 | ☐P ☐F | |
| UAT-F4 | 🟡 | Run backup | Settings → run a backup, download it | Backup created and downloadable | UC008, TC003_04 | ☐P ☐F | |
| UAT-F5 | 🟢 | Permissions view | Open Permissions page | Role/permission matrix is displayed | FR004 | ☐P ☐F | |

**Priority key:** 🔴 Critical (must pass) · 🟡 Important · 🟢 Nice-to-have.

---

## 6. Defect log

| # | Scenario ID | Severity (High/Med/Low) | Description | Steps to reproduce | Status |
|---|---|---|---|---|---|
| 1 | | | | | |
| 2 | | | | | |
| 3 | | | | | |

---

## 7. UAT result & sign-off

| | |
|---|---|
| Total scenarios | 27 |
| Passed | _fill in_ |
| Failed | _fill in_ |
| Critical (🔴) all passed? | ☐ Yes ☐ No |
| Open defects | _fill in_ |

**Overall outcome:** ☐ Accepted ☐ Accepted with minor defects ☐ Rejected (rework needed)

| Role | Name | Signature | Date |
|---|---|---|---|
| Tester / Evaluator | | | |
| Developer (student) | | | |

---

## 8. Likely evaluator questions (be ready to answer)

- **"How do you enforce role restrictions?"** — Route-level `role` middleware plus
  query-level scoping; covered by role-boundary regression tests
  (`php artisan test --filter=Role`).
- **"Is this behaviour tested?"** — Yes; STD test cases are mapped in
  `docs/STD_TRACEABILITY.md`; run `php artisan test --filter=TC0`.
- **"Is the WhatsApp integration real?"** — It uses a deterministic simulator for
  testing; the production API migration path is documented in `docs/PROJECT_SPEC.md`.
- **"Why both PDF and XLSX exports?"** — PDF for formal statements/reports; XLSX
  for analysis (Summary, Invoices, Payments, Sales sheets).
- **"What's deferred and why?"** — See `docs/SUBMISSION_READINESS.md`; deferred
  items are enhancement-level (e.g., live WhatsApp API, 2FA, cloud backups).

> For a guided live walkthrough, see `docs/VIVA_DEMO_SCRIPT.md` (5-min and 15-min versions).
