<?php

namespace Tohidplus\Translation;

use Illuminate\Support\Facades\File;
use Tohidplus\Translation\Contract\TranslationFileHelper;

class LaravelTranslationFileHelper implements TranslationFileHelper
{

    /**
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function fetch()
    {
        return File::allFiles($this->resourcePath());
    }

    /**
     * @param array $data
     */
    public function write(array $data)
    {
        if (!File::exists($this->destinationPath())) {
            File::makeDirectory($this->destinationPath(), 0755, true, true);
        }
        File::put($this->destinationPath() . '/translations.json', json_encode($data));
    }

    /**
     * @return string
     */
    public function resourcePath()
    {
        return resource_path('lang');
    }

    /**
     * @return string
     */
    public function destinationPath()
    {
        return resource_path('js/VueTranslation');
    }
}
