<?php

return [
    'name' => env('APP_NAME', 'RentOS'),
    'currency' => env('RENTOS_CURRENCY', 'BDT'),
    'timezone' => env('RENTOS_TIMEZONE', 'Asia/Dhaka'),

    'notice_days' => (int) env('RENTOS_NOTICE_DAYS', 30),
    'renewal_offer_days' => (int) env('RENTOS_RENEWAL_OFFER_DAYS', 60),
    'late_fee_grace_days' => (int) env('RENTOS_LATE_FEE_GRACE_DAYS', 5),
    'late_fee_percent' => (float) env('RENTOS_LATE_FEE_PERCENT', 5),
    'platform_fee_percent' => (float) env('RENTOS_PLATFORM_FEE_PERCENT', 2.5),

    'supported_currencies' => ['BDT', 'USD', 'AUD', 'EUR', 'GBP', 'INR'],
    'supported_countries' => ['BD', 'US', 'AU', 'GB', 'IN', 'CA', 'AE'],

    'gateways' => [
        'stripe' => env('RENTOS_GATEWAY_STRIPE', false),
        'sslcommerz' => env('RENTOS_GATEWAY_SSLCOMMERZ', true),
        'bkash' => env('RENTOS_GATEWAY_BKASH', true),
        'nagad' => env('RENTOS_GATEWAY_NAGAD', true),
    ],

    'assistant_permissions' => [
        'view_finances' => 'View owner P&L and full financial reports',
        'collect_rent' => 'Record and reconcile rent payments',
        'approve_maintenance' => 'Approve maintenance cost estimates',
        'sign_leases' => 'Sign leases on the owner\'s behalf',
        'manage_listings' => 'Publish and edit listings without approval',
        'process_applications' => 'Approve or reject tenant applications',
        'manage_staff' => 'Invite and manage other staff',
        'message_tenants' => 'Message tenants and send announcements',
        'manage_vendors' => 'Assign vendors and approve invoices',
        'view_reports' => 'View portfolio analytics',
        'manage_documents' => 'Upload and delete vault documents',
        'schedule_showings' => 'Schedule property viewings',
        'log_inspections' => 'Create move-in/out inspections',
    ],
];
