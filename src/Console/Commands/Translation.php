<?php

namespace Tohidplus\Translation\Console\Commands;

use Illuminate\Console\Command;
use Tohidplus\Translation\Facades\VueTranslation;

class Translation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VueTranslation:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compiles the translation files into a json file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        VueTranslation::compile();
        $this->info('The translations.json file updated successfully.');
    }
}
