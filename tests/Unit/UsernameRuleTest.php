<?php

use Aliziodev\UsernameGuard\Rules\UsernameRule;
use Aliziodev\UsernameGuard\Services\UsernameService;
use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;
use Mockery;

/**
 * Test for UsernameRule
 * 
 * This class tests the functionality of UsernameRule including
 * validation and error handling.
 */

beforeEach(function () {
    $this->serviceMock = null;
});

afterEach(function () {
    Mockery::close();
});

test('valid username', function () {
    $serviceMock = Mockery::mock(UsernameService::class);
    $serviceMock->shouldReceive('isValid')
        ->with('validusername', 'pattern')
        ->andReturn(true);
    $serviceMock->shouldReceive('isValid')
        ->with('validusername', 'words')
        ->andReturn(true);
    $serviceMock->shouldReceive('getLastException')
        ->andReturn(null);
        
    app()->instance(UsernameService::class, $serviceMock);
    
    $rule = new UsernameRule();
    $failCalled = false;
    
    $rule->validate('username', 'validusername', function() use (&$failCalled) {
        $failCalled = true;
    });
    
    expect($failCalled)->toBeFalse();
});

test('invalid pattern', function () {
    $exception = new UsernameGuardException(
        'Username length must be between 3 and 20 characters',
        'pattern',
        'ab',
        ['rule' => 'length']
    );
    
    $serviceMock = Mockery::mock(UsernameService::class);
    $serviceMock->shouldReceive('isValid')
        ->with('ab', 'pattern')
        ->andReturn(false);
    $serviceMock->shouldReceive('getLastException')
        ->andReturn($exception);
        
    app()->instance(UsernameService::class, $serviceMock);
    
    $rule = new UsernameRule();
    $failMessage = null;
    
    $rule->validate('username', 'ab', function($message) use (&$failMessage) {
        $failMessage = $message;
    });
    
    expect($failMessage)->not->toBeNull();
    expect($failMessage)->toContain('length is invalid');
});

test('prohibited word', function () {
    $exception = new UsernameGuardException(
        'Username contains prohibited word from category \'profanity\'',
        'words',
        'badword',
        ['category' => 'profanity', 'word' => 'bad']
    );
    
    $serviceMock = Mockery::mock(UsernameService::class);
    $serviceMock->shouldReceive('isValid')
        ->with('badword', 'pattern')
        ->andReturn(true);
    $serviceMock->shouldReceive('isValid')
        ->with('badword', 'words')
        ->andReturn(false);
    $serviceMock->shouldReceive('getLastException')
        ->andReturn($exception);
        
    app()->instance(UsernameService::class, $serviceMock);
    
    $rule = new UsernameRule();
    $failMessage = null;
    
    $rule->validate('username', 'badword', function($message) use (&$failMessage) {
        $failMessage = $message;
    });
    
    expect($failMessage)->not->toBeNull();
    expect($failMessage)->toContain('prohibited word');
});

test('custom error message', function () {
    $serviceMock = Mockery::mock(UsernameService::class);
    $serviceMock->shouldReceive('isValid')
        ->with('ab', 'pattern')
        ->andReturn(false);
        
    app()->instance(UsernameService::class, $serviceMock);
    
    $customMessage = 'Invalid username';
    $rule = new UsernameRule($customMessage);
    $failMessage = null;
    
    $rule->validate('username', 'ab', function($message) use (&$failMessage) {
        $failMessage = $message;
    });
    
    expect($failMessage)->toBe($customMessage);
});

test('set message method', function () {
    $serviceMock = Mockery::mock(UsernameService::class);
    $serviceMock->shouldReceive('isValid')
        ->with('ab', 'pattern')
        ->andReturn(false);
        
    app()->instance(UsernameService::class, $serviceMock);
    
    $customMessage = 'Invalid username';
    $rule = new UsernameRule();
    $rule->setMessage($customMessage);
    $failMessage = null;
    
    $rule->validate('username', 'ab', function($message) use (&$failMessage) {
        $failMessage = $message;
    });
    
    expect($failMessage)->toBe($customMessage);
});