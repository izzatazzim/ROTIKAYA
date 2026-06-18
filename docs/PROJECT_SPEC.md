# Project Specification (Unified)

Authoritative precedence used for consolidation: **SRS > FYP1 report > STD** (per request).

## 1) Source References

- `SRS_IZZAT AZZIM_A22MJ5014.pdf`
  - Section `2.2` (System Features / UC001-UC008)
  - Section `2.3.1` to `2.3.8` (use case details and flows)
  - Section `2.4` (software system attributes, performance, other requirements)
  - Section `2.5` (design constraints)
- `FYP1 IZZAT .pdf`
  - Section `4.2.1` Table 4.1 (FR001-FR005)
  - Section `4.2.1.3` Tables 4.4, 4.5, 4.6 (sample UC flow specs)
  - Section `4.2.2` Table 4.7 (NFR summary)
  - Section `4.4` Figure 4.11 (domain diagram)
  - Section `4.5` Figures 4.12-4.20 (UI mockups)
- `STD_IZZAT AZZIM_A22MJ5014.pdf`
  - Section `2.1` (TC001_01 to TC001_04)
  - Section `2.2` (TC002_01 to TC002_08)
  - Section `2.3` (TC003_01 to TC003_04)
  - Appendix A (traceability matrix)

## 2) Functional Requirements (FR)

From FYP1 Table 4.1 and aligned with SRS Section 2.2:

- **FR001**: System automatically records sales and categorizes by salesperson, campaign, client.
- **FR002**: Accountants can generate and send invoices quickly with minimal manual effort.
- **FR003**: System sends WhatsApp reminders at 15/30/45 overdue days.
- **FR004**: Admin can assign roles and permissions.
- **FR005**: Management can view real-time revenue dashboards filtered by date/client.

## 3) Non-Functional Requirements (NFR)

### 3.1 NFR IDs (normalized from FYP1 Table 4.7 + SRS Section 2.4/2.5)

- **NFR001 Accessibility/Compatibility**: Works on modern browsers and responsive screen sizes.
- **NFR002 Security**: RBAC, secure auth, hashed passwords, HTTPS, controlled module access.
- **NFR003 Reliability**: Data integrity and document handling consistency.
- **NFR004 Scalability/Maintainability**: Modular design (Laravel MVC), future extension support.
- **NFR005 Performance**: Typical user actions should complete in under 3 seconds.
- **NFR006 Availability**: Continuous availability with minimal maintenance disruption.
- **NFR007 Backup/Recovery**: Daily backups + manual admin backup trigger.
- **NFR008 Capacity**: Up to 100 concurrent users and at least 50,000 transactions.
- **NFR009 Encryption/Compliance**: AES-256 for sensitive data at rest; PDPA-aware handling.

### 3.2 Design Constraints (SRS 2.5)

- Office and remote browser access assumptions (>=10 Mbps typical baseline).
- Server/runtime constraints (PHP 8.1+, MySQL 8.x, Apache/Nginx, 4 CPU/8GB RAM minimum).
- Mandatory secure audit trail for critical actions (invoice/payment/config changes).

## 4) Use Cases (UC001-UC008) with Flows

Consolidated primarily from SRS Section 2.3.x; FYP1 tables used for cross-check.

### UC001 Record Sale
- **Actor**: Sales Staff
- **Normal flow**:
  1. Select "New Sale"
  2. Enter client/campaign/amount
  3. Upload contract PDF
  4. Submit
- **Alternative flow**: Invalid data highlights validation errors.
- **Exception flow**: None specified.
- **Post-condition**: Sale stored and visible in dashboards.

### UC002 View Sales Dashboard
- **Actor**: Sales Staff (authorized users)
- **Normal flow**:
  1. Open dashboard
  2. Fetch revenue data
  3. Apply filters (date/client/salesperson)
  4. View updated charts/tables
- **Alternative flow**: No matching results -> "No data available".
- **Exception flow**: Server error/timeout -> show error and suggest refresh.
- **Post-condition**: Filtered real-time view shown.

### UC003 Generate Invoice
- **Actor**: Accountant
- **Normal flow**:
  1. Open completed campaign
  2. Click generate invoice
  3. System preloads sale/client data
  4. Select/customize template
  5. Confirm and save
