<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Translation;

class GenerateTranslations extends Command
{
    protected $signature = 'translations:generate {count=100000}';
    protected $description = 'Generate a large number of translations for testing.';

    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating $count translations...");

        Translation::factory()->count($count)->create();

        $this->info('Done!');
    }
}
