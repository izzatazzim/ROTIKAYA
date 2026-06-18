<?php

/**
 * Role-Permission Matrix (Documentation Source)
 *
 * This config is the canonical reference for role permissions, displayed
 * to administrators on /permissions. It MUST match the actual enforcement
 * in routes/web.php middleware and controller authorization checks.
 *
 * When adding new routes/features:
 * 1. Add the route with appropriate role middleware
 * 2. Add the permission key to the relevant role(s) in this config
 * 3. Add the description in permission_descriptions
 *
 * Drift between this file and actual enforcement is a bug.
 */
return [
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Full system access including user management, settings, backups, and role documentation.',
            'permissions' => [
                'dashboard.view',
                'sales.view',
                'sales.manage',
                'clients.view',
                'clients.manage',
                'invoices.view',
                'invoices.create',
                'invoices.send',
                'payments.view',
                'payments.create',
                'reports.view',
                'reports.export',
                'reminders.trigger',
                'users.manage',
                'settings.manage',
                'backup.run',
                'backup.download',
                'permissions.view',
            ],
        ],
        'accountant' => [
            'label' => 'Accountant',
            'description' => 'Manages invoicing, payments, reminders, and financial reporting workflows.',
            'permissions' => [
                'dashboard.view',
                'invoices.view',
                'invoices.create',
                'invoices.send',
                'payments.view',
                'payments.create',
                'reports.view',
                'reports.export',
                'reminders.trigger',
            ],
        ],
        'sales_staff' => [
            'label' => 'Sales Staff',
            'description' => 'Records sales transactions, manages clients, and views role-scoped invoices/dashboard data.',
            'permissions' => [
                'dashboard.view',
                'sales.view',
                'sales.manage',
                'clients.view',
                'clients.manage',
                'invoices.view',
            ],
        ],
    ],
    'permission_descriptions' => [
        'dashboard.view' => 'View dashboard with role-scoped filters and statistics.',
        'sales.view' => 'View the sales entry page and related sales lists.',
        'sales.manage' => 'Create and manage sales records.',
        'clients.view' => 'View client listing pages.',
        'clients.manage' => 'Create and manage client records.',
        'invoices.view' => 'View invoice list and invoice detail pages.',
        'invoices.create' => 'Generate new invoices from completed sales.',
        'invoices.send' => 'Send invoices through the configured dispatch flow.',
        'payments.view' => 'View payment listing and payment module.',
        'payments.create' => 'Record new payments and trigger invoice status refresh.',
        'reports.view' => 'Access reporting dashboard pages and filters.',
        'reports.export' => 'Export report artifacts (PDF/XLSX/client statement).',
        'reminders.trigger' => 'Run manual reminder scan trigger.',
        'users.manage' => 'Create, edit, and delete user accounts.',
        'settings.manage' => 'Update system configuration settings.',
        'backup.run' => 'Run manual database backup jobs.',
        'backup.download' => 'Download generated backup files.',
        'permissions.view' => 'View role-permission documentation matrix.',
    ],
];
