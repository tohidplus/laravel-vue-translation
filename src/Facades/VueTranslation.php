<?php

namespace Tohidplus\Translation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array compile(?callable $onFileCompiled = null)
 * @method static array translations(?callable $onFileCompiled = null)
 * @method static string outputFile()
 * @method static string langPath()
 *
 * @see \Tohidplus\Translation\LaravelVueTranslation
 */
class VueTranslation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'VueTranslation';
    }
}
