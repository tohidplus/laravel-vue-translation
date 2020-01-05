<?php


namespace Tohidplus\Translation;

class CLIPrinter
{
    const FOREGROUND_BLACK = '';
    const FOREGROUND_DARK_GREY = ';1;30';
    const FOREGROUND_RED = ';0;30';
    const FOREGROUND_LIGHT_RED = ';1;31';
    const FOREGROUND_GREEN = ';0;32';
    const FOREGROUND_LIGHT_GREEN = ';1;32';
    const FOREGROUND_BROWN = ';0;33';
    const FOREGROUND_YELLOW = ';1;33';
    const FOREGROUND_BLUE = ';0;34';
    const FOREGROUND_LIGHT_BLUE = ';1;34';
    const FOREGROUND_MAGENTA = ';0;35';
    const FOREGROUND_LIGHT_MAGENTA = ';1;35';
    const FOREGROUND_CYAN = ';0;36';
    const FOREGROUND_GRAY = ';1;36';
    const FOREGROUND_LIGHT_GREY = ';0;37';
    const FOREGROUND_WHITE = '';
    //
    const BACKGROUND_BLACK = '';
    const BACKGROUND_RED = '41';
    const BACKGROUND_GREEN = '42';
    const BACKGROUND_YELLOW = '43';
    const BACKGROUND_BLUE = '44';
    const BACKGROUND_MAGENTA = '45';
    const BACKGROUND_CYAN = '46';
    const BACKGROUND_LIGHT_GREY = '47';

    public static function print($message, $foreground = self::FOREGROUND_WHITE, $background = self::BACKGROUND_BLACK)
    {
        echo "\e[{$background}{$foreground}m {$message} \e[0m\n";
    }

    public static function clear()
    {
        echo chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J';
    }
}
