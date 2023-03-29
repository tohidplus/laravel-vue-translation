<?php

namespace Tohidplus\Translation\Console\Commands;

use ElementaryFramework\FireFS\Watcher\FileSystemWatcher;
use Illuminate\Console\Command;
use function MongoDB\BSON\toJSON;
use Tohidplus\Translation\Facades\VueTranslation;
use Tohidplus\Translation\FileWatcher\Listener;

class Translation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VueTranslation:generate {--watch=0}';

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
        $this->generate();
        if ($this->option('watch')) {
            $this->watch();
        }
    }

    public function generate()
    {
        VueTranslation::compile();
    }

    public function watch()
    {
        /** @var FileSystemWatcher $watcher */
        $watcher = app('watcher');
        $watcher->setListener(new Listener())
            ->setRecursive(true)
            ->setPath('./lang')
            ->setWatchInterval(250)
            ->build();
        $watcher->start();
    }
}
