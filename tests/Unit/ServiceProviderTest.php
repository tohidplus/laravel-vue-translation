<?php

namespace Tohidplus\Translation\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Tohidplus\Translation\Contract\TranslationFileHelper;
use Tohidplus\Translation\Facades\VueTranslation;
use Tohidplus\Translation\LaravelTranslationFileHelper;
use Tohidplus\Translation\LaravelVueTranslation;
use Tohidplus\Translation\Tests\TestCase;
use Tohidplus\Translation\TranslationServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_the_facade_and_the_container_use_one_instance(): void
    {
        $instance = $this->app->make(LaravelVueTranslation::class);

        $this->assertSame($instance, $this->app->make('VueTranslation'));
        $this->assertSame($instance, VueTranslation::getFacadeRoot());
    }

    public function test_the_default_file_helper_uses_the_laravel_paths(): void
    {
        $helper = $this->app->make(LaravelTranslationFileHelper::class);

        $this->assertSame(lang_path(), $helper->resourcePath());
        $this->assertSame(resource_path('js/VueTranslation'), $helper->destinationPath());
        $this->assertSame(
            resource_path('js/VueTranslation').DIRECTORY_SEPARATOR.'translations.json',
            $helper->outputFile()
        );
    }

    public function test_it_does_not_replace_a_file_helper_that_the_application_bound(): void
    {
        // TestCase::setUp() binds a file helper that uses a temporary lang directory.
        (new TranslationServiceProvider($this->app))->register();

        $this->assertSame($this->langPath(), $this->app->make(TranslationFileHelper::class)->resourcePath());
    }

    public function test_it_registers_the_command(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('VueTranslation:generate', $commands);
        $this->assertArrayHasKey('vue-translation:generate', $commands);
    }

    public function test_it_publishes_the_javascript_files(): void
    {
        $paths = ServiceProvider::pathsToPublish(TranslationServiceProvider::class, 'vue-translation');

        $this->assertCount(1, $paths);

        $source = array_key_first($paths);

        $this->assertSame(resource_path('js/VueTranslation'), $paths[$source]);
        $this->assertFileExists($source.'/Translation.js');
        $this->assertFileExists($source.'/translator.js');
        $this->assertSame('{}', trim(file_get_contents($source.'/translations.json')));
    }
}