- **Alternative flow**: Manual edits allowed for wrong autofill.
- **Exception flow**: Missing active default template blocks generation with explicit message.
- **Post-condition**: Invoice persisted.

### UC004 Track Payment Status
- **Actor**: Accountant
- **Normal flow**:
  1. Open payment status module
  2. Fetch unpaid invoices
  3. Evaluate due dates/status
  4. Trigger WhatsApp/email reminders at 15/30/45 days when overdue
  5. Update invoice status
- **Alternative flow**: Reminder API failure logged and retried.
- **Exception flow**: Template/service failure handling.
- **Post-condition**: Status/reminder outcomes stored.

### UC005 Generate Revenue Report
- **Actor**: Accountant/Management (reporting privilege)
- **Normal flow**:
  1. Select date range and filters
  2. Validate inputs
  3. Generate PDF/Excel report with totals, overdue, top campaigns
- **Alternative flow**: Export XLSX with raw data tabs.
- **Exception flow**: No data -> "No records match your criteria", suggest broader range.
- **Post-condition**: Report downloaded and audit trail logged.

### UC006 Export Client Statement
- **Actor**: Accountant
- **Normal flow**:
  1. Open reports
  2. Select client statement
  3. Fetch client transactions
  4. Generate PDF
  5. Download
- **Alternative flow**: CSV export.
- **Exception flow**: No client transactions; export failure with retry.
- **Post-condition**: Statement downloaded and export action logged.

### UC007 Manage User Accounts
- **Actor**: Administrator
- **Normal flow**:
  1. Select add user
  2. Assign role
  3. System sends login details
- **Alternative flow**: Duplicate email rejected.
- **Exception flow**: SRS/FYP include copy-paste inconsistency here (see clarification).
- **Post-condition**: Account created/updated, auditable.

### UC008 Configure System
- **Actor**: Administrator
- **Normal flow**:
  1. Open config panel
  2. Edit payment terms/reminder intervals/invoice template
  3. Save
  4. Validate and persist
- **Alternative flow**: Invalid format blocks save.
- **Exception flow**: DB save failure, retry allowed.
- **Post-condition**: Settings active system-wide, change logged.

## 5) Database Entities and Relationships

From SRS domain diagram references and implemented schema in project migrations:

- **roles** (1) -> (many) **users**
- **users** (salesperson_id) (1) -> (many) **sales**
- **clients** (1) -> (many) **sales**
- **sales** (1) -> (0..1) **invoices**
- **invoices** (1) -> (many) **payments**
- **invoices** (1) -> (many) **reminders**
- **invoices** (1) -> (many) **invoice_dispatches**
- **users** (1) -> (many) **reports_logs** via generated_by
- **users** (0..1) -> (many) **audit_logs**
- **users** (1) -> (many) **invoice_dispatches** via dispatched_by
- **users** (0..1) -> (many) **backups** via triggered_by
- **system_settings** singleton config record
- **backups** stores compressed DB backup metadata/status for manual + scheduled runs

Key data fields called out in SRS/FYP1/Appendix interview:
- client details, campaign info, amount, due dates, payment methods, partial payment support.

## 6) UI Pages and Key Elements

From FYP1 interface section and figures:

- **Figure 4.12 Login**
  - Email/password input, remember-me, sign-in action.
- **Figure 4.13 Dashboard**
  - Role-specific summary cards and trends.
- **Figure 4.14 Invoices (Accountant)**
  - Invoice list, filtering, generation actions.
- **Figure 4.15 Reports (Accountant)**
  - Financial reports, filtered revenue reporting, exports.
- **Figure 4.16 User Management (Admin)**
  - User list and role matrix actions.
- **Figure 4.17 Add/Edit User (Admin)**
  - Form for create/update role-scoped users.
- **Figure 4.18 Invoices (Sales Staff)**
  - Sales-visible invoice status list.
- **Figure 4.19 Record New Sale**
  - Sale form with client/campaign/amount and contract upload.
- **Figure 4.20 Clients**
  - Client listing and add-client flow.

### 6.1 Flash Message Contract (Standardized)

