<?php

namespace Aliziodev\UsernameGuard\Console;

use Illuminate\Console\Command;

/**
 * Clear cache command for Laravel Username Guard package.
 *
 * This command handles clearing all caches related to the package:
 * - Configuration cache
 * - Application cache
 * - Package discovery cache
 *
 * @package Aliziodev\UsernameGuard\Console
 */
class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'username-guard:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all Laravel Username Guard caches';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Clearing Laravel Username Guard caches...');

      
        $this->info('Clearing configuration cache...');
        $this->callSilently('config:clear');
        $this->line('  Configuration cache cleared.');

        $this->info('Clearing application cache...');
        $this->callSilently('cache:clear');
        $this->line('  Application cache cleared.');

        $this->info('Clearing package discovery cache...');
        $this->callSilently('package:discover');
        $this->line('  Package discovery cache cleared.');

        $this->info('All caches have been cleared successfully!');
    }
}
