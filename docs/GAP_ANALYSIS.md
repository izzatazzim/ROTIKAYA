# Gap Analysis vs Current Laravel Codebase

Baseline for comparison uses precedence: **SRS > FYP1 > STD**.
Scope checked: routes, controllers, models, requests, services, migrations, views, tests.

## ✅ Fully Implemented

- **Authentication baseline (login/logout)**
  - **Spec refs**: SRS `2.1` user roles, FYP1 Figure `4.12`.
  - **Code refs**: `app/Http/Controllers/Web/AuthController.php:16`, `routes/web.php:18`, `routes/web.php:23`, `resources/views/auth/login.blade.php:31`.
- **Role-based route restriction (FR004 core)**
  - **Spec refs**: FR004 (FYP1 Table 4.1), SRS `2.3.7 UC007`.
  - **Code refs**: `app/Http/Middleware/EnsureUserHasRole.php:11`, `bootstrap/app.php:18`, `routes/web.php:26`, `routes/web.php:42`, `routes/web.php:48`.
- **Invoice outstanding tab semantics**
  - **Spec refs**: UC004 payment tracking/listing expectations.
  - **Code refs**: `resources/views/invoices/index.blade.php`, `app/Http/Controllers/Web/InvoicesController.php`.
- **UC008 strict reminder interval validation**
  - **Spec refs**: UC008 validation behavior, TC003_04.
  - **Code refs**: `app/Http/Controllers/Web/SystemSettingsController.php`, `resources/views/settings/index.blade.php`.
- **Reminder orchestration and channel-attempt logging**
  - **Spec refs**: FR003, UC004, TC002_04.
  - **Code refs**: `app/Services/ReminderService.php`, `app/Console/Commands/SendPaymentReminders.php`, `database/migrations/2026_05_08_010000_create_payment_reminders_table.php`.
- **Forgot-password flow (password broker scaffold)**
  - **Spec refs**: NFR002 security baseline, mandatory clarification for PSM 2.
  - **Code refs**: `routes/web.php`, `app/Http/Controllers/Web/AuthController.php`, `resources/views/auth/forgot-password.blade.php`, `resources/views/auth/reset-password.blade.php`.
- **UC006 Client Statement export (PDF + no-data behavior)**
  - **Spec refs**: SRS `2.3.6`, STD `TC002_07`, `TC002_08`.
  - **Code refs**: `routes/web.php`, `app/Http/Controllers/Web/ReportsController.php`, `resources/views/reports/client-statement.blade.php`, `resources/views/reports/pdf/client-statement.blade.php`, `tests/Feature/ClientStatementTest.php`.
- **UC005 revenue XLSX export (multi-sheet raw tabs)**
  - **Spec refs**: SRS `2.3.5`, STD `TC002_05`.
  - **Code refs**: `routes/web.php`, `app/Http/Controllers/Web/ReportsController.php`, `app/Exports/RevenueReportExport.php`, `app/Exports/Sheets/*`, `tests/Feature/RevenueReportXlsxTest.php`.
- **Payment recording and status refresh core**
  - **Spec refs**: UC004 normal flow, TC002_03.
  - **Code refs**: `app/Http/Controllers/Web/PaymentsController.php:25`, `app/Services/PaymentService.php:9`, `app/Services/PaymentService.php:22`.
- **Overdue reminder scheduling infrastructure (command + service wiring)**
  - **Spec refs**: FR003, UC004, TC002_04.
  - **Code refs**: `routes/console.php:15`, `app/Console/Commands/SendPaymentReminders.php:13`, `app/Services/ReminderService.php:15`.
- **Core DB schema for clients/sales/invoices/payments/reminders/users/roles**
  - **Spec refs**: SRS domain references, FYP1 Figure `4.11`.
  - **Code refs**: `database/migrations/2026_05_07_000100_create_sales_tracking_tables.php:11`, `database/migrations/0001_01_01_000000_create_users_table.php:14`.
- **UC001 contract upload persistence**
  - **Spec refs**: UC001 normal flow (contract PDF upload), TC001_01.
  - **Code refs**: `app/Http/Requests/StoreSaleRequest.php`, `app/Http/Controllers/Web/SalesController.php`, `resources/views/sales/index.blade.php`.

