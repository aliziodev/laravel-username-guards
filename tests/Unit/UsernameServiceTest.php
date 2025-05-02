<?php

use Aliziodev\UsernameGuard\Services\UsernameService;
use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;

/**
 * Test for UsernameService
 * 
 * This class tests the functionality of UsernameService including
 * username validation, error handling, and other features.
 */

beforeEach(function () {
    // Basic configuration for testing
    $config = [
        'default_locale' => 'en',
        'supported_locales' => ['global', 'en', 'id'],
        'preferred_locale_only' => true,
        'check_all_locales' => false,
        'categories' => [
            'profanity' => true,
            'adult' => true,
            'spam' => false
        ],
        'custom_categories' => [],
        'default_categories' => [
            'profanity' => true,
            'adult' => true,
            'spam' => true
        ],
        'cache' => [
            'enabled' => false
        ],
        'patterns' => [
            'sets' => [],
            'rules' => [
                'no_consecutive_special_chars' => '/([._-])\\1+/',
                'no_special_chars_at_start' => '/^[a-zA-Z0-9]/',
                'no_special_chars_at_end' => '/[a-zA-Z0-9]$/',
            ],
            'presets' => [
                'username' => [
                    'min_length' => 3,
                    'max_length' => 20,
                    'allowed_chars' => '[a-zA-Z0-9._-]',
                    'rules' => [
                        'no_consecutive_special_chars',
                        'no_special_chars_at_start',
                        'no_special_chars_at_end'
                    ]
                ]
            ],
            'active' => [
                'username' => true
            ]
        ]
    ];
    
    $this->service = new UsernameService($config);
});

test('valid username', function () {
    expect($this->service->isValid('validusername'))->toBeTrue();
    expect($this->service->getLastError())->toBeNull();
    expect($this->service->getLastException())->toBeNull();
});

test('username too short', function () {
    expect($this->service->isValid('ab', 'pattern'))->toBeFalse();
    expect($this->service->getLastError())->not->toBeNull();
    expect($this->service->getLastException())->toBeInstanceOf(UsernameGuardException::class);
    
    $exception = $this->service->getLastException();
    expect($exception->getValidationType())->toBe('pattern');
    expect($exception->getUsername())->toBe('ab');
    expect($exception->getContext())->toHaveKey('rule');
    expect($exception->getContext()['rule'])->toBe('length');
});

test('username invalid characters', function () {
    expect($this->service->isValid('invalid@username', 'pattern'))->toBeFalse();
    expect($this->service->getLastError())->not->toBeNull();
    expect($this->service->getLastException())->toBeInstanceOf(UsernameGuardException::class);
    
    $exception = $this->service->getLastException();
    expect($exception->getValidationType())->toBe('pattern');
    expect($exception->getUsername())->toBe('invalid@username');
    expect($exception->getContext())->toHaveKey('rule');
    expect($exception->getContext()['rule'])->toBe('allowed_chars');
});

test('username special char at start', function () {
    expect($this->service->isValid('_username', 'pattern'))->toBeFalse();
    expect($this->service->getLastError())->not->toBeNull();
    expect($this->service->getLastException())->toBeInstanceOf(UsernameGuardException::class);
    
    $exception = $this->service->getLastException();
    expect($exception->getValidationType())->toBe('pattern');
    expect($exception->getUsername())->toBe('_username');
});

test('username consecutive special chars', function () {
    expect($this->service->isValid('user__name', 'pattern'))->toBeFalse();
    expect($this->service->getLastError())->not->toBeNull();
    expect($this->service->getLastException())->toBeInstanceOf(UsernameGuardException::class);
    
    $exception = $this->service->getLastException();
    expect($exception->getValidationType())->toBe('pattern');
    expect($exception->getUsername())->toBe('user__name');
});

test('error state reset', function () {
    // First, create an error
    expect($this->service->isValid('_invalid', 'pattern'))->toBeFalse();
    expect($this->service->getLastError())->not->toBeNull();
    expect($this->service->getLastException())->not->toBeNull();
    
    // Then successful validation
    expect($this->service->isValid('validuser'))->toBeTrue();
    expect($this->service->getLastError())->toBeNull();
    expect($this->service->getLastException())->toBeNull();
});

test('validate configuration', function () {
    // Test with valid configuration already done in setUp()
    expect(true)->toBeTrue(); // Valid configuration doesn't throw exception
});