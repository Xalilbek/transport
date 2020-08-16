<?php
namespace Lib;

use Models\Cache;
use Models\Translations;

class Translation
{
    public $data;

    public $lang = "en";

    public $templateId = 1;

    public $cacheSeconds = 100;

    public $langs = [
        "en",
        "dk",
        "de",
    ];

    public $testLangs = [
        "en",
        "dk",
        "de",
    ];

    public $langData = [
        "en" => ["name" => "English", "original" => "English"],
        //"ru" => ["name" => "Russian", "original" => "Русский язык"],
        "dk" => ["name" => "Danish", "original" => "Danish"],
        //"ua" => ["name" => "Ukrainian", "original" => "Українська мова"],
        //"tr" => ["name" => "Turkish", "original" => "Türk dili"],
        //"az" => ["name" => "Azerbaijan", "original" => "Azərbaycan dili"],
        "de" => ["name" => "German", "original" => "Deutsch"],
    ];

    public $templates = [
        1 => ["name" => "web"],
        2 => ["name" => "api"],
        3 => ["name" => "panel"],
        4 => ["name" => "app"],
        5 => ["name" => "admin"],
    ];

    public function init($templateId, $lang = false)
    {
        if ($lang) {
            $this->lang = $lang;
        } else if (strlen(@$_POST["lang"]) > 1) {
            $this->lang = @$_POST["lang"];
        } else if (strlen(@$_POST["_setlang"]) > 1) {
            $this->lang = @$_POST["_setlang"];
        } else if (strlen(@$_GET["lang"]) > 1) {
            $this->lang = @$_GET["lang"];
            if (@$_COOKIE['lang'] !== $this->lang) {
                //setcookie("lang", $this->lang, time()+365*24*3600, "/");
            }
        } else if (strlen(@$_GET["_setlang"]) > 1) {
            $this->lang = @$_GET["_setlang"];
            if (@$_COOKIE['lang'] !== $this->lang) {
                setcookie("lang", $this->lang, time() + 365 * 24 * 3600, "/");
            }
        } else if (strlen(@$_COOKIE['lang']) > 1) {
            $this->lang = @$_COOKIE['lang'];
        } else if (!$lang || !in_array($lang, $this->langs)) {
            $this->lang = _MAIN_LANG_;
        } else {
            $this->lang = _MAIN_LANG_;
        }

        define("_LANG_", $this->lang);
        $this->setLang($this->lang);

        $this->templateId = $templateId;
        if (!$this->data) {
            $this->getTranslationsBySiteID($this->templateId);
        }
        return true;
    }

    public function setLang($lang)
    {
        return $this->lang = $lang;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getLangs()
    {
        return $this->langs;
    }

    public function getTranslationsBySiteID($templateId)
    {
        $lang = strtolower(Lang::getLang());
        $data = $this->getFromCache();
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
                $newKey = $this->getReplacement($key);
                if ($newKey && $data[$newKey]) {
                    $data[$key] = $data[$newKey];
                }

            }

            $this->saveCache($data);
        }

        return $this->data = $data;
    }

    public function getReplacement($key)
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

    public function replaceLangKey($key)
    {
        $newKey = $this->getReplacement($key);

        if ($newKey) {
            return $newKey;
        }

        return $key;
    }

    public function get($key, $original = false)
    {
        $translation = $this->replaceLangKey(trim($key));
        if ($this->data !== false) {
            if (@$this->data[$key]) {
                $translation = @$this->data[$key];
            } elseif (mb_strlen($key) > 0) {
                //$this->add($key, $original);
                $translation      = ($original) ? $original : $key;
                $this->data[$key] = $translation;
            }
        }
        return $translation;
    }

    public function add($key, $original = false)
    {
        $key = trim($key);
        if (strlen($key) > 0 && !$data = Translations::findFirst([["key" => trim($key)]])) {
            $insert = [
                "template_id"  => [$this->templateId],
                "key"          => $key,
                "translations" => [
                    "en" => ($original) ? $original : $key,
                ],
                "created_at"   => Translations::getDate(),
            ];

            Translations::insert($insert);

            $this->flushCache();
        } else {
            if (!in_array($this->templateId, $data->template_id)) {
                if (is_array($data->template_id)) {
                    $data->template_id[] = $this->templateId;
                } else {
                    $data->template_id = [$this->templateId, (int) $data->template_id];
                }
                Translations::update(["key" => trim($key)], ["template_id" => $data->template_id]);
            }
        }
        return true;
    }

    public function getFromCache()
    {
        return Cache::get($this->getCacheKey());
    }

    public function getCacheKey()
    {
        return md5("trnslatins-" . $this->lang . "-" . $this->templateId);
    }

    public function flushCache()
    {
        return Cache::set($this->getCacheKey(), false, time());
    }

    public function saveCache($data)
    {
        return Cache::set($this->getCacheKey(), $data, time() + $this->cacheSeconds);
    }
}
