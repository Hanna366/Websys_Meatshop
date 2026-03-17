<?php

return [
    // Domain used when a tenant domain is not provided.
    // Typically this should be the root domain for tenant subdomains.
    // Example: 'localhost' or 'example.test'.
    'fallback_domain' => env('TENANT_ROOT_DOMAIN', 'localhost'),

    // Whether to allow requesting tenants by hostname when no tenant is found.
    // This is useful for showing a landing page on the central app.
    'allow_public_fallback' => true,
];
