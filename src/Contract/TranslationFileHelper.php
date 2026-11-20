<?php

namespace Tohidplus\Translation\Contract;

use Symfony\Component\Finder\SplFileInfo;

interface TranslationFileHelper
{
    /**
     * Get the translation files (.php and .json) in the lang directory.
     *
     * @return SplFileInfo[]
     *
     * @throws \Tohidplus\Translation\Exceptions\TranslationException
     */
    public function fetch(): array;

    /**
     * Write the compiled translations to the translations.json file.
     *
     * @param  array<array-key, mixed>  $data
     */
    public function write(array $data): void;

    /**
     * Get the path of the lang directory to compile.
     */
    public function resourcePath(): string;

    /**
     * Get the path of the directory that receives the translations.json file.
     */
    public function destinationPath(): string;
}
