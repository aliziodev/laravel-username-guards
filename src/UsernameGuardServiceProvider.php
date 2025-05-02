<?php

namespace Aliziodev\UsernameGuard;

use Aliziodev\UsernameGuard\Services\UsernameService;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for Laravel Username Guard Package.
 *
 * This package provides robust username validation with features:
 * - Username pattern validation
 * - Forbidden words checking
 * - Multi-language support
 * - Flexible configuration
 *
 * @package Aliziodev\UsernameGuard
 * @version 1.0.0
 * @author Alizio Dev <aliziodev@gmail.com>
 */
class UsernameGuardServiceProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array<class-string>
     */
    protected $commands = [
        Console\InstallCommand::class,
        Console\ClearCommand::class,
    ];

    /**
     * Register package services.
     *
     * This method registers the configuration and singleton service
     * for username validation.
     */
    public function register(): void
    {
        // Merge default configuration with application configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/username-guard.php',
            'username-guard'
        );

        // Register UsernameService as singleton
        $this->app->singleton(UsernameService::class, function ($app) {
            $config = $app['config']->get('username-guard');
            return new UsernameService($config);
        });

        // Register the commands
        $this->commands($this->commands);
    }

    /**
     * Bootstrap package services.
     *
     * This method publishes configuration file and forbidden words resources
     * when the application is running in console mode.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration file
            $this->publishes([
                __DIR__ . '/../config/username-guard.php' => config_path('username-guard.php'),
            ], 'username-guard-config');

            // Publish forbidden words files
            $this->publishes([
                __DIR__ . '/../resources/words' => resource_path('vendor/username-guard/words'),
            ], 'username-guard-words');
        }
    }
}
