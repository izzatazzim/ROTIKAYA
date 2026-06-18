# VIVA Demo Walkthrough Script

This script is designed for PSM 2 live demonstration with two timing options.

## 5-Minute Demo (Must-Show)

### Step 1: Login and role-based landing
- What to click/show:
  - Open login page, sign in as `admin`, then briefly show `sales_staff` role session.
- Requirement reference:
  - FR004, UC007, NFR002
- Expected result:
  - Role-appropriate navigation and page access.
- Likely evaluator question:
  - "How do you enforce role restrictions?"
- Suggested answer:
  - "Routes use role middleware and we added role-boundary regression tests covering authorized, unauthorized, and guest scenarios."

### Step 2: Record sale + validation guard
- What to click/show:
  - Go to Sales, create a valid sale; then submit once with missing client to show validation.
- Requirement reference:
  - FR001, UC001, `TC001_01`, `TC001_02`
- Expected result:
  - Valid sale saved; invalid attempt blocked with validation error.
- Likely evaluator question:
  - "Is this behavior tested explicitly?"
- Suggested answer:
  - "Yes, both paths are mapped in STD traceability with dedicated `test_TC...` methods and `@std` tags."

### Step 3: Invoice + payment status lifecycle
- What to click/show:
  - Generate invoice from completed sale, then record payment and show status update.
- Requirement reference:
  - FR002, UC003, UC004, `TC002_01`, `TC002_03`
- Expected result:
  - Invoice appears; payment moves status to paid.
- Likely evaluator question:
  - "What prevents invalid generation conditions?"
- Suggested answer:
  - "Invoice generation is blocked when no active default template exists, with explicit user feedback and test coverage (`TC002_02`)."

### Step 4: WhatsApp failure demo with email fallback
- What to click/show:
  - Set `.env` to `WHATSAPP_SIMULATE_MODE=always_fail`.
  - Trigger invoice send or reminder process, then inspect dispatch/reminder records.
- Requirement reference:
  - FR003, UC004, `TC002_04`
- Expected result:
  - WhatsApp attempt marked failed, fallback email attempt marked sent.
- Likely evaluator question:
  - "Is this a real WhatsApp integration?"
- Suggested answer:
  - "Current build uses a simulator for deterministic testing; production API migration path is documented in `docs/PROJECT_SPEC.md`."

### Step 5: Reporting evidence + test proof
- What to click/show:
  - Export revenue report (XLSX) and client statement (PDF), then show test/doc evidence.
- Requirement reference:
  - FR005, UC005, UC006, `TC002_05` to `TC002_08`
- Expected result:
  - Export downloads successfully; no-data flow shows explicit error.
- Likely evaluator question:
  - "How do we know all STD TCs are covered?"
- Suggested answer:
  - "Use `docs/STD_TRACEABILITY.md` and run `php artisan test --filter=TC0` to execute all STD-mapped tests directly."

## 15-Minute Demo (Comprehensive)

### Part A: Architecture and scope framing (2 min)
- What to click/show:
  - Open `docs/SUBMISSION_READINESS.md`, `docs/PROJECT_SPEC.md`, and `docs/STD_TRACEABILITY.md`.
- Requirement reference:
  - FR001-FR005, UC001-UC008, NFR002, NFR007
- Expected result:
  - Evaluator sees explicit traceability and implementation status.
- Likely evaluator question:
  - "What is deferred and why?"
- Suggested answer:
  - "Deferred items are listed in submission readiness; they are enhancement-level and do not block current evaluation criteria."

### Part B: Sales flow and dashboard filtering (3 min)
- What to click/show:
  - Create sale, then open dashboard and apply date/client filters.
  - Show empty-state behavior for no-data range.
- Requirement reference:
  - FR001, FR005, UC001, UC002, `TC001_01`, `TC001_03`, `TC001_04`
- Expected result:
  - Data updates by filters; no-data messaging is explicit and readable.
- Likely evaluator question:
  - "Can sales staff access others' data using URL tampering?"
- Suggested answer:
  - "No. We enforce scope at query level in addition to middleware; tampering cases are covered in role-boundary tests."

### Part C: Invoice, payment, and reminders (4 min)
- What to click/show:
  - Generate invoice from completed sale.
  - Record payment and show status transition.
  - Demonstrate overdue reminder logging behavior.
- Requirement reference:
  - FR002, FR003, UC003, UC004, `TC002_01` to `TC002_04`
- Expected result:
  - End-to-end finance workflow works and is auditable.
- Likely evaluator question:
  - "How do you handle failure scenarios?"
- Suggested answer:
  - "Failure paths are explicit: template guard, WhatsApp failure fallback, and validation errors are all tested."

### Part D: Reports and exports (2 min)
- What to click/show:
  - Run revenue XLSX export and client statement PDF.
  - Show no-data export rejection message.
- Requirement reference:
  - FR005, UC005, UC006, `TC002_05` to `TC002_08`
- Expected result:
  - Correct file outputs; no-data cases blocked with clear feedback.
- Likely evaluator question:
  - "Why both PDF and XLSX?"
- Suggested answer:
  - "PDF supports formal reporting and statements; XLSX supports analysis with raw sheets (Summary, Invoices, Payments, Sales)."

### Part E: Admin controls, security, and backup (3 min)
- What to click/show:
  - Create user (valid + duplicate email rejection).
  - Update payment terms/reminder interval (valid and invalid).
  - Trigger manual backup and show retention behavior explanation.
  - Show route middleware output with `php artisan route:list -v`.
- Requirement reference:
  - FR004, UC007, UC008, NFR002, NFR007, `TC003_01` to `TC003_04`
- Expected result:
  - Admin flows are controlled, validated, and persisted with expected guardrails.
- Likely evaluator question:
  - "How do you verify submission quality quickly?"
- Suggested answer:
  - "Run three commands: `php artisan test`, `php artisan test --filter=TC0`, and `php artisan test --filter=Role`."

### Part F: Final proof (1 min)
- What to click/show:
  - Terminal results for:
    - `php artisan test`
    - `php artisan migrate:fresh --seed`
    - `php artisan route:list -v`
- Requirement reference:
  - Overall readiness and reproducibility
- Expected result:
  - Green tests, clean install success, and enforced route middleware map.
- Likely evaluator question:
  - "What would you improve next?"
- Suggested answer:
  - "Production WhatsApp API integration, cloud backups, 2FA, and deeper export/reminder test hardening are planned next."
