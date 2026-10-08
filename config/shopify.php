<?php
return [
    'url' => env('APP_URL', 'http://localhost'),
    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET_KEY'),
    'scopes' => env('SHOPIFY_SCOPES', 'read_products,read_inventory,read_locations,read_script_tags,write_script_tags,read_customers,write_customers'),
    'url' => env('SHOPIFY_APP_URL', env('APP_URL')),
    'embedded' => env('SHOPIFY_EMBEDDED', true),
];
