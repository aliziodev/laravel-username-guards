<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language to use when no specific language is specified.
    | This should match one of the languages in supported_locales.
    |
    | Default: 'en'
    |
    */
    'default_locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | List of languages supported by the package.
    | 'global' is a REQUIRED locale that applies to all languages and must be included.
    | Add other languages here as needed.
    |
    | Note: The 'global' locale is mandatory and cannot be disabled or removed.
    | Example: ['global', 'en', 'id', 'es', 'fr']
    |
    */
    'supported_locales' => [
        'global',    // Global words (REQUIRED, applies to all languages)
        'en',        // English
        'id',        // Indonesian
        // 'es',     // Spanish
        // 'fr',     // French
        // 'de',     // German
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtering Mode (Locale)
    |--------------------------------------------------------------------------
    |
    | The word filtering system will work based on these two flags:
    |
    | - check_all_locales = true
    |   -> Check all languages in 'supported_locales'
    |
    | - preferred_locale_only = true
    |   -> Override check_all_locales and only use [APP_LOCALE + global]
    |
    | If both are false → only APP_LOCALE will be used (without global)
    |
    */
    'check_all_locales' => env('WORD_FILTER_CHECK_ALL_LOCALES', true),
    'preferred_locale_only' => env('WORD_FILTER_PREFERRED_LOCALE_ONLY', false),

    /*
    |--------------------------------------------------------------------------
    | Character Normalization
    |--------------------------------------------------------------------------
    |
    | Enable or disable character normalization for word filtering.
    | When enabled, the system will normalize text by replacing common character
    | substitutions (like '0' to 'o', '1' to 'i', etc.) before checking for
    | prohibited words. This helps catch attempts to bypass filters using
    | character substitution.
    |
    | Example: When enabled, "k0nt0l" will be normalized to "kontol" for checking.
    |
    | - enabled: Enable/disable the entire normalization feature
    | - check_normalized_only: When true, only checks normalized text
    |   When false, checks both original and normalized text
    |
    */
    'normalization' => [
        'enabled' => env('WORD_FILTER_NORMALIZATION_ENABLED', true),
        'check_normalized_only' => env('WORD_FILTER_CHECK_NORMALIZED_ONLY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Word Categories
    |--------------------------------------------------------------------------
    |
    | Define all word categories here. Each category can be enabled/disabled.
    | Default categories are pre-configured, but you can add custom ones.
    |
    | Structure:
    | 'category-name' => true|false
    |
    | Example custom categories:
    | 'hate-speech' => true,
    | 'trademark' => true,
    |
    | Note: When adding custom categories, make sure to create corresponding word files
    | in resources/vendor/username-guard/words/{category}/ with proper structure:
    | - Create a directory with your category name (e.g., trademark/)
    | - Add locale-specific PHP files (e.g., en.php, id.php) for each supported language
    | - Add global.php for words that apply to all languages
    | - Each PHP file should return an array of words
    | - Words should be in lowercase
    | - No special characters except hyphens and underscores
    |
    | Example structure for 'trademark' category:
    | trademark/
    |   ├── global.php  (words for all languages)
    |   ├── en.php     (English-specific words)
    |   └── id.php     (Indonesian-specific words)
    |
    */

    'categories' => [
        // Default categories
        'profanity'      => true,
        'adult'          => true,
        'gambling'       => true,
        'religion_abuse' => true,
        'illegal'        => true,
        'spam'           => true,
        'reserved'       => true,
        'hate'           => true,
        'scam'           => true,

        // Optional categories
        'political'      => false,
        'trending'       => false,

        // Custom categories example:
        // 'hate-speech' => true,
        // 'trademark' => true,
    ],


    /*
    |--------------------------------------------------------------------------
    | Validation Patterns
    |--------------------------------------------------------------------------
    |
    | Pattern configuration for different validation scenarios.
    | These patterns are used to validate text format beyond word filtering.
    |
    | Available Patterns:
    | - username: For validating usernames
    | - domain or subdomain: For validating domain names
    | - basic: For basic text validation
    |
    */
    'patterns' => [
        // Character sets
        'sets' => [
            'alpha' => 'a-zA-Z',
            'numeric' => '0-9',
            'special' => '_-',
            'extra' => '.',
            'spaces' => '\s',
        ],

        // Common validation rules
        'rules' => [
            'start_alpha' => '/^[a-zA-Z]/',                 // Must start with letter
            'end_alphanumeric' => '/[a-z0-9]$/',            // Must end with letter/number
            'no_consecutive_dash' => '/[-]{2,}/',           // No consecutive dashes
            'no_consecutive_underscore' => '/[_]{2,}/',     // No consecutive underscores
            'no_consecutive_dot' => '/[.]{2,}/',            // No consecutive dots
            'no_consecutive_special_mix' => '/[._-]{2}/',   // No mixed special characters (dot, underscore, dash)
            'min_length' => 3,
            'max_length' => 63,
        ],

        // Predefined pattern combinations
        'presets' => [
            'basic' => [
                'allowed_chars' => '[^a-zA-Z0-9\s._-]',
                'rules' => [
                    'start_alpha',
                    'end_alphanumeric',
                    'no_consecutive_dash',
                    'no_consecutive_underscore',
                    'no_consecutive_dot',
                    'no_consecutive_special_mix'
                ],
                'min_length' => 3,
                'max_length' => 100,
            ],
            'username' => [
                'allowed_chars' => '[^a-zA-Z0-9_-]',
                'rules' => ['start_alpha', 'no_consecutive_dash', 'no_consecutive_underscore', 'no_consecutive_special_mix'],
                'min_length' => 3,
                'max_length' => 20,
            ],
            'domain' => [
                'allowed_chars' => '[^a-z0-9-]',
                'rules' => ['start_alpha', 'end_alphanumeric', 'no_consecutive_dash'],
                'min_length' => 5,
                'max_length' => 63,
            ],

        ],

        // Active patterns (enable/disable as needed)
        'active' => [
            'basic' => false,           // Disable basic pattern
            'username' => true,         // Enable username pattern
            'domain' => false,          // Disable domain or subdomain pattern
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for better performance.
    | Recommended to enable in production only.
    |
    */
    'cache' => [
        'enabled' => env('WORD_FILTER_CACHE_ENABLED', true),    // Enabled by default
        'ttl'     => env('WORD_FILTER_CACHE_TTL', 86400),       // 24 hours
        'store'   => env('WORD_FILTER_CACHE_STORE', null),      // Cache store: file, redis, etc
    ],
];