- Session flash keys are standardized app-wide to:
  - `success` (green): action completed
  - `error` (red): action failed or blocked
  - `warning` (amber): partial completion or caution
  - `info` (blue): neutral informational notice
- Shared renderer: `resources/views/components/flash-messages.blade.php`, included globally in `resources/views/layouts/app.blade.php`.
- Validation error bags continue to render in the same red error style through the same shared flash component for consistent UX.

### 6.2 Standard Empty-State Component

- Shared empty-state renderer: `resources/views/components/empty-state.blade.php`.
- Applied to key no-data surfaces (dashboard charts/payments, reports table, invoices table, clients list) to keep messaging and visual treatment consistent.

### 6.3 Role-Permission Documentation Source

- Canonical role-permission matrix source: `config/permissions.php`.
- The admin-facing `/permissions` page is read-only and renders directly from this config to avoid documentation drift.
- Matrix entries are required to stay aligned with actual route middleware enforcement in `routes/web.php`.

### 6.4 Security Patterns

- **Role middleware first gate**: route-level role checks are enforced by `EnsureUserHasRole` middleware for endpoint access.
- **Query-level data scoping for sales staff**: controller/service queries explicitly force sales staff scope to authenticated user data:
  - Dashboard filters force `salesperson_id = auth()->id()` and ignore tampered values.
  - Invoice listing and invoice detail access are constrained to invoices linked to `sale.salesperson_id = auth()->id()`.
  - Sales listing is constrained to own sales records for `sales_staff`.
- **Defense in depth**: middleware controls endpoint access; query scoping controls record visibility inside authorized endpoints.
- **Audit logging behavior**: current `audit_logs` observer tracks model create/update/delete style events; unauthorized route attempts blocked by middleware are not currently stored as audit log events.

## 7) STD Test Cases and Pass Criteria

### 7.1 Sales Module (TC001)

- **TC001_01** Add Sale with valid inputs  
  - Pass: new sale saved and visible.
- **TC001_02** Add Sale missing client name  
  - Pass: validation error shown ("Client Name is required").
- **TC001_03** View dashboard with available data  
  - Pass: dashboard shows transaction data.
- **TC001_04** Apply date filter no results  
  - Pass: filter bar remains visible, stat cards show zero values, chart and payments list show explicit empty-state messaging.

### 7.2 Finance Module (TC002)

- **TC002_01** Generate invoice from completed sale  
  - Pass: invoice generated and listed.
- **TC002_02** Generate invoice without template  
  - Pass: blocked with explicit error: "No invoice template configured. Please contact administrator."
- **TC002_03** Update payment status to Paid  
  - Pass: invoice status transitions to paid after payment confirmation.
- **TC002_04** Trigger overdue WhatsApp reminder  
  - Pass: reminder send attempt logged with appropriate success/failure status, fallback triggered on failure.
- **TC002_05** Generate revenue report in date range  
  - Pass: PDF and XLSX export available; XLSX includes Summary, Invoices, Payments, Sales raw-data sheets.
- **TC002_06** Generate revenue report with no data  
  - Pass: explicit no-records message.
- **TC002_07** Export client statement PDF  
  - Pass: client statement generated and downloaded.
- **TC002_08** Export client statement with no transactions  
  - Pass: clear no-transactions message.

### 7.3 Admin Module (TC003)

- **TC003_01** Create user with valid data  
  - Pass: user created.
- **TC003_02** Create user with duplicate email  
  - Pass: duplicate blocked with error.
- **TC003_03** Update payment terms setting  
  - Pass: setting saved and applied.
- **TC003_04** Set invalid reminder interval  
  - Pass: invalid value rejected with explicit error.

## 8) Conflicts and Ambiguities

### 8.1 Document conflicts (resolved by precedence)

- **Invoice sending channel**: SRS/STD mention email+WhatsApp in some places; FR003 in FYP1 is WhatsApp-specific.
  - Resolved as: WhatsApp is mandatory baseline, email optional unless explicitly required by acceptance.
- **UC007 exception/post-condition text**: appears copied from report use case in both FYP1/SRS (contains "No Data Found" and report download wording).
  - Resolved as: UC007 should be user-account outcomes, not report outcomes.
