# Laravel Username Guards

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aliziodev/laravel-username-guards.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)
[![Total Downloads](https://img.shields.io/packagist/dt/aliziodev/laravel-username-guards.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)
[![PHP Version](https://img.shields.io/packagist/php-v/aliziodev/laravel-username-guards.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)
[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-username-guards)

Laravel Username Guards is a comprehensive package for validating usernames in Laravel applications. It provides robust validation features including pattern matching and prohibited word filtering across multiple languages.

## Features

-   Pattern-based username validation (length, allowed characters, format)
-   Prohibited word filtering in multiple categories (profanity, adult, spam, etc.)
-   Multi-language support (en, id, and extendable)
-   Flexible configuration
-   Easy integration with Laravel Validation
-   Facade for direct usage
-   Caching for better performance

## Installation

Install the package via Composer:

```bash
composer require aliziodev/laravel-username-guards
```

## Console Commands

```bash
php artisan username-guard:install
```

This command:

-   Publishes configuration file
-   Publishes word resources
-   Sets up basic configuration

Cache Clearing Command:

```bash
php artisan username-guard:clear
```

This command clears all caches related to the package:

-   Configuration cache
-   Application cache
-   Package discovery cache

## Configuration

After publishing the configuration file, you can customize various options in config/username-guard.php :

### Language Settings

```php
// Default language
'default_locale' => env('APP_LOCALE', 'en'),

// Supported languages
'supported_locales' => [
'global', // Global words (REQUIRED, applies to all languages)
'en', // English
'id', // Indonesian
// Add other languages as needed
],
```

### Filtering Mode

```php
// Check all supported languages
'check_all_locales' => env('WORD_FILTER_CHECK_ALL_LOCALES', true),

// Only use default language + global
'preferred_locale_only' => env('WORD_FILTER_PREFERRED_LOCALE_ONLY', false),
```

### Word Categories

```php
'categories' => [
// Default categories
'profanity' => true,
'adult' => true,
'gambling' => true,
'religion_abuse' => true,
'illegal' => true,
'spam' => true,
'reserved' => true,
'hate' => true,
'scam' => true,

    // Optional categories
    'political'      => false,
    'trending'       => false,

    // Custom category example
    // 'trademark' => true,

],
```

### Validation Patterns

```php
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
        // ...
    ],

    // Pattern presets
    'presets' => [
        'username' => [
            'allowed_chars' => '[^a-zA-Z0-9_-]',
            'rules' => ['start_alpha', 'no_consecutive_dash', 'no_consecutive_underscore', 'no_consecutive_special_mix'],
            'min_length' => 3,
            'max_length' => 20,
        ],
        // ...
    ],

    // Active patterns
    'active' => [
        'username' => true,      // Enable username pattern
        // ...
    ],

],
```

### Cache Settings

```php
'cache' => [
'enabled' => env('WORD_FILTER_CACHE_ENABLED', true), // Enabled by default
'ttl' => env('WORD_FILTER_CACHE_TTL', 86400), // 24 hours
'store' => env('WORD_FILTER_CACHE_STORE', null), // Cache store: file, redis, etc
],
```

## Usage

### Using Validation Rule

The easiest way to use this package is with the UsernameRule in Laravel validation:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Aliziodev\UsernameGuard\Rules\UsernameRule;

class UpdateUsernameRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', new UsernameRule()],
        ];
    }
}
```

Then use the form request in your controller:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UpdateUsernameRequest;

class UsernameController extends Controller
{
    /**
     * Check username availability using UsernameRule.
     *
     * @param UpdateUsernameRequest $request
     * @return JsonResponse
     */
    public function index(UpdateUsernameRequest $request): JsonResponse
    {
        // If we reach here, validation has passed
        return response()->json([
            'valid' => true,
            'username' => $request->username,
            'message' => 'Username is valid and can be used'
        ]);
    }
}
```

### Using Facade

You can also use the Username facade for direct validation:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Aliziodev\UsernameGuard\Facades\Username;

class UsernameController extends Controller
{
    /**
     * Check username availability.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $username = $request->get('username');

        if (empty($username)) {
            return response()->json([
                'valid' => false,
                'message' => 'Username cannot be empty'
            ], 422);
        }

        $isValid = Username::isValid($username);

        return response()->json([
            'valid' => $isValid,
            "username" => $username,
            'message' => $isValid ? 'Username is valid and can be used' : 'Username is invalid or contains prohibited words'
        ]);
    }
}
```

### Using Service Directly

For more control, you can use the UsernameService directly:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Aliziodev\UsernameGuard\Services\UsernameService;

class CustomUsernameController extends Controller
{
    protected $usernameService;

    public function __construct(UsernameService $usernameService)
    {
        $this->usernameService = $usernameService;
    }

    public function validateUsername(Request $request): JsonResponse
    {
        $username = $request->get('username');
        $type = $request->get('type', 'all'); // 'all', 'pattern', or 'word'

        $isValid = false;

        // Validate based on type
        if ($type === 'pattern') {
            $isValid = $this->usernameService->isValid($username, 'pattern');
        } elseif ($type === 'word') {
            $isValid = $this->usernameService->isValid($username, 'word');
        } else {
            $isValid = $this->usernameService->isValid($username);
        }

        // Get error if validation fails
        $error = $this->usernameService->getLastError();
        $exception = $this->usernameService->getLastException();

        return response()->json([
            'valid' => $isValid,
            'username' => $username,
            'error' => $error,
            'details' => $exception ? [
                'type' => $exception->getValidationType(),
                'context' => $exception->getContext()
            ] : null,
            'message' => $isValid ? 'Username is valid' : 'Username is invalid: ' . $error
        ]);
    }
}
```

## Adding Custom Categories

To add custom prohibited word categories:

1. Add the category in configuration:

```php
'categories' => [
    // Default categories...

    // Custom category
    'trademark' => true,
],
```

2. Create directory structure and word files:

```bash
resources/vendor/username-guard/words/trademark/
  ├── global.php  (words for all languages)
  ├── en.php      (English-specific words)
  └── id.php      (Indonesian-specific words)
```

3. Fill the files with arrays of prohibited words:

```php
<?php

/**
 * Trademark Words - Global
 */

return [
    'facebook',
    'instagram',
    'twitter',
    'tiktok',
    // Add more words...
];
```

## Handling Errors

When validation fails, you can get error information:

```php
// Check validity
$isValid = $usernameService->isValid($username);

if (!$isValid) {
    // Get error message
    $errorMessage = $usernameService->getLastError();

    // Get exception for more detailed information
    $exception = $usernameService->getLastException();

    if ($exception) {
        $validationType = $exception->getValidationType(); // 'pattern' or 'word'
        $context = $exception->getContext(); // Additional information about the error

        // Handle based on validation type
        if ($validationType === 'pattern') {
            // Pattern error (length, characters, etc.)
            $rule = $context['rule'] ?? 'unknown';
            // ...
        } elseif ($validationType === 'word') {
            // Prohibited word error
            $category = $context['category'] ?? 'unknown';
            $locale = $context['locale'] ?? 'unknown';
            // ...
        }
    }
}
```

## Customizing Error Messages

You can customize error messages when using UsernameRule :

```php
// Default error message
$rule = new UsernameRule();

// Custom error message
$rule = (new UsernameRule())
    ->setMessage('Username is invalid or contains prohibited words.');
```

## License

This package is licensed under the MIT License.

## Contributing

Contributions are welcome! Please create issues or pull requests on the GitHub repository.
