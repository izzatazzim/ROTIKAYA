# Contrast Audit (Phase 3B.3)

Pragmatic dark-theme contrast/visibility audit performed before fixes.

## Issues Found

1. **Login heading dark-on-dark**
   - **File**: `resources/css/app.css`
   - **Problematic style**: `.rtk-login-heading { color: #150304; }`
   - **Element**: Login card heading text over dark red gradient card.

2. **Input placeholders too dim on dark fields**
   - **File**: `resources/views/auth/login.blade.php`
   - **Problematic class**: `placeholder-gray-500`
   - **Element**: Email and password placeholders.

3. **Input placeholders too dim on forgot/reset forms**
   - **Files**:
     - `resources/views/auth/forgot-password.blade.php`
     - `resources/views/auth/reset-password.blade.php`
   - **Problematic class**: `placeholder-gray-500`
   - **Element**: Email/password placeholders.

4. **Interactive controls missing clearly visible focus ring**
   - **Files**:
     - `resources/views/layouts/app.blade.php`
     - `resources/views/dashboard/_content.blade.php`
     - `resources/views/invoices/index.blade.php`
     - `resources/views/users/index.blade.php`
     - `resources/views/users/edit.blade.php`
     - `resources/views/settings/index.blade.php`
     - `resources/views/reports/index.blade.php`
     - `resources/views/reports/client-statement.blade.php`
     - `resources/views/payments/index.blade.php`
     - `resources/views/clients/index.blade.php`
   - **Problematic pattern**: `focus:outline-none` and hover-only interactions without `focus-visible:ring-*`.
   - **Elements**: Form inputs, selects, action buttons, links, sidebar navigation, logout button.

5. **Disabled backup action not visually distinct enough**
   - **File**: `resources/views/settings/index.blade.php`
   - **Problematic class**: Button uses `:disabled` without disabled styling classes.
   - **Element**: “Run Backup Now” button while loading.

6. **Low-emphasis metadata text slightly too dim on dark cards**
   - **File**: `resources/views/invoices/show.blade.php`
   - **Problematic class**: `text-gray-500`
   - **Element**: Dispatch timestamp line in history cards.

7. **Error message region duplicated style path**
   - **File**: `resources/views/reports/client-statement.blade.php`
   - **Problematic pattern**: Legacy `@error('client_statement')` block while global flash/error component exists.
   - **Element**: Error banner consistency and readability path across pages.

## Non-Issues (checked, no change needed)

- Sidebar active state (`bg-[#c0392b] text-white`) remains readable.
- Status badges in users/invoices/payments have readable foreground contrast.
- Table hover backgrounds (`hover:bg-[#262626]`) are visible and not overly bright.
- Flash/empty-state components from 3B.2 are already readable across severity types.
