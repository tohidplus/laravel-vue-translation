<?php

namespace Tohidplus\Translation\Watcher;

use Illuminate\Filesystem\Filesystem;
use Tohidplus\Translation\LaravelTranslationFileHelper;

/**
 * Polls a directory and calls back when a file is added, changed, or removed.
 */
class TranslationWatcher
{
    public function __construct(protected Filesystem $files)
    {
    }

    /**
     * Watch the path until $shouldContinue returns false (or forever when it is null).
     *
     * @param  callable(): void  $onChange
     * @param  (callable(): bool)|null  $shouldContinue
     */
    public function watch(string $path, callable $onChange, int $intervalMs = 250, ?callable $shouldContinue = null): void
    {
        $intervalMs = max(10, $intervalMs);
        $fingerprint = $this->fingerprint($path);

        while ($shouldContinue === null || $shouldContinue()) {
            usleep($intervalMs * 1000);

            $current = $this->fingerprint($path);

            if ($current !== $fingerprint) {
                $fingerprint = $current;
                $onChange();
            }
        }
    }

    /**
     * Get a hash of the names and contents of all translation files in the path.
     */
    public function fingerprint(string $path): string
    {
        clearstatcache();

        if (! $this->files->isDirectory($path)) {
            return '';
        }

        $context = hash_init('xxh128');

        foreach ($this->files->allFiles($path) as $file) {
            if (! in_array(strtolower($file->getExtension()), LaravelTranslationFileHelper::EXTENSIONS, true)) {
                continue;
            }

            $contents = @file_get_contents($file->getPathname());

            hash_update($context, $file->getRelativePathname()."\0".($contents === false ? '' : $contents)."\0");
        }

        return hash_final($context);
    }
}
