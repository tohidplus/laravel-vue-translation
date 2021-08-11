<?php

namespace Tohidplus\Translation;

use Tohidplus\Translation\Contract\TranslationFileHelper;

class LaravelVueTranslation

{
    /**
     * @var array $translations
     */
    private $translations = [];
    /**
     * @var TranslationFileHelper
     */
    private $translationFileHelper;

    /**
     * LaravelJSTranslation constructor.
     * @param TranslationFileHelper $translationFileHelper
     */
    public function __construct(TranslationFileHelper $translationFileHelper)
    {
        $this->translationFileHelper = $translationFileHelper;
    }

    public function compile()
    {
        $this->printCompileStarted();
        $this->setTranslations();
        $this->translationFileHelper->write($this->translations);
        $this->printCompileEnded();
    }

    private function addArrayLevels(array $keys, array $target, $data)
    {
        if ($keys) {
            $key = array_shift($keys);
            if (!count($keys)) {
                $target[$key] = $data;
            } else {
                $target[$key] = $this->addArrayLevels($keys, [], $data);
            }
        }
        return $target;
    }

    private function setTranslations()
    {
        $this->translations = [];
        foreach ($this->translationFileHelper->fetch() as $file) {
            $path = $file->getRelativePathName();
            $this->printFileCompiled($path);
            $delimiter = strpos($path, '/') !== false ? '/' : '\\';
            
            $array = array_map(function ($key) use ($file) {
                return str_replace('.' . $file->getExtension(), '', $key);
            }, explode($delimiter, $path));

            $nestedArray = $this->addArrayLevels($array, [], 
                $file->getExtension() === 'json'  
                    ? json_decode(file_get_contents($file->getPathName()), true)
                    : require $file->getPathName()
            );
            
            $this->translations = array_merge_recursive($this->translations, $nestedArray);
        }
    }

    protected function printCompileStarted(): void
    {
        CLIPrinter::clear();
        CLIPrinter::print("\n\nCompiling...\n", CLIPrinter::FOREGROUND_BLUE, CLIPrinter::BACKGROUND_BLACK);
        usleep(50000);
    }

    /**
     * @param $path
     */
    private function printFileCompiled($path): void
    {
        CLIPrinter::print($path, CLIPrinter::FOREGROUND_YELLOW, CLIPrinter::BACKGROUND_BLACK);
        usleep(25000);
    }

    private function printCompileEnded()
    {
        CLIPrinter::print("\nThe translations.json file updated successfully.", CLIPrinter::FOREGROUND_GREEN);
    }
}
