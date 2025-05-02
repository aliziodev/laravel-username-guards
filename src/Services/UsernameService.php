<?php

namespace Aliziodev\UsernameGuard\Services;

use Illuminate\Support\Facades\Cache;
use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;

/**
 * Username Guard Service
 *
 * A comprehensive service for validating usernames based on configurable patterns and prohibited words.
 * Features include:
 * - Pattern-based validation (length, characters, format)
 * - Prohibited word checking
 * - Multi-locale support
 * - Caching mechanism
 * - Multiple validation presets (username, domain, basic)
 *
 * @package Aliziodev\UsernameGuard
 * @version 1.0.0
 * @author Alizio Dev
 */
class UsernameService
{
    /**
     * Main service configuration array containing all settings
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Active word categories for validation
     * Maps category names to their enabled status
     *
     * @var array
     */
    protected array $categories = [];

    /**
     * Custom word categories defined by the user
     * Extends the default categories with application-specific ones
     *
     * @var array
     */
    protected array $customCategories = [];

    /**
     * Default word categories provided by the package
     * Contains base prohibited word lists
     *
     * @var array
     */
    protected array $defaultCategories = [];

    /**
     * Cache configuration settings
     * Contains cache status, TTL, and other cache-related options
     *
     * @var array
     */
    protected array $cacheConfig = [];

    /**
     * Validation patterns configuration
     * Contains pattern sets, rules, and presets for username validation
     *
     * @var array
     */
    protected array $patterns = [];

    /**
     * Current active locale for word validation
     * Used for loading locale-specific prohibited words
     *
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     *  Store the last error message that occurred
     *
     * @var string|null
     */
    protected ?string $lastError = null;

    /**
     * Store the last exception that occurred
     *
     * @var UsernameGuardException|null
     */
    protected ?UsernameGuardException $lastException = null;

    /**
     * Initialize the username guard service with configuration
     *
     * Sets up the service with provided configuration or falls back to config file.
     * Initializes categories, patterns, and locale settings.
     *
     * @param array $config Custom configuration array (optional)
     */
    public function __construct(array $config = [])
    {
        $this->config = $config ?: config('username-guard');

        $this->categories = $this->config['categories'] ?? [];
        $this->customCategories = $this->config['custom_categories'] ?? [];
        $this->defaultCategories = $this->config['default_categories'] ?? [];

        $this->cacheConfig = $this->config['cache'] ?? ['enabled' => false];
        $this->patterns = $this->loadPatterns($this->config['patterns'] ?? []);

        $this->locale = $this->config['default_locale'] ?? 'en';

        // Validate configuration
        $this->validateConfiguration();
    }

    /**
     * Validate basic configuration requirements
     * 
     * This method checks if the essential configuration parameters are properly set:
     * - Ensures that a default locale is configured
     * - Verifies that the 'global' locale is included in supported_locales
     *
     * @throws UsernameGuardException If configuration validation fails
     */
    protected function validateConfiguration(): void
    {
        // validate default locale
        if (empty($this->locale)) {
            throw new UsernameGuardException(
                'Default locale is not configured',
                'configuration',
                '',
                ['config' => 'default_locale']
            );
        }

        // Validate supported locales
        if (empty($this->config['supported_locales']) || !in_array('global', $this->config['supported_locales'])) {
            throw new UsernameGuardException(
                "The 'global' locale is required in supported_locales",
                'configuration',
                '',
                ['config' => 'supported_locales']
            );
        }
    }

    /**
     * Set the validation locale for word checks
     *
     * Changes the active locale for subsequent word validations.
     * Affects which locale-specific word lists are used.
     *
     * @param string|null $locale The locale code to set (e.g., 'en', 'es')
     * @return self For method chaining
     */
    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Load and structure validation patterns from configuration
     *
     * Organizes pattern configuration into structured arrays for validation use.
     * Includes pattern sets, rules, presets, and active status.
     *
     * @param array $patterns Raw pattern configuration array
     * @return array Structured patterns array
     */
    protected function loadPatterns(array $patterns): array
    {
        if (empty($patterns)) {
            return [];
        }

        return [
            'sets' => $patterns['sets'] ?? [],
            'rules' => $patterns['rules'] ?? [],
            'presets' => $patterns['presets'] ?? [],
            'active' => $patterns['active'] ?? []
        ];
    }