- **Figure numbering inconsistency** in FYP1 interface pages (repeated/misaligned numbering around 4.17-4.20).
  - Resolved as: rely on page titles/content, not repeated figure number tokens.
- **STD typo**: TC003_04 block labels ID as TC003_03.
  - Resolved as: use table-of-contents and heading intent (TC003_04).

### 8.2 Needs Clarification

- Is management role separate from admin/accountant in production RBAC?
- Is 60-day automatic service suspension (from interview appendix) mandatory in this release?
- Is CSV export mandatory for UC006, or PDF only acceptable for milestone?
- Is two-factor authentication mandatory now or future hardening scope?

## 9) Confirmed Scope Decisions (Phase 1 Clarifications)

- **FR002 finalized dispatch behavior**:
  - Invoice generation remains in UC003 flow and send is implemented as explicit action.
  - Dispatch endpoint: `POST /invoices/{invoice}/send` (role: `admin,accountant`).
  - Channel strategy: WhatsApp first; email fallback if WhatsApp fails and client email exists.
  - Re-send is explicitly allowed; each attempt creates a new `invoice_dispatches` row.
  - UI shows latest successful dispatch timestamp ("Last sent").
  - PDF artifact path is tracked in dispatch records for future WhatsApp media API integration.
- **Reminder channels (FR003/UC004)**: WhatsApp is mandatory; email is fallback when WhatsApp fails; both attempts are logged.
- **Role model**: no separate management role in PSM 2; management reporting capabilities are mapped to Admin.
- **Deferred items**:
  - 2FA
  - AES-256 sensitive field encryption
  - 60-day automatic service suspension
  - Email as mandatory parallel reminder channel
- **Mandatory auth baseline for PSM 2**:
  - Forgot-password flow is mandatory and uses Laravel password broker workflow.
  - Session timeout target is 2 hours.
  - HTTPS enforcement is mandatory at deployment.
- **UC006 finalized behavior**:
  - Dedicated statement template: `resources/views/reports/pdf/client-statement.blade.php`.
  - Scope includes invoiced financials only (invoices + payments); uninvoiced sales excluded.
  - No-data result shows inline web error only; no empty PDF generation.
  - Successful export only is logged in `report_logs` with `report_type = client_statement`.
- **UC005 finalized XLSX behavior**:
  - New endpoint: `POST /reports/revenue/export-xlsx` (role: `admin,accountant`).
  - Export uses same date/client/campaign filters as revenue report actions.
  - XLSX is multi-sheet with fixed tab order:
    1. `Summary` -> period, totals, overdue, top 5 campaigns
    2. `Invoices` -> raw invoice rows with finance columns
    3. `Payments` -> raw payment rows with method/reference
    4. `Sales` -> raw sales rows with campaign/status
  - No-data guard mirrors UC006: if invoices+payments+sales are all empty, show inline error and do not generate file or log.
  - Successful XLSX export logs `report_type = revenue_report_xlsx` in `report_logs`.
- **UC002 finalized filter behavior**:
  - Query params are persisted in URL (`start`, `end`, optional `client_id`, optional `salesperson_id`).
  - Default filter window is current calendar month.
  - Visibility and scope rules:
    - Admin: date + client + salesperson filters.
    - Accountant: date + client + salesperson filters.
    - Sales staff: date + client filters only; data is always scoped to authenticated salesperson even if URL is tampered.
  - Dashboard outputs affected by filters:
    - Stats: revenue, overdue %, active clients (with invoice in range), new payments.
    - Chart: invoice trend with adaptive granularity (<=90 days daily, 91-365 monthly, >365 quarterly).
    - Recent payments: latest 5 matching records.
  - No-data behavior:
    - Stats remain visible with zero values.
    - Chart shows: "No data available for the selected filters."
    - Payments list shows: "No payments match the selected filters."
- **Invoice dispatch tracking schema (`invoice_dispatches`)**:
  - `invoice_id` FK -> `invoices`
  - `channel` (`whatsapp`, `email`)
  - `dispatched_by` FK -> `users`
  - `status` (`sent`, `failed`)
  - `recipient`, `message_body`, nullable `error_message`
  - nullable `pdf_path`, `dispatched_at`, timestamps
