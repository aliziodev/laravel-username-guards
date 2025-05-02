<?php

use Aliziodev\UsernameGuard\Services\UsernameService;
use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;

/**
 * Test for invalid configuration
 * 
 * This class tests the handling of invalid configurations
 * in UsernameService.
 */

test('missing default locale', function () {
    $config = [
        'default_locale' => '',
        'supported_locales' => ['global', 'en'],
    ];
    
    expect(fn() => new UsernameService($config))
        ->toThrow(UsernameGuardException::class, 'Default locale is not configured');
});

test('missing global locale', function () {
    $config = [
        'default_locale' => 'en',
        'supported_locales' => ['en', 'id'],
    ];
    
    expect(fn() => new UsernameService($config))
        ->toThrow(UsernameGuardException::class, "The 'global' locale is required in supported_locales");
});