    /**
     * Retrieve prohibited words for a specific category with caching
     *
     * Gets words from the specified category, using cache if enabled.
     * Supports locale-specific word lists.
     *
     * @param string $category The word category to retrieve
     * @param string|null $locale Optional locale override
     * @return array List of prohibited words
     * @throws UsernameGuardException If loading words fails
     */
    public function getWordsByCategory(string $category, ?string $locale = null): array
    {
        $locale = $locale ?? $this->locale ?? $this->config['default_locale'];
        $cacheKey = "username_guard_words_{$category}_{$locale}";

        // Check if caching is enabled
        if ($this->cacheConfig['enabled']) {
            try {
                return Cache::remember(
                    $cacheKey,
                    $this->cacheConfig['ttl'] ?? 86400,
                    fn() => $this->loadWords($category, $locale)
                );
            } catch (\Exception $e) {
                throw new UsernameGuardException(
                    "Failed to retrieve words from cache: {$e->getMessage()}",
                    'cache',
                    '',
                    ['category' => $category, 'locale' => $locale]
                );
            }
        }

        return $this->loadWords($category, $locale);
    }

    /**
     * Validate a username with optional validation type
     *
     * Main validation method supporting both pattern and word validation.
     * Results are cached if caching is enabled.
     *
     * @param string $text The username to validate
     * @param string|null $type Validation type: 'pattern', 'words', or null for both
     * @return bool True if validation passes, false otherwise
     */
    public function isValid(string $text, ?string $type = null): bool
    {
        // Reset error state
        $this->lastError = null;
        $this->lastException = null;

        // Cache validation result if enabled
        if ($this->cacheConfig['enabled']) {
            $cacheKey = "username_guard_validation_{$type}_" . md5($text);
            try {
                return Cache::remember(
                    $cacheKey,
                    $this->cacheConfig['ttl'] ?? 86400,
                    fn() => $this->validate($text, $type)
                );
            } catch (\Exception $e) {
                // Continue without cache
            }
        }

        return $this->validate($text, $type);
    }

    /**
     * Internal validation logic handler
     *
     * Performs the actual validation based on specified type.
     * Handles both pattern and word validation with error logging.
     *
     * @param string $text The username to validate
     * @param string|null $type Validation type
     * @return bool Validation result
     */
    protected function validate(string $text, ?string $type = null): bool
    {
        try {
            // Pattern validation
            if ($type === null || $type === 'pattern') {
                if (!$this->validatePattern($text)) {
                    $this->lastError = 'Pattern validation failed';
                    return false;
                }
            }

            // Words validation
            if ($type === null || $type === 'words') {
                if (!$this->validateWords($text)) {
                    $this->lastError = 'Word validation failed';
                    return false;
                }
            }

            return true;
        } catch (UsernameGuardException $e) {
            $this->lastException = $e;
            $this->lastError = $e->getMessage();
            return false;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return false;
        }
    }

