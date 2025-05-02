<?php

namespace Aliziodev\UsernameGuard\Exceptions;

use Exception;

/**
 * Custom Exception for Username Guard Package
 * 
 * Used to handle specific errors that occur during username validation
 * with additional information about validation type and the validated username.
 *
 * @package Aliziodev\UsernameGuard\Exceptions
 * @version 1.0.0
 * @author Alizio Dev
 */
class UsernameGuardException extends Exception
{
    /**
     * Validation type that caused the exception
     * Examples: 'pattern', 'words', or 'general'
     *
     * @var string
     */
    protected string $validationType;
    
    /**
     * Username that caused the exception
     *
     * @var string
     */
    protected string $username;
    
    /**
     * Additional information about the error
     *
     * @var array
     */
    protected array $context = [];

    /**
     * Create a new exception instance
     *
     * @param string $message Error message
     * @param string $validationType Validation type that failed
     * @param string $username Username being validated
     * @param array $context Additional information about the error
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message, 
        string $validationType, 
        string $username, 
        array $context = [],
        int $code = 0, 
        ?\Throwable $previous = null
    ) {
        $this->validationType = $validationType;
        $this->username = $username;
        $this->context = $context;
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the validation type that caused the exception
     *
     * @return string
     */
    public function getValidationType(): string
    {
        return $this->validationType;
    }

    /**
     * Get the username that caused the exception
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    
    /**
     * Get additional context information
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}