## ⚠️ Partially Implemented

- **FR001 / UC001 Record Sale**
  - **Spec expectation**: Sale capture includes attachment path and clean validation handling.
  - **Current state**:
    - PDF contract upload is validated as file and persisted to public storage.
    - Stored file path is mapped to `contract_path` before sale creation.
  - **Code refs**: `app/Http/Requests/StoreSaleRequest.php`, `app/Http/Controllers/Web/SalesController.php`, `resources/views/sales/index.blade.php`.
- **FR003 / UC004 Reminder workflow**
  - **Spec expectation**: robust 15/30/45 reminder behavior with API failure handling and logging.
  - **Current state**:
    - Processing is centralized through `ReminderService` and scheduled command invokes that single path.
    - WhatsApp is primary channel; email fallback is attempted only if WhatsApp fails.
    - Channel-level attempts are logged in `payment_reminders`.
    - WhatsApp provider is simulator-based with configurable success/failure modes (`app/Services/WhatsAppService.php`).
  - **Gap**: production WhatsApp API provider remains a future integration, but failure handling is now fully testable.
- **FR005 / UC002/UC005 Reporting**
  - **Spec expectation**: real-time dashboards + filtered reporting + PDF/Excel export with no-data guidance.
  - **Current state**:
    - Filtered report table exists (`app/Http/Controllers/Web/ReportsController.php:22`, `resources/views/reports/index.blade.php:112`),
    - PDF + XLSX export flows now implemented for UC005.
  - **Gap**: remaining polish only (no blocker-level gaps in UC002/UC005 core flows).
- **UC007 Manage User Accounts**
  - **Spec expectation**: create/modify users and send login details.
  - **Current state**:
    - Create/edit/delete users implemented (`app/Http/Controllers/Web/UsersController.php:23`, `app/Http/Controllers/Web/UsersController.php:40`),
    - No email dispatch of credentials or onboarding notification.
  - **Gap**: account lifecycle exists, notification requirement missing.
- **UC008 Configure System**
  - **Spec expectation**: strict validation and auditable configuration changes.
  - **Current state**:
    - Settings page + update endpoint exist.
    - Invalid reminder intervals are explicitly rejected with clear validation feedback.
  - **Remaining gap**: advanced config audit/reporting UX still polish-level.

## ❌ Not Implemented

- **2FA for financial access (from stakeholder requirements in appendices)**
  - **Spec refs**: SRS Appendix interview; FYP1 Appendix A.
  - **Missing evidence**: no MFA/2FA implementation.
- **60-day auto-suspension rule for overdue accounts**
  - **Spec refs**: SRS/FYP1 appendices interview.
  - **Missing evidence**: no service suspension state/logic.

## 🔧 Issues / Bugs

- **Mock WhatsApp service in production path**
  - Simulator driver is intentionally used for PSM 2; real provider integration is deferred by design.
  - Impact: production deployment requires provider implementation and credentials, but app-level fallback paths are covered.
## 🧪 Test Coverage Status (STD vs Current)

See `docs/STD_TRACEABILITY.md` for the current STD coverage matrix.

## Needs Clarification

- UC007 exception/post-condition text is clearly inconsistent (copied from reporting use case in source docs).
- Whether email reminders are mandatory together with WhatsApp in this phase.

## ✅ Fully Implemented (Newly Closed in Phase 2)

- **UC002 Sales Dashboard Filters**
  - **Spec refs**: SRS `2.3.2`, STD `TC001_04`.
  - **Implemented evidence**:
    - Validation and defaults through `app/Http/Requests/DashboardFilterRequest.php`.
    - Aggregation/scoping through `app/Services/DashboardService.php`.
    - Filter UI + empty states in `resources/views/dashboard/_content.blade.php`.
    - Role-aware behavior:
      - Admin/Accountant: date + client + salesperson filters.
      - Sales staff: date + client only, always scoped to own data.
    - Chart granularity logic (daily/monthly/quarterly) based on range length.
    - Recent payments list and stat cards update by filters with no-data fallback.
    - Automated coverage in `tests/Feature/DashboardFiltersTest.php`.

