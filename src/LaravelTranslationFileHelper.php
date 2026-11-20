<?php

namespace Tohidplus\Translation;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Tohidplus\Translation\Contract\TranslationFileHelper;
use Tohidplus\Translation\Exceptions\TranslationException;

class LaravelTranslationFileHelper implements TranslationFileHelper
{
    /**
     * The file extensions that contain translations.
     */
    public const EXTENSIONS = ['php', 'json'];

    /**
     * The name of the compiled file.
     */
    public const FILENAME = 'translations.json';

    /**
     * @param  string|null  $langPath  The lang directory. Defaults to lang_path().
     * @param  string|null  $outputPath  The output directory. Defaults to resources/js/VueTranslation.
     */
    public function __construct(
        protected Filesystem $files,
        protected ?string $langPath = null,
        protected ?string $outputPath = null,
    ) {
    }

    public function fetch(): array
    {
        $path = $this->resourcePath();

        if (! $this->files->isDirectory($path)) {
            throw TranslationException::langDirectoryNotFound($path);
        }

        return array_values(array_filter(
            $this->files->allFiles($path),
            fn (SplFileInfo $file) => in_array(strtolower($file->getExtension()), self::EXTENSIONS, true)
        ));
    }

    public function write(array $data): void
    {
        $this->files->ensureDirectoryExists($this->destinationPath());

        $this->files->put(
            $this->outputFile(),
            json_encode((object) $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );
    }

    public function resourcePath(): string
    {
        return $this->langPath ?? lang_path();
    }

    public function destinationPath(): string
    {
        return $this->outputPath ?? resource_path('js/VueTranslation');
    }

    /**
     * Get the full path of the compiled translations.json file.
     */
    public function outputFile(): string
    {
        return rtrim($this->destinationPath(), '/\\').DIRECTORY_SEPARATOR.static::FILENAME;
    }
}
