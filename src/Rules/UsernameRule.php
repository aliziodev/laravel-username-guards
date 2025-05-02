<?php

namespace Aliziodev\UsernameGuard\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Aliziodev\UsernameGuard\Services\UsernameService;
use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;

/**
 * Username Validation Rule
 *
 * This rule validates usernames using the UsernameGuard service.
 * It checks for prohibited words, patterns, and other constraints
 * defined in the username-guard configuration.
 *
 * Features:
 * - Validates username format and length
 * - Checks for prohibited words across multiple languages
 * - Supports custom validation patterns
 * - Allows custom error messages
 *
 * @package Aliziodev\UsernameGuard
 * @version 1.0.0
 * @author Alizio Dev <aliziodev@gmail.com>
 */
class UsernameRule implements ValidationRule
{
    /**
     * The username validation service instance.
     * This service handles all validation logic including pattern matching
     * and prohibited words checking.
     *
     * @var UsernameService
     */
    protected UsernameService $service;

    /**
     * Custom validation error message.
     * When set, this message overrides both pattern and prohibited words
     * error messages.
     *
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * Create a new username validation rule instance.
     *
     * @param string|null $message Optional custom error message that overrides default messages
     */
    public function __construct(?string $message = null)
    {
        $this->service = app(UsernameService::class);
        $this->message = $message;
    }

    /**
     * Set a custom validation error message.
     * This message will override both pattern and prohibited words error messages.
     *
     * @param string $message The custom error message to use
     * @return self Returns the rule instance for method chaining
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Validate the username value.
     *
     * The validation process follows these steps:
     * 1. Checks if the value is a string
     * 2. Validates the username pattern (length, allowed characters)
     * 3. Checks for prohibited words if pattern validation passes
     *
     * Each validation step has its own specific error message that can be
     * customized through translation files.
     *
     * @param string $attribute The name of the attribute being validated
     * @param mixed $value The value to validate
     * @param Closure $fail The callback to execute on validation failure
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        // Pattern validation first
        if (!$this->service->isValid($value, 'pattern')) {
            $message = $this->message ?? $this->getErrorMessage($attribute, 'pattern');
            $fail($message);
            return;
        }

        // Words validation second
        if (!$this->service->isValid($value, 'words')) {
            $message = $this->message ?? $this->getErrorMessage($attribute, 'words');
            $fail($message);
        }
    }

    /**
     * Get error message based on validation type
     *
     * This method will try to get more specific error information
     * from the service if available, or use default messages if not.
     *
     * @param string $attribute The attribute being validated
     * @param string $type Validation type (pattern or words)
     * @return string The formatted error message
     */
    protected function getErrorMessage(string $attribute, string $type): string
    {
        // Try to get the specific error message from the service
        $lastException = $this->service->getLastException();

        if ($lastException instanceof UsernameGuardException) {
            // Use the exception message if available
            $validationType = $lastException->getValidationType();
            $context = $lastException->getContext();

            // Spesific message based on validation type and context
            if ($validationType === 'pattern') {
                $rule = $context['rule'] ?? '';
                if ($rule === 'length') {
                    return "The {$attribute} length is invalid. Please check the length requirements.";
                } elseif ($rule === 'allowed_chars') {
                    return "The {$attribute} contains invalid characters.";
                } else {
                    return "The {$attribute} format is invalid. {$lastException->getMessage()}";
                }
            } elseif ($validationType === 'words') {
                $category = $context['category'] ?? '';
                $word = $context['word'] ?? '';
                if (!empty($category) && !empty($word)) {
                    return "The {$attribute} contains prohibited word from category '{$category}'.";
                } else {
                    return "The {$attribute} contains prohibited words.";
                }
            }
        }

        // Fallback message if no specific message is found
        if ($type === 'pattern') {
            return "The {$attribute} format is invalid. Please check the allowed characters and length requirements.";
        } else {
            return "The {$attribute} contains prohibited words.";
        }
    }

    /**
     * Get the default validation error message for prohibited words.
     *
     * This message is used when a username contains prohibited words and no
     * custom message has been set. The message can be customized through
     *
     * @param string $attribute The name of the attribute being validated
     * @return string The formatted error message
     * @deprecated Use getErrorMessage() as a replacement
     */
    protected function getDefaultMessage(string $attribute): string
    {
        return "The {$attribute} contains prohibited words.";
    }

    /**
     * Get the validation error message for invalid patterns.
     *
     * This message is used when a username doesn't match the required pattern
     * (length, allowed characters, etc.) and no custom message has been set.
     *
     * @param string $attribute The name of the attribute being validated
     * @return string The formatted error message
     * @deprecated Use getErrorMessage() as a replacement
     */
    protected function getPatternErrorMessage(string $attribute): string
    {
        return "The {$attribute} format is invalid. Please check the allowed characters and length requirements.";
    }
}