- **FR002 / UC003 Send Invoice behavior**
  - **Spec refs**: FYP1 `FR002`, SRS `2.3.3` context + Phase 2 clarifications.
  - **Implemented evidence**:
    - Send endpoint with role guard: `POST /invoices/{invoice}/send` in `routes/web.php`.
    - Dispatch orchestration in `app/Services/InvoiceDispatchService.php` with WhatsApp primary and email fallback.
    - PDF generation reused from `app/Services/InvoiceService.php` (`storeInvoicePdf()`), with path stored for dispatch tracking.
    - Persistent dispatch trail in `invoice_dispatches` via migration `2026_05_08_020000_create_invoice_dispatches_table.php`.
    - UI send controls/state feedback in `resources/views/invoices/index.blade.php` and `resources/views/invoices/show.blade.php`.
    - Resend supported; latest successful send shown via `Invoice::lastSuccessfulDispatch`.
    - Audit events emitted through observer pattern as `invoice.dispatched` / `invoice.dispatch_failed`.
    - Automated coverage in `tests/Feature/InvoiceSendTest.php`.
- **TC002_02 template guard for invoice generation**
  - **Spec refs**: STD `TC002_02`, UC003 generate flow prerequisite.
  - **Implemented evidence**:
    - Template schema in `database/migrations/2026_05_08_050000_create_invoice_templates_table.php`.
    - Generation guard + exception in `app/Services/InvoiceService.php` and `app/Exceptions/TemplateNotFoundException.php`.
    - Controller feedback path in `app/Http/Controllers/Web/InvoicesController.php`.
    - Default template seeded in `database/seeders/DatabaseSeeder.php`.
    - Automated coverage in `tests/Feature/InvoiceGenerationTest.php`.
- **NFR007 Backup/Recovery (daily + manual trigger)**
  - **Spec refs**: SRS `2.4`, NFR007.
  - **Implemented evidence**:
    - Backup command `backup:database` in `app/Console/Commands/BackupDatabase.php`.
    - Daily schedule at `02:00` in `routes/console.php`.
    - Manual admin trigger + download routes in `routes/web.php`.
    - Backup metadata persistence in `backups` table (`2026_05_08_040000_create_backups_table.php`).
    - Admin UI section in `resources/views/settings/index.blade.php`.
    - Retention policy (keep last 30) in `app/Services/DatabaseBackupService.php`.
    - Automated coverage in `tests/Feature/BackupTest.php`.
- **P3.1 report log noise reduction (export-only report logs)**
  - **Spec refs**: Phase 3B.1 polish scope, reporting audit quality.
  - **Implemented evidence**:
    - Removed report log creation from `ReportsController@index`.
    - Kept report log creation in export endpoints (`downloadPdf`, `exportRevenuePdf`, `exportRevenueXlsx`, `exportClientStatement`).
    - Added regression checks in `tests/Feature/ReportsTest.php`:
      - viewing reports page does not log
      - exporting a report logs exactly as expected
- **P3.2 flash/error semantics standardization**
  - **Spec refs**: Phase 3B.2 polish scope, UX consistency requirement.
  - **Implemented evidence**:
    - Unified flash contract keys (`success`, `error`, `warning`, `info`) rendered globally by `resources/views/components/flash-messages.blade.php`.
    - Shared empty-state component added at `resources/views/components/empty-state.blade.php` and applied in dashboard/report/invoice/client no-data views.
    - Self-delete messaging severity corrected in `app/Http/Controllers/Web/UsersController.php` (`error` instead of success-style feedback).
    - Non-JSON invoice send failure path now returns flash `error` for consistent semantics in browser form flows.
    - Coverage added in `tests/Feature/FlashMessagesTest.php`.
- **P3.3 contrast/accessibility pragmatic sweep**
  - **Spec refs**: Phase 3B.3 polish scope, pragmatic readability/accessibility pass.
  - **Implemented evidence**:
    - Audit evidence documented in `docs/CONTRAST_AUDIT.md`.
    - Resolved login heading contrast issue in `resources/css/app.css` (`.rtk-login-heading`).
    - Added visible keyboard focus treatment (`:focus-visible`) for links/buttons/form controls in `resources/css/app.css`.
    - Improved auth placeholder readability and select/date control visibility in dark theme.
    - Improved disabled-state affordance in settings backup action.
    - Added render smoke coverage in `tests/Feature/AccessibilitySmokeTest.php`.
