<?php

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Aliziodev\UsernameGuard\UsernameGuardServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup package service provider
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            UsernameGuardServiceProvider::class,
        ];
    }
    
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('username-guard.default_locale', 'en');
        $app['config']->set('username-guard.supported_locales', ['global', 'en', 'id']);
        $app['config']->set('username-guard.preferred_locale_only', true);
        $app['config']->set('username-guard.check_all_locales', false);
        $app['config']->set('username-guard.categories', [
            'profanity' => true,
            'adult' => true,
            'spam' => false
        ]);
        $app['config']->set('username-guard.cache.enabled', false);
    }
}