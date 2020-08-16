<?php
namespace Lib;

use Models\Cache;
use Models\Translations;

class Lang
{
    public static $data;

    public static $lang = "en";

    public static $templateId = 1;

    public static $cacheSeconds = 100;

    public static $langs = [
        "en",
        "dk",
        "de",
    ];

    public static $testLangs = [
        "en",
        "dk",
        "de",
    ];

    public static $langData = [
        "en" => ["name" => "English", "original" => "English"],
        //"ru" => ["name" => "Russian", "original" => "Русский язык"],
        "dk" => ["name" => "Danish", "original" => "Danish"],
        //"ua" => ["name" => "Ukrainian", "original" => "Українська мова"],
        //"tr" => ["name" => "Turkish", "original" => "Türk dili"],
        //"az" => ["name" => "Azerbaijan", "original" => "Azərbaycan dili"],
        "de" => ["name" => "German", "original" => "Deutsch"],
    ];

    public static $templates = [
        1 => ["name" => "web"],
        2 => ["name" => "api"],
        3 => ["name" => "panel"],
        4 => ["name" => "app"],
        5 => ["name" => "admin"],
    ];

    public static function init($templateId, $lang = false)
    {
        if ($lang) {
            Lang::$lang = $lang;
        } else if (strlen(@$_POST["lang"]) > 1) {
            Lang::$lang = @$_POST["lang"];
        } else if (strlen(@$_POST["_setlang"]) > 1) {
            Lang::$lang = @$_POST["_setlang"];
        } else if (strlen(@$_GET["lang"]) > 1) {
            Lang::$lang = @$_GET["lang"];
            if (@$_COOKIE['lang'] !== Lang::$lang) {
                //setcookie("lang", Lang::$lang, time()+365*24*3600, "/");
            }
        } else if (strlen(@$_GET["_setlang"]) > 1) {
            Lang::$lang = @$_GET["_setlang"];
            if (@$_COOKIE['lang'] !== Lang::$lang) {
                setcookie("lang", Lang::$lang, time() + 365 * 24 * 3600, "/");
            }
        } else if (strlen(@$_COOKIE['lang']) > 1) {
            Lang::$lang = @$_COOKIE['lang'];
        } else if (!$lang || !in_array($lang, Lang::$langs)) {
            Lang::$lang = _MAIN_LANG_;
        } else {
            Lang::$lang = _MAIN_LANG_;
        }

        define("_LANG_", Lang::$lang);
        Lang::setLang(Lang::$lang);

        Lang::$templateId = $templateId;
        if (!Lang::$data) {
            Lang::getTranslationsBySiteID(Lang::$templateId);
        }
        return true;
    }

    public static function setLang($lang)
    {
        return Lang::$lang = $lang;
    }

    public static function getLang()
    {
        return Lang::$lang;
    }

    public static function getLangs()
    {
        return Lang::$langs;
    }

    public static function getTranslationsBySiteID($templateId)
    {
        $lang = strtolower(Lang::getLang());
        $data = Lang::getFromCache();
        if (!$data) {
            $data  = [];
            $query = Translations::find([["template_id" => (int) $templateId]]);
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $translation = (mb_strlen($value->translations->$lang) > 0) ? $value->translations->$lang : $value->translations->en;
                    if (mb_strlen($translation) < 1) {
                        foreach ($value->translations as $k => $v) {
                            if (mb_strlen($v) > 0) {
                                $translation = $v;
                            }
                        }
                    }

                    if (mb_strlen($translation) < 1) {
                        $translation = $value->key;
                    }

                    $data[$value->key] = $translation;
                }
            }

            foreach ($data as $key => $value) {
                $newKey = Lang::getReplacement($key);
                if ($newKey && $data[$newKey]) {
                    $data[$key] = $data[$newKey];
                }

            }

            Lang::saveCache($data);
        }

        return Lang::$data = $data;
    }

    public static function getReplacement($key)
    {
        $keyReplacements = [
            2 => [
                "Users"        => "Students",
                "User"         => "Student",
                "Employee"     => "Teacher",
                "Employees"    => "Teachers",
                "HisEmployees" => "HisTeachers",
                "HisUsers"     => "HisStudents",
            ],
            3 => [
                "Categories" => "Categories",
            ],
        ];
        $crmType = CRM_TYPE > 0 ? CRM_TYPE : 1;
        if ($keyReplacements[$crmType] && @$keyReplacements[$crmType][$key]) {
            return $keyReplacements[$crmType][$key];
        }

        return false;
    }

    public static function replaceLangKey($key)
    {
        $newKey = Lang::getReplacement($key);

        if ($newKey) {
            return $newKey;
        }

        return $key;
    }

    public static function get($key, $original = false)
    {
        $translation = Lang::replaceLangKey(trim($key));
        if (Lang::$data !== false) {
            if (@Lang::$data[$key]) {
                $translation = @Lang::$data[$key];
            } elseif (mb_strlen($key) > 0) {
                //Lang::$add($key, $original);
                $translation      = ($original) ? $original : $key;
                Lang::$data[$key] = $translation;
            }
        }
        return $translation;
    }

    public static function add($key, $original = false)
    {
        $key = trim($key);
        if (strlen($key) > 0 && !$data = Translations::findFirst([["key" => trim($key)]])) {
            $insert = [
                "template_id"  => [Lang::$templateId],
                "key"          => $key,
                "translations" => [
                    "en" => ($original) ? $original : $key,
                ],
                "created_at"   => Translations::getDate(),
            ];

            Translations::insert($insert);

            Lang::flushCache();
        } else {
            if (!in_array(Lang::$templateId, $data->template_id)) {
                if (is_array($data->template_id)) {
                    $data->template_id[] = Lang::$templateId;
                } else {
                    $data->template_id = [Lang::$templateId, (int) $data->template_id];
                }
                Translations::update(["key" => trim($key)], ["template_id" => $data->template_id]);
            }
        }
        return true;
    }

    public static function getFromCache()
    {
        return Cache::get(Lang::getCacheKey());
    }

    public static function getCacheKey()
    {
        return md5("trnslatins-" . Lang::$lang . "-" . Lang::$templateId);
    }

    public static function flushCache()
    {
        return Cache::set(Lang::getCacheKey(), false, time());
    }

    public static function saveCache($data)
    {
        return Cache::set(Lang::getCacheKey(), $data, time() + Lang::$cacheSeconds);
    }
}
