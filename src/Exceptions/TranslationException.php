<?php

namespace Tohidplus\Translation\Exceptions;

use RuntimeException;
use Throwable;

class TranslationException extends RuntimeException
{
    public static function langDirectoryNotFound(string $path): self
    {
        return new self(
            "The lang directory [{$path}] does not exist. Run [php artisan lang:publish] or add your translation files first."
        );
    }

    public static function invalidJson(string $path, Throwable $previous): self
    {
        return new self("The translation file [{$path}] contains invalid JSON: {$previous->getMessage()}.", 0, $previous);
    }

    public static function notAnArray(string $path): self
    {
        return new self("The translation file [{$path}] must return an array.");
    }
}
