<?php

namespace Aliziodev\UsernameGuard\Console;

use Illuminate\Console\Command;

/**
 * Installation command for Laravel Username Guard package.
 *
 * This command handles the initial setup of the package by:
 * - Publishing configuration file
 * - Publishing forbidden words resources
 * - Setting up basic configuration
 *
 * @package Aliziodev\UsernameGuard\Console
 */
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'username-guard:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Laravel Username Guard package';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing Laravel Username Guard...');

        // Publish configuration
        $this->publishConfiguration();

        // Publish word resources
        $this->publishWordResources();

        $this->info('Installation completed!');
        $this->newLine();
        $this->info('Please review the configuration file at:');
        $this->line('  config/username-guard.php');
        $this->newLine();
        $this->info('Forbidden words can be found at:');
        $this->line('  resources/vendor/username-guard/words/');
    }

    /**
     * Publish the configuration file.
     */
    protected function publishConfiguration(): void
    {
        $this->info('Publishing configuration...');

        $this->callSilently('vendor:publish', [
            '--tag' => 'username-guard-config',
            '--force' => true,
        ]);

        $this->line('  Configuration file published successfully.');
    }

    /**
     * Publish the forbidden words resources.
     */
    protected function publishWordResources(): void
    {
        $this->info('Publishing word resources...');

        $this->callSilently('vendor:publish', [
            '--tag' => 'username-guard-words',
            '--force' => true,
        ]);

        $this->line('  Word resources published successfully.');
    }
}