- **Invoice template schema (`invoice_templates`)**:
  - `name`, `is_active`, `is_default`, `content`, nullable `created_by`, timestamps
  - Generation prerequisite: at least one active default template must exist before invoice creation.
- **NFR007 finalized backup behavior**:
  - Backup command: `php artisan backup:database` (scheduled trigger type).
  - Manual trigger path: admin settings action calls `backup:database --manual`.
  - Backup output location: `storage/app/backups/`.
  - Filename convention: `rotikaya-backup-{YYYY-MM-DD-HHmmss}.sql.gz`.
  - Retention policy: keep latest 30 backups, delete older DB rows + files.
  - Download is admin-only through authenticated route, served from non-public storage.
  - Scheduled backups do not create audit rows; manual trigger/download actions are audited.
- **Backups table schema (`backups`)**:
  - `filename` (unique), `file_path`, `file_size`
  - `trigger_type` (`scheduled`, `manual`), nullable `triggered_by`
  - `status` (`success`, `failed`), nullable `error_message`
  - `completed_at`, timestamps

## 10) Deployment Prerequisites

- PHP extensions required for reporting exports:
  - `ext-fileinfo` (Composer + DomPDF dependency path)
  - `ext-zip` (XLSX packaging via PhpSpreadsheet)
  - `ext-gd` (spreadsheet rendering dependencies)
- Composer package requirements:
  - `maatwebsite/excel` `3.1.69`
  - `phpoffice/phpspreadsheet` `1.30.4` (transitive dependency)
- Backup dependencies/ops:
  - `mysqldump` must be installed and accessible in server PATH.
  - Scheduler cron required in production:
    - `* * * * * cd /path/to/app && php artisan schedule:run`

## 11) Future Enhancements

### 11.1 WhatsApp Production Integration

- **Current state**:
  - The system uses a simulated WhatsApp provider with configurable modes (`always_success`, `always_fail`, `random`) via `services.whatsapp`.
  - This preserves realistic success/failure flows while keeping PSM 2 setup lightweight and deterministic for testing.
- **Why simulated in PSM 2**:
  - Real WhatsApp Business API requires business verification, approved templates, production phone assets, and recurring service costs.
  - These operational prerequisites are intentionally outside current academic scope.
- **Migration path to production provider**:
  1. Choose provider strategy: Meta Cloud API direct or Twilio WhatsApp.
  2. Install required SDK/package (e.g., `twilio/sdk` for Twilio path).
  3. Implement `WhatsAppApiDriver` behavior behind existing `WhatsAppService::send()` contract.
  4. Set `WHATSAPP_DRIVER=meta` or `WHATSAPP_DRIVER=twilio` in `.env`.
  5. Configure credentials (`WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`, `WHATSAPP_BUSINESS_PHONE_ID`, provider-specific keys).
  6. Keep `ReminderService` and `InvoiceDispatchService` unchanged; they already consume a stable send contract.
