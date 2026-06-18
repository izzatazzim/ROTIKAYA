# Implementation Roadmap (Prioritized)

Reference baseline: `docs/PROJECT_SPEC.md` (authoritative requirements map).
Priority strategy: production correctness -> functional completeness -> UX polish -> STD alignment.

## Phase 1: Critical Gaps (Blocks Core Functionality)

- **Fix UC001 file upload path mismatch** (FR001, UC001, TC001_01)
  - Problem: `contract` upload field does not persist due to `contract_path` mismatch.
  - Files:
    - `resources/views/sales/index.blade.php`
    - `app/Http/Requests/StoreSaleRequest.php`
    - `app/Http/Controllers/Web/SalesController.php`
    - optionally storage config + migration if file metadata structure updated.
- **Stabilize reminder sending workflow** (FR003, UC004, TC002_04)
  - Problem: duplicate logic and mocked delivery path.
  - Files:
    - `app/Console/Commands/SendPaymentReminders.php`
    - `app/Services/ReminderService.php`
    - `app/Services/WhatsAppService.php`
    - `routes/console.php`
- **Correct invoice status filtering behavior** (UC004/Invoice tracking usability)
  - Problem: `outstanding` tab maps to unsupported status value.
  - Files:
    - `resources/views/invoices/index.blade.php`
    - `app/Http/Controllers/Web/InvoicesController.php`
- **Harden auth/account access essentials** (NFR002, UC007 operational safety)
  - Problem: no forgot-password flow and no strong access hardening add-ons.
  - Files:
    - `resources/views/auth/login.blade.php`
    - `routes/web.php`
    - relevant auth reset controllers/views (new)
- **Explicit validation for system config intervals** (UC008, TC003_04)
  - Problem: invalid reminder values filtered silently.
  - Files:
    - `app/Http/Controllers/Web/SystemSettingsController.php`
    - `resources/views/settings/index.blade.php`

## Phase 2: Functional Gaps (Missing Features, Not Blocking Boot)

- **Implement UC006 Export Client Statement (PDF + CSV/XLSX as required)** (UC006, TC002_07, TC002_08)
  - Files:
    - `routes/web.php`
    - `app/Http/Controllers/Web/ReportsController.php` (or dedicated statement controller)
    - `resources/views/reports/index.blade.php`
    - `resources/views/reports/pdf/client-statement.blade.php` (new)
    - export service class (new)
- **Complete UC005 alternate export behavior (Excel/XLSX)** (UC005 alt flow, TC002_05)
  - Files:
    - `app/Http/Controllers/Web/ReportsController.php`
    - `resources/views/reports/index.blade.php`
    - export package integration + service abstraction (new)
- **Complete FR002 sending behavior for invoices** (FR002, UC003)
  - Files:
    - `app/Http/Controllers/Web/InvoicesController.php`
    - invoice dispatch service (new)
    - templates / notification channels config (new/updated)
- **Implement sales dashboard filter semantics per UC002** (UC002, TC001_04)
  - Files:
    - `app/Http/Controllers/Web/DashboardController.php`
    - `resources/views/dashboard/sales.blade.php`
- **Implement backup trigger and audit-safe admin operations** (NFR007, NFR002)
  - Files:
    - `app/Http/Controllers/Web/SystemSettingsController.php`
    - `routes/web.php`
    - backup job/command classes (new)
    - `resources/views/settings/index.blade.php`

## Phase 3: Polish (UI, UX, Validation, Consistency)

- **Enforce strict contrast rule across all dark-theme components** (NFR001 + project rule)
  - Files:
    - `resources/css/app.css`
    - all major blade forms/tables with subdued text colors.
- **Standardize flash/error semantics and no-data messaging**
  - Files:
    - `app/Http/Controllers/Web/UsersController.php`
    - `resources/views/*` for consistent error components.
- **Reduce noisy report log writes**
  - Files:
    - `app/Http/Controllers/Web/ReportsController.php`
- **Improve configuration UX constraints**
  - Files:
    - `resources/views/settings/index.blade.php`
    - `app/Http/Controllers/Web/SystemSettingsController.php`
- **Align role matrix docs/page with actual enforced routes**
  - Files:
    - `resources/views/permissions/index.blade.php`
    - `routes/web.php`

## Phase 4: Test Coverage (Full STD Alignment)

- **Map every STD test (TC001-TC003) to automated feature/unit tests**
  - Files:
    - `tests/Feature/*`
    - `tests/Unit/*`
- **Add missing tests for currently failing/not-testable cases**
  - Targets:
    - TC001_04, TC002_02, TC002_04, TC002_05, TC002_07, TC002_08, TC003_04.
- **Add regression tests for role middleware and permissions boundaries**
  - Files:
    - `tests/Feature/Auth*`, `tests/Feature/Permissions*` (new)
- **Add export artifact assertions (PDF/CSV/XLSX)**
  - Files:
    - `tests/Feature/Reports*` (new)
- **Add reminder delivery behavior tests with fake integrations**
  - Files:
    - `tests/Unit/Reminder*`
    - notification service contracts (if introduced)

## Suggested Delivery Sequence

1. Phase 1.1 + 1.3 + 1.5 (quick correctness fixes)
2. Phase 1.2 reminder stabilization
3. Phase 2 exports (UC006 + UC005 alt flow)
4. Phase 2 invoice sending and UC002 dashboard filter completion
5. Phase 3 UI/accessibility cleanup
6. Phase 4 STD-complete automation

## Dependencies and Risks

- Export features may require new package decisions (PDF already present; XLSX package selection pending).
- Notification delivery requires environment credentials and retry strategy decisions.
- Some source spec text is internally inconsistent (notably UC007 exception/post-condition), requiring confirmation before strict acceptance assertions.
