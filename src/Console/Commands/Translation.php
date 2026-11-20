<?php

namespace Tohidplus\Translation\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Tohidplus\Translation\LaravelVueTranslation;
use Tohidplus\Translation\Watcher\TranslationWatcher;

class Translation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VueTranslation:generate
                            {--watch=0 : Watch the lang directory and compile again when a file changes}
                            {--interval=250 : The time in milliseconds between two checks in watch mode}';

    /**
     * The console command name aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['vue-translation:generate'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile the Laravel translation files into a JSON file for JavaScript';

    public function handle(LaravelVueTranslation $translation, TranslationWatcher $watcher): int
    {
        $compiled = $this->generate($translation);

        if (! $this->shouldWatch()) {
            return $compiled ? self::SUCCESS : self::FAILURE;
        }

        $this->components->info("Watching [{$translation->langPath()}] for changes. Press Ctrl+C to stop.");

        $watcher->watch(
            $translation->langPath(),
            fn () => $this->generate($translation),
            (int) $this->option('interval')
        );

        return self::SUCCESS;
    }

    /**
     * Compile the translations and report the result.
     */
    public function generate(LaravelVueTranslation $translation): bool
    {
        $count = 0;

        try {
            $translation->compile(function (string $path) use (&$count) {
                $count++;

                if ($this->output->isVerbose()) {
                    $this->components->twoColumnDetail($path, '<fg=green;options=bold>DONE</>');
                }
            });
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return false;
        }

        $this->components->info(sprintf(
            'Compiled %d translation %s into [%s].',
            $count,
            $count === 1 ? 'file' : 'files',
            $translation->outputFile()
        ));

        return true;
    }

    /**
     * Accept "--watch", "--watch=1" and "--watch=true". Ignore "--watch=0" and "--watch=false".
     */
    protected function shouldWatch(): bool
    {
        if (! $this->input->hasParameterOption('--watch', true)) {
            return false;
        }

        $value = $this->option('watch');

        return $value === null || ! in_array(strtolower((string) $value), ['0', 'false', 'no', 'off', ''], true);
    }
}