- **Provider references**:
  - [Meta WhatsApp Cloud API Documentation](https://developers.facebook.com/docs/whatsapp/cloud-api)
  - [Twilio WhatsApp API Documentation](https://www.twilio.com/docs/whatsapp)

## 12) Evaluation Demo Tips

- **Resilience demonstration script**:
  - Set `WHATSAPP_SIMULATE_MODE=always_fail` in `.env`.
  - Trigger reminder workflow and show:
    - WhatsApp attempt recorded as failed.
    - Email fallback attempt triggered and logged.
  - Reset `WHATSAPP_SIMULATE_MODE=always_success` after demo.

## 13) UI Design Conventions

Applies to all user-facing strings in Blade views, buttons, labels, flash messages, and empty states. Does **not** apply to database column names, route names, test assertions, or code identifiers.

### 13.1 Status Label Mapping (technical → user-facing)

| Database / Code Value | Displayed Label |
|---|---|
| `outstanding` / `unpaid` | Awaiting Payment |
| `partial` / `partial_paid` / `partially_paid` | Partially Paid |
| `coming_due` | Due Soon |
| `overdue` | Overdue |
| `paid` | Paid |

### 13.2 Status Badge Colors

| Label | Color |
|---|---|
| Paid | Green (`bg-emerald-500/10 text-emerald-400 border-emerald-500/20`) |
| Awaiting Payment | Gray (`bg-gray-500/10 text-gray-400 border-gray-500/20`) |
| Partially Paid | Amber (`bg-amber-500/10 text-amber-400 border-amber-500/20`) |
| Due Soon | Blue (`bg-blue-500/10 text-blue-400 border-blue-500/20`) |
| Overdue | Red (`bg-red-500/10 text-red-400 border-red-500/20`) |

Badge pill pattern: `inline-flex px-2.5 py-1 rounded-full text-xs font-semibold` + color classes above.

### 13.3 Customer vs Client Decision

**Locked to "Customer"** for all user-facing UI labels, buttons, placeholders, and headings.
Database columns (`client_id`, `clients` table, `sale.client`) remain unchanged — no migration needed.
Test assertions targeting database values use `client` terminology as before.

### 13.4 Terminology Replacements

| Technical / Old | User-facing |
|---|---|
| Insertion Order | Job Order |
| Salesperson | Sales Rep |
| Generate Invoice | Create Invoice |
| Dispatch | Send |
| User Account | User |
| System Configuration | Settings |
| Audit Trail | Activity Log |
| Reminder Intervals | When to send overdue reminders (in days) |
| Payment Terms (days) | Default payment due (days after invoice) |
| Permissions | Roles & Access |
| Client / Clients | Customer / Customers |
| Recent Payments | Latest Payments |

### 13.5 Button Hierarchy

| Tier | Style | Use cases |
|---|---|---|
| **Primary** | Solid red (`bg-[#c0392b] hover:bg-red-600 text-white`) | Save, Send, Create, Add, Record, Generate |
| **Secondary** | Ghost outline (`border border-gray-700 bg-transparent text-gray-300`) | Cancel, Reset, Back |
| **Tertiary** | Text-only with hover underline (`text-gray-400 hover:text-white hover:underline`) | View, Edit links in tables |
| **Destructive** | Solid red + Alpine.js confirmation modal | Delete |

### 13.6 Action Verb Conventions

Specific verbs are preferred over generic "Save":
- Save Settings
- Add Customer / Update Customer
- Record Sale
- Send Invoice
- Create Invoice
- Add User / Update User
- Reset Password

### 13.7 Help Text Convention

Style: `text-xs text-gray-400 mt-1.5`
Placement: below the field it describes, never above.
Length: 1–2 sentences maximum.
Example: "We'll send a WhatsApp reminder this many days after an invoice becomes overdue."

### 13.8 Required Field Indicator

Append `<span class="text-red-400">*</span>` after the label text for required fields.

### 13.9 Design DNA Reference

Original FYP1 mockups (Figures 4.12–4.20) establish the visual baseline:
- Dark theme (near-black backgrounds, `#0a0a0a` / `#0f0f0f`)
- Bold red accent (`#c0392b`) — "RÖTIKAYA" logo with red dot
- Rounded-2xl corners on cards and forms
- Fixed left sidebar navigation by role
- Stat cards in horizontal rows on dashboard (4-column grid)
- Status badge pills with colored background/text/border
- `Inter` body, `Space Grotesk` display, `JetBrains Mono` numbers

The current implementation preserves this design language while elevating spacing, typography scale (stat numbers at `text-4xl`), and badge polish. Changes are additive — the same visual DNA, refined.

## 14) Responsive Design

### 14.1 Breakpoint Mapping

| Tailwind prefix | Viewport | Context |
|---|---|---|
| (default) | < 640 px | Mobile portrait |
| `sm:` | ≥ 640 px | Mobile landscape / small tablet |
| `md:` | ≥ 768 px | Tablet |
| `lg:` | ≥ 1024 px | Desktop / laptop |
| `xl:` | ≥ 1280 px | Wide desktop (not used beyond natural Tailwind grids) |

### 14.2 Sidebar Behaviour

| Viewport | State | Width |
|---|---|---|
| < 768 px (mobile) | Hidden by default; opens as an overlay drawer (`x-show` + `-translate-x-full`) | 240 px (w-60) |
| 768–1023 px (tablet) | Always visible, icons-only (label `md:hidden`) | 60 px (w-[60px]) |
| ≥ 1024 px (desktop) | Always visible, icons + labels | 240 px (lg:w-60) |

Mobile overlay uses a semi-transparent backdrop (`bg-black/50`, `md:hidden`) that closes the drawer on tap. The sidebar uses `will-change-transform` and `transition-transform duration-300` for a hardware-accelerated slide.

### 14.3 Main Content Layout

```
pt-14 lg:pt-0          — offset for mobile top bar
ml-0 md:ml-[60px] lg:ml-60  — match sidebar width at each breakpoint
p-4  md:p-6  lg:p-8    — breathing room scales with viewport
max-w-7xl mx-auto      — inner wrapper caps line length on wide screens
```

### 14.4 Component-Level Responsive Rules

**Stat cards (dashboard)**
`grid-cols-1 → sm:grid-cols-2 → lg:grid-cols-4`
Number scale: `text-3xl lg:text-4xl`

**Tables**
- Strategy A (overflow-x-auto + `min-width` guard): Invoices, Payments, Backups, Reports.
  Progressive column hide: `hidden sm:table-cell` (method/channel), `hidden md:table-cell` (reference/details), `hidden lg:table-cell` (last login on desktop table).
- Strategy B (mobile cards + desktop table): Users, Customers.
  `md:hidden` card list for mobile; `hidden md:block` table for desktop.

**Forms / filter bars**
- Grid forms use `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3` (or similar) so fields stack on mobile.
- Filter bars are collapsible on mobile via `x-data="{ showFilters: false }"` Alpine.js toggle. At `md:` and above they are always shown using `md:!block` (important override of Alpine's inline `display:none`). The `md:\!block` CSS rule is declared in `app.css` under `@layer base`.

**Chart** `h-48 sm:h-64 lg:h-72`, `responsive: true`, `maintainAspectRatio: false`

### 14.5 Touch Targets

All interactive elements (nav links, buttons, icon-only controls) meet the 44 × 44 px minimum:
- Nav links: `px-3 py-3` (≥ 44 px tall at default font-size)
- Icon-only logout (tablet): `p-2` wrapper around `w-4 h-4` icon = 32 px; parent button `w-full` fills the 60 px sidebar column
- Mobile hamburger: `w-10 h-10` (40 px, near-44)
- Table action buttons: `px-3 py-2` minimum; delete modals use `px-4 py-2.5`

### 14.6 Long-Content Handling

- Customer / campaign names: `truncate` + `title="..."` on all table cells and detail views.
- Pagination links: present on all list views (invoices, payments, clients, users, sales, reports). Verified pattern: `@if(method_exists($collection, 'links')){{ $collection->links() }}@endif`.
- Empty states: `@forelse ... @empty` blocks on every table.

### 14.7 Avatar Initials

Multi-word names produce first + last initial: `explode(' ', name)` → `substr($parts[0], 0, 1) . substr(end($parts), 0, 1)`. Single-word names use the first letter only.

### 14.8 Test Coverage

`tests/Feature/ResponsiveSmokeTest.php` — 3 test methods cover every major authenticated route for `admin`, `accountant`, and `sales_staff` roles, asserting HTTP 200 after the responsive layout. These are pure HTTP tests (no Dusk / browser automation required).

## 15) Notification Delivery

This section is the canonical reference for how the system sends payment
reminders (FR003 / UC004) and invoices (FR002). Audited and confirmed
2026-06-04.

### 15.1 WHO receives notifications

Notifications go to the **customer on the invoice**, never to internal staff.
Both flows resolve contact details through `invoice → sale → client` (the
`clients` table = customers):

- WhatsApp recipient = `sale.client.phone`
- Email recipient = `sale.client.email`

The salesperson (`sale.salesperson_id`) and the dispatcher (the logged-in
accountant/admin) are **never** used as recipients. The dispatcher is recorded
only in `invoice_dispatches.dispatched_by` for audit purposes.

`clients.phone` and `clients.email` are both **nullable**. If a channel's
contact is missing, that channel is skipped and the failure reason is logged
(`"Client phone is missing."` / `"Client email is missing."`).

### 15.2 WHEN reminders are sent

- Intervals come from `system_settings.reminder_intervals` (default
  `[15, 30, 45]` days after `due_date`).
- A reminder for a given interval is sent once. Idempotency is enforced by the
  `reminders` table (`invoice_id` + `days_overdue`): if a row already exists for
  that interval, it is skipped on subsequent runs.
- Candidate invoices: status in `unpaid`, `partial`, `overdue`. The
  `daysOverdue >= interval` check excludes invoices that are not yet overdue.
- Triggers:
  - **Scheduled:** `php artisan reminders:send` runs daily at **09:00** via the
    Laravel scheduler (`routes/console.php`). Requires the OS cron entry
    `* * * * * php artisan schedule:run` (see §10).
  - **Manual:** accountant/admin click "Trigger Reminder Scan" on the dashboard
    → `POST /reminders/trigger` (`role:admin,accountant`).

> **Known limitation (by design):** because idempotency is keyed on the
> `reminders` row existing, a reminder whose WhatsApp **and** email both fail is
> **not** automatically retried on the next run (the row already exists with
> `status = failed`). UC004 AF1 ("retry after a fixed interval") is therefore
> only partially realised. Re-sending currently requires manual intervention.

### 15.3 WHAT channels (WhatsApp primary, email fallback)

1. WhatsApp is attempted first via `WhatsAppService::send($phone, $message)`.
2. Email (`Mail::raw`) is attempted **only if** WhatsApp fails (or the phone is
   missing).
3. If both fail, the failures are logged and the process continues to the next
   invoice — it never throws/crashes.

Invoice send (`InvoiceDispatchService`) follows the same primary→fallback order
and additionally attaches the generated invoice PDF to the email.

### 15.4 WHAT data is logged

| Flow | Table | Rows written |
|------|-------|--------------|
| Reminders | `reminders` | One row per interval — the idempotency/state record (`status`: pending→sent/failed). |
| Reminders | `payment_reminders` | Audit log: a `whatsapp` row **always**; an `email` row **only when** WhatsApp failed. Columns: `channel`, `status` (sent/failed), `recipient`, `message`, `error_message`, `sent_at`. |
| Invoice send | `invoice_dispatches` | Audit log: a `whatsapp` row, then an `email` row if WhatsApp failed. Columns: `channel`, `status`, `recipient`, `dispatched_by`, `message_body`, `error_message`, `pdf_path`, `dispatched_at`. |

The reminder message body (shared by both channels) includes: customer name,
invoice number, outstanding amount (RM), original due date, days overdue,
payment instructions (`config('company.bank_details')`), and the "Rotikaya
Media" reference.

### 15.5 HOW to switch from the WhatsApp simulator to production

Default `WHATSAPP_DRIVER=simulator` sends no real messages. **Setting the driver
to any other value currently makes every WhatsApp send fail** (returning an
"Unsupported driver" error) and fall back to email — switching the env var alone
does **not** enable real WhatsApp. To go live:

1. Implement a real driver branch in `WhatsAppService::send()` (Meta WhatsApp
   Business Cloud API or Twilio) — keep the `array{success, message_id, error,
   driver, simulated}` return contract stable so `ReminderService` and
   `InvoiceDispatchService` need no changes.
2. Set `WHATSAPP_DRIVER`, `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`,
   `WHATSAPP_BUSINESS_PHONE_ID`, `WHATSAPP_TEMPLATE_NAME` in `.env`
   (all present in `.env.example` and wired in `config/services.php`).

See also §11.1.

### 15.6 HOW to switch from the log mailer to real SMTP

Default `MAIL_MAILER=log` writes emails to `storage/logs` instead of sending
(so reminder/email "sent" status in dev means "written to log"). For production,
in `.env`:

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@rotikaya.com
MAIL_FROM_NAME="Rotikaya Media"
```

For invoice PDF attachments to work under SMTP, the generated file must exist at
`storage/app/public/<pdf_path>` (produced by `InvoiceService::storeInvoicePdf`).
