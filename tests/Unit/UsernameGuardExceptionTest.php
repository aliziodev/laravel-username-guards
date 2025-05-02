<?php

use Aliziodev\UsernameGuard\Exceptions\UsernameGuardException;

/**
 * Test for UsernameGuardException
 * 
 * This class tests the functionality of UsernameGuardException
 * including properties and getter methods.
 */

test('exception properties', function () {
    $message = 'Test error message';
    $validationType = 'pattern';
    $username = 'testuser';
    $context = ['rule' => 'length'];
    
    $exception = new UsernameGuardException($message, $validationType, $username, $context);
    
    expect($exception->getMessage())->toBe($message);
    expect($exception->getValidationType())->toBe($validationType);
    expect($exception->getUsername())->toBe($username);
    expect($exception->getContext())->toBe($context);
});

test('exception with empty context', function () {
    $exception = new UsernameGuardException('Error', 'words', 'badword');
    
    expect($exception->getMessage())->toBe('Error');
    expect($exception->getValidationType())->toBe('words');
    expect($exception->getUsername())->toBe('badword');
    expect($exception->getContext())->toBe([]);
});