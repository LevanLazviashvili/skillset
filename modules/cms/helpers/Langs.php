<?php namespace cms\helpers;

use Config;
use File as Filesystem;
use Illuminate\Support\Arr;

class Langs
{
    private static $Langs;
   public static function get($key)
   {
       $Langs = self::getLangs();
       return Arr::get($Langs, $key, '');

   }

    private static function getLangs()
    {
        if (!empty(self::$Langs)) {
            return self::$Langs;
        }
        $lang = self::detectLang();
        $lang = self::checkLang($lang);
        $langPath = storage_path().'/app/langs/%s.json';
        $Langs = file_get_contents(sprintf($langPath, $lang));
        if (!$Langs) {
            self::$Langs = [];
            return;
        }
        $Langs = json_decode($Langs, 1);
        self::$Langs = $Langs;
        return $Langs;
    }

    public static function detectLang()
    {
        $urlPaths = explode('/', request()->getRequestUri());
        return Arr::get($urlPaths, 1);
    }

    public static function checkLang($lang)
    {
        $langPath = storage_path().'/app/langs/%s.json';
        if (!file_exists(sprintf($langPath, $lang))) {
            return config('app.default_lang');
        }
        return $lang;
    }
}
