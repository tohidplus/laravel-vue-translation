<?php


namespace Tohidplus\Translation\Contract;


interface TranslationFileHelper
{
    public function fetch();

    public function write(array $data);

    public function resourcePath();

    public function destinationPath();
}
