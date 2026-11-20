<?php

namespace Tohidplus\Translation\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Tohidplus\Translation\Tests\TestCase;
use Tohidplus\Translation\Watcher\TranslationWatcher;

class GenerateCommandTest extends TestCase
{
    public function test_it_compiles_the_translations(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $this->writeLangFile('en.json', ['Hi' => 'Hi']);

        $this->artisan('VueTranslation:generate')
            ->expectsOutputToContain('Compiled 2 translation files')
            ->assertSuccessful();

        $this->assertSame(['en' => ['Hi' => 'Hi', 'messages' => ['hello' => 'Hello']]], $this->compiled());
    }

    public function test_it_has_a_lower_case_alias(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);

        $this->artisan('vue-translation:generate')
            ->expectsOutputToContain('Compiled 1 translation file ')
            ->assertSuccessful();

        $this->assertFileExists($this->outputFile());
    }

    public function test_it_lists_the_files_in_verbose_mode(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);

        $this->artisan('VueTranslation:generate', ['-v' => true])
            ->expectsOutputToContain('en/messages.php')
            ->assertSuccessful();
    }

    public function test_it_fails_with_a_clear_message_when_the_lang_directory_does_not_exist(): void
    {
        $this->files->deleteDirectory($this->langPath());

        $this->artisan('VueTranslation:generate')
            ->expectsOutputToContain('does not exist')
            ->assertFailed();

        $this->assertFileDoesNotExist($this->outputFile());
    }

    public function test_it_does_not_watch_without_the_watch_option(): void
    {
        $watcher = $this->fakeWatcher();

        $this->artisan('VueTranslation:generate')->assertSuccessful();
        $this->artisan('VueTranslation:generate', ['--watch' => '0'])->assertSuccessful();
        $this->artisan('VueTranslation:generate', ['--watch' => 'false'])->assertSuccessful();

        $this->assertSame(0, $watcher->calls);
    }

    public function test_it_watches_the_lang_directory_and_compiles_again_on_change(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $watcher = $this->fakeWatcher(function () {
            $this->writeLangFile('en/messages.php', ['hello' => 'Hello again']);
        });

        $this->artisan('VueTranslation:generate', ['--watch' => '1', '--interval' => '100'])
            ->expectsOutputToContain('Watching')
            ->assertSuccessful();

        $this->assertSame(1, $watcher->calls);
        $this->assertSame($this->langPath(), $watcher->path);
        $this->assertSame(100, $watcher->interval);
        $this->assertSame(['en' => ['messages' => ['hello' => 'Hello again']]], $this->compiled());
    }

    public function test_the_watch_option_works_without_a_value(): void
    {
        $watcher = $this->fakeWatcher();

        $this->artisan('VueTranslation:generate', ['--watch' => null])->assertSuccessful();

        $this->assertSame(1, $watcher->calls);
    }

    public function test_it_keeps_watching_when_a_file_has_an_error(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $this->fakeWatcher(function () {
            $this->writeLangFile('en.json', '{"broken": ');
        });

        $this->artisan('VueTranslation:generate', ['--watch' => true])
            ->expectsOutputToContain('contains invalid JSON')
            ->assertSuccessful();
    }

    /**
     * Replace the watcher with one that makes one change and then stops.
     */
    protected function fakeWatcher(?callable $change = null): TranslationWatcher
    {
        $watcher = new class(new Filesystem, $change) extends TranslationWatcher
        {
            public int $calls = 0;

            public ?string $path = null;

            public ?int $interval = null;

            public function __construct(Filesystem $files, private $change)
            {
                parent::__construct($files);
            }

            public function watch(string $path, callable $onChange, int $intervalMs = 250, ?callable $shouldContinue = null): void
            {
                $this->calls++;
                $this->path = $path;
                $this->interval = $intervalMs;

                if ($this->change !== null) {
                    ($this->change)();
                    $onChange();
                }
            }
        };

        $this->app->instance(TranslationWatcher::class, $watcher);

        return $watcher;
    }
}
