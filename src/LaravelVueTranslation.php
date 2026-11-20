<?php

namespace Tohidplus\Translation;

use JsonException;
use Symfony\Component\Finder\SplFileInfo;
use Tohidplus\Translation\Contract\TranslationFileHelper;
use Tohidplus\Translation\Exceptions\TranslationException;

class LaravelVueTranslation
{
    public function __construct(protected TranslationFileHelper $translationFileHelper)
    {
    }

    /**
     * Compile the translation files and write them to the translations.json file.
     *
     * @param  (callable(string): void)|null  $onFileCompiled  Receives the relative path of each compiled file.
     * @return array<array-key, mixed> The compiled translations.
     *
     * @throws TranslationException
     */
    public function compile(?callable $onFileCompiled = null): array
    {
        $translations = $this->translations($onFileCompiled);

        $this->translationFileHelper->write($translations);

        return $translations;
    }

    /**
     * Read the translation files into one array, keyed by locale.
     *
     * The file "en/messages.php" becomes "en.messages", "en/admin/users.php"
     * becomes "en.admin.users", and the file "en.json" becomes "en".
     *
     * @param  (callable(string): void)|null  $onFileCompiled  Receives the relative path of each compiled file.
     * @return array<array-key, mixed>
     *
     * @throws TranslationException
     */
    public function translations(?callable $onFileCompiled = null): array
    {
        $translations = [];

        foreach ($this->translationFileHelper->fetch() as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            $keys = explode('/', substr($path, 0, -strlen($file->getExtension()) - 1));

            $translations = array_replace_recursive($translations, $this->nest($keys, $this->load($file)));

            if ($onFileCompiled !== null) {
                $onFileCompiled($path);
            }
        }

        return $translations;
    }

    /**
     * Get the full path of the translations.json file.
     */
    public function outputFile(): string
    {
        return rtrim($this->translationFileHelper->destinationPath(), '/\\')
            .DIRECTORY_SEPARATOR.LaravelTranslationFileHelper::FILENAME;
    }

    /**
     * Get the path of the lang directory.
     */
    public function langPath(): string
    {
        return $this->translationFileHelper->resourcePath();
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws TranslationException
     */
    protected function load(SplFileInfo $file): array
    {
        $path = $file->getPathname();

        if (strtolower($file->getExtension()) === 'json') {
            try {
                $lines = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw TranslationException::invalidJson($path, $exception);
            }
        } else {
            // In watch mode the same file is loaded again. Make sure OPcache does not return an old version.
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($path, true);
            }

            $lines = (static fn (string $__path) => require $__path)($path);
        }

        if (! is_array($lines)) {
            throw TranslationException::notAnArray($path);
        }

        return $lines;
    }

    /**
     * Put the value at the nested position given by the keys.
     *
     * @param  string[]  $keys
     * @param  array<array-key, mixed>  $value
     * @return array<array-key, mixed>
     */
    protected function nest(array $keys, array $value): array
    {
        foreach (array_reverse($keys) as $key) {
            $value = [$key => $value];
        }

        return $value;
    }
}
