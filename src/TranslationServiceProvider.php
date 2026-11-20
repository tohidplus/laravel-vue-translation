<?php

namespace Tohidplus\Translation;

use Illuminate\Support\ServiceProvider;
use Tohidplus\Translation\Console\Commands\Translation;
use Tohidplus\Translation\Contract\TranslationFileHelper;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bindIf(TranslationFileHelper::class, LaravelTranslationFileHelper::class);

        $this->app->singleton(LaravelVueTranslation::class);
        $this->app->alias(LaravelVueTranslation::class, 'VueTranslation');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Translation::class,
        ]);

        $this->publishes([
            __DIR__.'/resources/js/VueTranslation' => resource_path('js/VueTranslation'),
        ], 'vue-translation');
    }
}
