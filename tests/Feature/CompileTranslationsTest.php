<?php

namespace Tohidplus\Translation\Tests\Feature;

use Tohidplus\Translation\Exceptions\TranslationException;
use Tohidplus\Translation\Facades\VueTranslation;
use Tohidplus\Translation\Tests\TestCase;

class CompileTranslationsTest extends TestCase
{
    public function test_it_compiles_php_files_under_the_locale_and_file_name(): void
    {
        $this->writeLangFile('en/messages.php', ['foo' => ['bar' => 'Some message']]);
        $this->writeLangFile('fr/messages.php', ['foo' => ['bar' => 'Un message']]);

        VueTranslation::compile();

        $this->assertSame([
            'en' => ['messages' => ['foo' => ['bar' => 'Some message']]],
            'fr' => ['messages' => ['foo' => ['bar' => 'Un message']]],
        ], $this->compiled());
    }

    public function test_it_compiles_json_files_under_the_locale_and_keeps_keys_with_dots(): void
    {
        $this->writeLangFile('fr.json', ['I love programming.' => "J'adore programmer."]);

        VueTranslation::compile();

        $this->assertSame(['fr' => ['I love programming.' => "J'adore programmer."]], $this->compiled());
    }

    public function test_it_merges_json_and_php_files_of_the_same_locale(): void
    {
        $this->writeLangFile('en.json', ['Welcome!' => 'Welcome!']);
        $this->writeLangFile('en/auth.php', ['failed' => 'These credentials do not match our records.']);
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello :name']);

        VueTranslation::compile();

        $this->assertSame([
            'en' => [
                'Welcome!' => 'Welcome!',
                'auth' => ['failed' => 'These credentials do not match our records.'],
                'messages' => ['hello' => 'Hello :name'],
            ],
        ], $this->compiled());
    }

    public function test_it_compiles_files_in_sub_directories(): void
    {
        $this->writeLangFile('en/admin/users.php', ['title' => 'Users']);

        VueTranslation::compile();

        $this->assertSame(['en' => ['admin' => ['users' => ['title' => 'Users']]]], $this->compiled());
    }

    public function test_it_ignores_files_that_are_not_php_or_json(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $this->writeLangFile('en/README.md', '# Notes');
        $this->writeLangFile('en/messages.php.bak', 'not a translation file');
        $this->writeLangFile('.DS_Store', "\0\0binary");
        $this->writeLangFile('en/.gitkeep', '');

        VueTranslation::compile();

        $this->assertSame(['en' => ['messages' => ['hello' => 'Hello']]], $this->compiled());
    }

    public function test_it_keeps_numeric_keys(): void
    {
        $this->writeLangFile('en.json', ['404' => 'Not found', '500' => 'Server error']);
        $this->writeLangFile('en/errors.php', [404 => 'Page not found']);

        VueTranslation::compile();

        $this->assertSame([
            'en' => [
                '404' => 'Not found',
                '500' => 'Server error',
                'errors' => ['404' => 'Page not found'],
            ],
        ], $this->compiled());
    }

    public function test_it_writes_unicode_characters_without_escaping(): void
    {
        $this->writeLangFile('fa/messages.php', ['hello' => 'سلام']);

        VueTranslation::compile();

        $this->assertStringContainsString('سلام', $this->files->get($this->outputFile()));
        $this->assertSame(['fa' => ['messages' => ['hello' => 'سلام']]], $this->compiled());
    }

    public function test_it_writes_an_empty_object_when_there_are_no_translation_files(): void
    {
        VueTranslation::compile();

        $this->assertSame('{}', $this->files->get($this->outputFile()));
    }

    public function test_it_returns_the_compiled_translations_and_reports_each_file(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        $this->writeLangFile('en.json', ['Hi' => 'Hi']);
        $files = [];

        $translations = VueTranslation::compile(function (string $path) use (&$files) {
            $files[] = $path;
        });

        $this->assertSame(['en' => ['Hi' => 'Hi', 'messages' => ['hello' => 'Hello']]], $translations);
        $this->assertSame(['en.json', 'en/messages.php'], $files);
    }

    public function test_it_creates_the_output_directory(): void
    {
        $this->assertDirectoryDoesNotExist($this->outputPath());

        VueTranslation::compile();

        $this->assertDirectoryExists($this->outputPath());
    }

    public function test_it_reads_the_files_again_on_each_compile(): void
    {
        $this->writeLangFile('en/messages.php', ['hello' => 'Hello']);
        VueTranslation::compile();

        $this->writeLangFile('en/messages.php', ['hello' => 'Hello again']);
        VueTranslation::compile();

        $this->assertSame(['en' => ['messages' => ['hello' => 'Hello again']]], $this->compiled());
    }

    public function test_it_fails_when_the_lang_directory_does_not_exist(): void
    {
        $this->files->deleteDirectory($this->langPath());

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('lang:publish');

        VueTranslation::compile();
    }

    public function test_it_fails_on_invalid_json(): void
    {
        $this->writeLangFile('en.json', '{"broken": ');

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('contains invalid JSON');

        VueTranslation::compile();
    }

    public function test_it_fails_when_a_php_file_does_not_return_an_array(): void
    {
        $this->writeLangFile('en/messages.php', '<?php return "text";');

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('must return an array');

        VueTranslation::compile();
    }
}
