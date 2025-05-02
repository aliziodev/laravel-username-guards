<?php

use Aliziodev\UsernameGuard\Services\UsernameService;
use Illuminate\Support\Facades\Config;

test('service can be instantiated through container', function () {
    // Ensure service can be resolved from container
    $service = app(UsernameService::class);
    
    expect($service)->toBeInstanceOf(UsernameService::class);
});

test('service can validate username with special characters', function () {
    $service = app(UsernameService::class);
    
    // Username with allowed characters
    expect($service->isValid('user_name'))->toBeTrue();
    expect($service->isValid('user-name'))->toBeTrue();
    
    // Username with disallowed characters
    expect($service->isValid('user@name'))->toBeFalse();
    expect($service->isValid('user#name'))->toBeFalse();
});

test('service stores last error when validation fails', function () {
    $service = app(UsernameService::class);
    
    // Validate invalid username
    Config::set('username-guard.patterns.rules.min_length', 5); // Make sure min_length is large enough
    $service->isValid('a'); // Too short
    
    // Ensure error message is stored
    expect($service->getLastError())->not->toBeEmpty();
    
    // Validate valid username
    $service->isValid('valid_username');
    
    // Error message should be reset
    expect($service->getLastError())->toBeEmpty();
});