    /**
     * Validate username against prohibited words
     *
     * Checks if the username contains any prohibited words from active categories.
     * Case-insensitive matching is used.
     *
     * @param string $text The username to check
     * @return bool True if no prohibited words found, false otherwise
     * @throws UsernameGuardException If a prohibited word is found
     */
    protected function validateWords(string $text): bool
    {
        try {
            // Get normalization settings from config
            $normalizationEnabled = $this->config['normalization']['enabled'] ?? true;
            $checkNormalizedOnly = $this->config['normalization']['check_normalized_only'] ?? false;

            // Normalize text if enabled
            $normalizedText = $normalizationEnabled ? $this->normalizeText($text) : $text;

            // Forbidden words validation
            foreach ($this->categories as $category => $enabled) {
                if (!$enabled) continue;

                $words = $this->getWordsByCategory($category);
                foreach ($words as $word) {
                    // Check based on configuration
                    $foundInOriginal = !$checkNormalizedOnly && stripos($text, $word) !== false;
                    $foundInNormalized = $normalizationEnabled && stripos($normalizedText, $word) !== false;

                    if ($foundInOriginal || $foundInNormalized) {
                        throw new UsernameGuardException(
                            "Username contains prohibited word from category '{$category}'",
                            'words',
                            $text,
                            ['category' => $category, 'word' => $word]
                        );
                    }
                }
            }

            return true;
        } catch (UsernameGuardException $e) {
            // Re-throw UsernameGuardException
            throw $e;
        } catch (\Exception $e) {
            throw new UsernameGuardException(
                "Word validation error: {$e->getMessage()}",
                'words',
                $text,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Validate username against active patterns
     *
     * Comprehensive pattern validation including:
     * - Length requirements
     * - Allowed characters
     * - Format rules (start/end requirements)
     * - Special character restrictions
     * Validates against all active pattern presets.
     *
     * @param string $text The username to validate
     * @return bool True if all pattern validations pass, false otherwise
     * @throws UsernameGuardException if a pattern validation fails
     */
    public function validatePattern(string $text): bool
    {
        try {
            foreach ($this->patterns['active'] as $patternName => $isActive) {
                if (!$isActive) continue;

                $preset = $this->patterns['presets'][$patternName] ?? null;
                if (!$preset) continue;

                // Length validation
                $minLength = $preset['min_length'] ?? 3;
                $maxLength = $preset['max_length'] ?? 20;

                if (strlen($text) < $minLength || strlen($text) > $maxLength) {
                    throw new UsernameGuardException(
                        "Username length must be between {$minLength} and {$maxLength} characters",
                        'pattern',
                        $text,
                        ['pattern' => $patternName, 'rule' => 'length']
                    );
                }

                // Character validation
                $allowedChars = $preset['allowed_chars'] ?? '[^a-zA-Z0-9._-]';
                if (!preg_match('/^[' . str_replace('[^', '', str_replace(']', '', $allowedChars)) . ']+$/', $text)) {
                    throw new UsernameGuardException(
                        "Username contains invalid characters",
                        'pattern',
                        $text,
                        ['pattern' => $patternName, 'rule' => 'allowed_chars']
                    );
                }

                // Format validation
                foreach ($preset['rules'] as $rule) {
                    $pattern = $this->patterns['rules'][$rule];
                    if (str_contains($rule, 'no_consecutive_')) {
                        if (preg_match($pattern, $text)) {
                            throw new UsernameGuardException(
                                "Username violates rule: {$rule}",
                                'pattern',
                                $text,
                                ['pattern' => $patternName, 'rule' => $rule]
                            );
                        }
                    } else {
                        if (!preg_match($pattern, $text)) {
                            throw new UsernameGuardException(
                                "Username violates rule: {$rule}",
                                'pattern',
                                $text,
                                ['pattern' => $patternName, 'rule' => $rule]
                            );
                        }
                    }
                }
            }
            return true;
        } catch (UsernameGuardException $e) {
            // Re-throw UsernameGuardException
            throw $e;
        } catch (\Exception $e) {
            throw new UsernameGuardException(
                "Pattern validation error: {$e->getMessage()}",
                'pattern',
                $text,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Load prohibited words from files with locale support
     *
     * Loads and merges word lists from multiple sources and locales.
     * Supports both package and published word lists.
     * Handles locale fallbacks and global words.
     *
     * @param string $category The word category to load
     * @param string $locale The locale to load words for
     * @return array Unique array of prohibited words
     * @throws UsernameGuardException if loading words fails
     */
    protected function loadWords(string $category, string $locale): array
    {
        try {
            $words = [];
            $paths = [
                __DIR__ . "/../resources/words",              // Package path
                base_path("resources/vendor/username-guard/words") // Published path
            ];

            // Determine locales based on configuration
            if ($this->config['preferred_locale_only']) {
                $locales = ['global', $locale];
            } elseif ($this->config['check_all_locales']) {
                $locales = $this->config['supported_locales'];
            } else {
                $locales = [$locale];
            }

            // Load words from each path and locale
            foreach ($paths as $basePath) {
                foreach ($locales as $currentLocale) {
                    $path = "{$basePath}/{$category}/{$currentLocale}.php";
                    if (file_exists($path)) {
                        $loadedWords = require $path;
                        if (is_array($loadedWords)) {
                            $words = array_merge($words, $loadedWords);
                            // Optimization: Limit the number of words to 1000
                            if (count($words) > 1000) {
                                $words = array_unique($words);
                            }
                        }
                    }
                }
            }

            return array_unique($words);
        } catch (\Exception $e) {
            throw new UsernameGuardException(
                "Failed to load words for category '{$category}': {$e->getMessage()}",
                'words_loading',
                '',
                ['category' => $category, 'locale' => $locale]
            );
        }
    }

    /**
     * Normalize text by replacing common character substitutions
     * 
     * Replaces common character substitutions used to bypass word filters,
     * such as '0' to 'o', '1' to 'i', etc. This helps detect attempts to use
     * prohibited words with character substitutions.
     *
     * @param string $text The text to normalize
     * @return string The normalized text
     */
    protected function normalizeText(string $text): string
    {
        $substitutions = [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '6' => 'g',
            '7' => 't',
            '8' => 'b',
            '@' => 'a',
            '$' => 's',
            '+' => 't',
            '!' => 'i',
            'z' => '2',
            '&' => 'a',
            '#' => 'h',
            '%' => 'p',
            '^' => 'c',
            '*' => 'x',
            '(' => 'c',
        ];

        return str_replace(array_keys($substitutions), array_values($substitutions), strtolower($text));
    }

    /**
     * Get the last error that occurred
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Get the last exception that occurred
     *
     * @return UsernameGuardException|null
     */
    public function getLastException(): ?UsernameGuardException
    {
        return $this->lastException;
    }
}
