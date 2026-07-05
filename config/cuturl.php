<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local JSON storage location
    |--------------------------------------------------------------------------
    |
    | CutURL is local-first and does not use a database. Every shortened link
    | is persisted to this single JSON file. The folder and file are created
    | automatically on first use if they do not already exist.
    |
    */

    'storage_path' => storage_path('app/cuturl/links.json'),

    /*
    |--------------------------------------------------------------------------
    | Short code generation
    |--------------------------------------------------------------------------
    |
    | Auto-generated codes use the character set below at the configured
    | length. Custom aliases are limited to the alias character set.
    |
    */

    'code_length' => 6,

    'code_alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',

    // Custom aliases may only contain letters, numbers, hyphens and underscores.
    'alias_pattern' => '/^[A-Za-z0-9_-]+$/',

    'alias_max_length' => 64,

    /*
    |--------------------------------------------------------------------------
    | Reserved codes
    |--------------------------------------------------------------------------
    |
    | These words collide with real application routes / paths and can never
    | be used as a short code or custom alias.
    |
    */

    'reserved_codes' => [
        'dashboard',
        'shorten',
        'links',
        'admin',
        'login',
        'register',
        'api',
        'assets',
        'storage',
        'up',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limiting
    |--------------------------------------------------------------------------
    |
    | Maximum number of shorten requests allowed per minute, per client IP.
    |
    */

    'shorten_rate_limit' => 20,

];
