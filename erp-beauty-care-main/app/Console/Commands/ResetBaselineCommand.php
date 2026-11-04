<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\BaselineSeeder;

class ResetBaselineCommand extends Command
{
    protected $signature = 'erp:seed-baseline';
    protected $description = 'Reset database to PT Arunika Beauty Nusantara baseline dataset';

    public function handle(): int
    {
        $this->info('Resetting baseline dataset...');
        $seeder = new BaselineSeeder();
        $seeder->run();
        $this->info('Baseline dataset successfully seeded!');
        return Command::SUCCESS;
    }
}
