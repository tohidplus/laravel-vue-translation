<?php

namespace Tohidplus\Translation\Tests;

use Illuminate\Filesystem\Filesystem;
use Orchestra\Testbench\TestCase as Orchestra;
use Tohidplus\Translation\Contract\TranslationFileHelper;
use Tohidplus\Translation\LaravelTranslationFileHelper;
use Tohidplus\Translation\TranslationServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * A temporary directory for the lang files and the compiled output of one test.
     */
    protected string $workspace;

    protected Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->workspace = sys_get_temp_dir().DIRECTORY_SEPARATOR.'vue-translation-'.bin2hex(random_bytes(8));
        $this->files->ensureDirectoryExists($this->langPath());

        $this->app->bind(TranslationFileHelper::class, fn ($app) => new LaravelTranslationFileHelper(
            $app->make(Filesystem::class),
            $this->langPath(),
            $this->outputPath(),
        ));
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->workspace);

        parent::tearDown();
    }

    protected function getPackageProviders($app): array
    {
        return [TranslationServiceProvider::class];
    }

    protected function langPath(string $path = ''): string
    {
        return $this->workspace.DIRECTORY_SEPARATOR.'lang'.($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    protected function outputPath(): string
    {
        return $this->workspace.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'VueTranslation';
    }

    protected function outputFile(): string
    {
        return $this->outputPath().DIRECTORY_SEPARATOR.'translations.json';
    }

    /**
     * Write a lang file. An array becomes a PHP file (or JSON for .json files).
     */
    protected function writeLangFile(string $path, array|string $contents): void
    {
        if (is_array($contents)) {
            $contents = str_ends_with($path, '.json')
                ? json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                : '<?php return '.var_export($contents, true).';';
        }

        $this->files->ensureDirectoryExists(dirname($this->langPath($path)));
        $this->files->put($this->langPath($path), $contents);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function compiled(): array
    {
        $this->assertFileExists($this->outputFile());

        return json_decode($this->files->get($this->outputFile()), true, 512, JSON_THROW_ON_ERROR);
    }
}