- **P3.4 settings UX consistency pass**
  - **Spec refs**: Phase 3B.4 polish scope, settings UI consistency.
  - **Implemented evidence**:
    - Refactored `resources/views/settings/index.blade.php` into consistent section blocks with standardized section headers/descriptions/body structure.
    - Unified form label/help-text patterns and action-button placement for billing defaults and backup sections.
    - Added concise section descriptions for payment terms/reminder intervals and backup retention/storage behavior.
    - Added inline reminder-interval validation message styling aligned with existing flash/error semantics.
- **P3.5 permissions page documentation alignment**
  - **Spec refs**: Phase 3B.5 polish scope, route-role documentation consistency.
  - **Implemented evidence**:
    - Canonical role-permission documentation source added at `config/permissions.php`.
    - `PermissionsController@index` now reads config matrix and passes role/permission metadata to view.
    - `resources/views/permissions/index.blade.php` now renders role cards with permission key + description tables.
    - Matrix aligns with currently enforced role middleware in `routes/web.php` (documents current enforcement; no middleware behavior changes).
    - Access/render test coverage added in `tests/Feature/PermissionsPageTest.php`.
- **P4.2 role boundary regression hardening**
  - **Spec refs**: NFR002 security enforcement evidence.
  - **Implemented evidence**:
    - Canonical route-boundary regression suite added in `tests/Feature/RoleBoundariesTest.php`.
    - Coverage includes admin-only, admin+accountant, admin+sales, and shared authenticated routes.
    - Includes explicit guest redirect coverage for protected routes.
    - Includes URL tampering probes for dashboard/invoice scope bypass attempts.
- **P4.2.5 security bug fixes from role boundary findings**
  - **Spec refs**: NFR002 confidentiality and scope enforcement.
  - **Implemented evidence**:
    - Fixed sales-staff dashboard tampering vector by force-applying `salesperson_id = auth()->id()` in validated dashboard filters.
    - Fixed sales-staff invoice visibility bypass by applying sales-staff query scope to invoice listing and invoice detail access.
    - Added preventative sales-staff own-data scoping on sales listing query.
    - Verified with previously failing role-boundary tampering tests now passing.
    - Role boundary suite is now fully green (`30/30`).
- **P4.3 explicit STD TC assertions + method alignment**
  - **Spec refs**: STD traceability quality for `TC001_01`..`TC003_04`.
  - **Implemented evidence**:
    - Added explicit coverage for previously partial test cases: `TC001_02`, `TC003_02`, `TC003_03`, `TC003_04`.
    - Renamed TC-mapped test methods to `test_TC...` format for direct traceability and grepability.
    - Added standardized `@std` docblocks with STD description and expected-result text for each TC-mapped test.
    - Updated matrix entries in `docs/STD_TRACEABILITY.md` to show `16/16` fully covered.
    - Verified targeted TC run (`php artisan test --filter=TC0`) and full regression are green.
- **Resolved: sales dashboard scope bypass via date-range + salesperson tampering**
  - Resolved via forced self-scope in dashboard filter preparation/controller usage.
- **Resolved: invoice list scope bypass for sales staff via customer filter**
  - Resolved via mandatory sales-staff salesperson scope on invoice queries.
- **TC002_04 reminder delivery resilience with configurable WhatsApp simulator**
  - **Spec refs**: STD `TC002_04`, FR003/UC004 fallback handling.
  - **Implemented evidence**:
    - Driver + simulation config in `config/services.php` and `.env.example`.
    - Structured send contract + simulator modes in `app/Services/WhatsAppService.php`.
    - Consumer adaptation in `app/Services/ReminderService.php` and `app/Services/InvoiceDispatchService.php`.
    - Deterministic random-mode testability in `tests/Unit/ReminderTriggerTest.php` and `tests/Unit/WhatsAppServiceTest.php`.
