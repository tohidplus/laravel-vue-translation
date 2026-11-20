<?php

namespace Tohidplus\Translation\Tests\Unit;

use Tohidplus\Translation\Tests\TestCase;
use Tohidplus\Translation\Watcher\TranslationWatcher;

class TranslationWatcherTest extends TestCase
{
    public function test_the_fingerprint_changes_when_a_translation_file_changes(): void
    {
        $watcher = new TranslationWatcher($this->files);
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $first = $watcher->fingerprint($this->langPath());

        $this->assertSame($first, $watcher->fingerprint($this->langPath()));

        $this->writeLangFile('en/messages.php', ['hello' => 'Hallo']);
        $changed = $watcher->fingerprint($this->langPath());
        $this->assertNotSame($first, $changed);

        $this->writeLangFile('fr.json', ['Hello' => 'Bonjour']);
        $added = $watcher->fingerprint($this->langPath());
        $this->assertNotSame($changed, $added);

        $this->files->delete($this->langPath('fr.json'));
        $this->assertSame($changed, $watcher->fingerprint($this->langPath()));
    }

    public function test_the_fingerprint_ignores_files_that_are_not_translation_files(): void
    {
        $watcher = new TranslationWatcher($this->files);
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $before = $watcher->fingerprint($this->langPath());

        $this->writeLangFile('en/messages.php~', 'editor backup');

        $this->assertSame($before, $watcher->fingerprint($this->langPath()));
    }

    public function test_the_fingerprint_of_a_missing_directory_is_empty(): void
    {
        $this->assertSame('', (new TranslationWatcher($this->files))->fingerprint($this->langPath('missing')));
    }

    public function test_it_calls_back_when_a_file_changes(): void
    {
        $watcher = new TranslationWatcher($this->files);
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $checks = 0;
        $changes = 0;

        $watcher->watch(
            $this->langPath(),
            function () use (&$changes) {
                $changes++;
            },
            10,
            function () use (&$checks) {
                $checks++;

                if ($checks === 2) {
                    $this->writeLangFile('en/messages.php', ['hello' => 'Hello again']);
                }

                return $checks <= 4;
            }
        );

        $this->assertSame(5, $checks);
        $this->assertSame(1, $changes);
    }
}
