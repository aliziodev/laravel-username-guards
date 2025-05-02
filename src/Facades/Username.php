<?php

namespace Aliziodev\UsernameGuard\Facades;

use Illuminate\Support\Facades\Facade;
use Aliziodev\UsernameGuard\Services\UsernameService;

/**
 * Username Guard Facade
 *
 * A facade providing a simple interface to the Username Guard service for validating usernames.
 * This service offers comprehensive validation including pattern matching and prohibited word filtering.
 *
 * Features:
 * - Pattern-based validation (length, characters, format)
 * - Prohibited word checking with multi-locale support
 * - Caching mechanism for improved performance
 * - Multiple validation presets (username, domain, basic)
 *
 * @package Aliziodev\UsernameGuard
 *
 * @method static bool isValid(string $text, ?string $type = null)
 * Validates a username against configured rules and filters.
 * @param string $text The username to validate
 * @param string|null $type Optional validation type: 'pattern', 'words', or null for both
 * @return bool True if validation passes, false otherwise
 *
 * @method static self setLocale(?string $locale)
 * Sets the locale for word filtering.
 * @param string|null $locale The locale code to set (e.g., 'en', 'id')
 * @return self For method chaining
 *
 * @method static array getWordsByCategory(string $category, ?string $locale = null)
 * Retrieves prohibited words for a specific category.
 * @param string $category The word category to retrieve
 * @param string|null $locale Optional locale override
 * @return array List of prohibited words
 *
 * @see \Aliziodev\UsernameGuard\Services\UsernameService
 * @since 1.0.0
 * @author Alizio <aliziodev@gmail.com>
 */
class Username extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * This method returns the binding key for the Username Guard service
     * in the Laravel service container.
     *
     * @return string The fully qualified class name of the service
     */
    protected static function getFacadeAccessor(): string
    {
        return UsernameService::class;
    }
}
