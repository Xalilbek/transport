<?php
namespace Lib;

class TimeZones
{
    /*
     * datetime: timestamp / datetime / mongodate
     *
     * options: [
     *    tzfrom: id from timezone list, default: You can define constant DEFAULT_TIMEZONE in settings.php if not defined value: 100 - server time GMT 0
     *    tzto: id from timezone list, default: You can define constant USER_TIMEZONE in AclApi.php if not defined value: 101 - Denmark
     *    formatfrom: in settings.php You can define constant "DEFAULT_DATE_FORMAT", if not defined default: "Y-m-d H:i:s"
     *    formatto: in settings.php You can define constant "DEFAULT_DATE_FORMAT", if not defined default: "Y-m-d H:i:s"
     * ]
     *
     *
     * $dateTo = TimeZones::date(strtotime($date), false, ["tzfrom" => 102, "tzto" => 101]);
     */
    public static function date($datetime=0, $realFormatto=false, $options=[])
    {
        $tzfrom         = $options["tzfrom"] ?  (int)$options["tzfrom"]: (defined("DEFAULT_TIMEZONE") ? DEFAULT_TIMEZONE: 100);
        $tzto           = $options["tzto"] ?  (int)$options["tzto"]: (defined("USER_TIMEZONE") ? USER_TIMEZONE: 101);
        $formatfrom     = $options["formatfrom"] ?  $options["formatfrom"]: (defined("DEFAULT_DATE_FORMAT") ? DEFAULT_DATE_FORMAT: "Y-m-d H:i:s");
        $formatto       = $realFormatto ?  $realFormatto: (defined("DEFAULT_DATE_FORMAT") ? DEFAULT_DATE_FORMAT: "Y-m-d H:i:s");

        $timestamp      = $datetime;
        if (method_exists($datetime, "toDateTime")) {
            $timestamp = round(@$datetime->toDateTime()->format("U.u"), 0);
        }elseif(!is_numeric($datetime)){
            $timestamp = strtotime($datetime);
        }

        $diff = 0;
        if($tzfrom !== $tzto)
            $diff = self::getDiff($timestamp, $tzfrom, $tzto);

        $timestamp -= $diff;

        $log = [
            "tzfrom"        => $tzfrom,
            "tzto"          => $tzto,
            "formatfrom"    => $formatfrom,
            "formatto"      => $formatto,
            "diff"          => $diff,
        ];

        //echo json_encode($log)."<br/>";

        if($realFormatto === "unix")
            return $timestamp;
        if($realFormatto === "mongo")
            return MainDB::getDate($timestamp);
        return date($formatto, $timestamp);
    }

    public static function getDiff($timestamp=0, $tzfrom=100, $tzto=101)
    {
        $tzfromData = self::getById($tzfrom);
        $year = date("Y");
        if($tzfromData){
            $diffFrom = $tzfromData["timediff"];
            if($tzfromData["daylight"]){
                if($tzfromData["daylight"]["winter"][$year]){
                    if($timestamp > strtotime($tzfromData["daylight"]["winter"][$year]) && $timestamp < strtotime($tzfromData["daylight"]["summer"][$year])){
                        $diffFrom -= 3600;
                    }
                }
            }
        }

        $tztoData = self::getById($tzto);
        if($tztoData){
            $diffTo = $tztoData["timediff"];
            if($tztoData["daylight"]){
                if($tztoData["daylight"]["winter"][$year]){
                    if($timestamp > strtotime($tztoData["daylight"]["winter"][$year]) && $timestamp < strtotime($tztoData["daylight"]["summer"][$year])){
                        $diffTo -= 3600;
                    }
                }
            }
        }

        $log = [
            "fromTz"            => $tzfromData["titles"]["en"],
            "fromDefaultDiff"   => $tzfromData["timediff"],
            "fromFilteredDiff"  => $diffFrom,
            "toTz"              => $tztoData["titles"]["en"],
            "toDefaultDiff"     => $tztoData["timediff"],
            "toFilteredDiff"    => $diffTo,
        ];
        //echo json_encode($log)."<br/>";
        return ($diffFrom - $diffTo);
    }


    /*
     * countryCode: $_SERVER["HTTP_CF_IPCOUNTRY"], Cloudflare returns country code: AZ
     */
    public static function detectTz($countryCode=false, $datetime=false)
    {
        if(!$countryCode)
            $countryCode = @$_SERVER["HTTP_CF_IPCOUNTRY"];
        $timezone = self::getByCountryCode($countryCode);
        if(!$timezone)
            $timezone = self::getById(101);

        $currentDate = self::date(time(), false, ["tzfrom" => 100, "tzto" => $timezone["id"]]);
        return [
            "id"            => $timezone["id"],
            "title"         => $timezone["titles"]["en"],
            //"current_date"  => $currentDate,
            "current_time"  => strtotime($currentDate),
        ];
    }

    public static function getByCountryCode($countryCode)
    {
        foreach (self::getList() as $value)
        {
            if($value["country_code"] == mb_strtolower($countryCode))
                return $value;
        }
        return false;
    }

    public static function getById($id)
    {
        return self::getList()[(int)$id];
    }

    public static function getList($lang=false)
    {
        return [
            100 => [
                "id"    => 100,
                "titles" => [
                    "dk" => "Iceland (GMT+0)",
                    "en" => "Iceland (GMT+0)",
                ],
                "timediff"  => 0,
            ],
            101 => [
                "id"    => 101,
                "titles" => [
                    "dk" => "København (GMT+1)",
                    "en" => "Copenhagen, Denmark (GMT+1)",
                ],
                "keywords" => "copenhagen denmark",
                "daylight" => [
                    "winter" => [ // 0
                                  "2020" => "2020-03-29 03:00:00",
                                  "2021" => "2020-03-28 03:00:00",
                    ],
                    "summer" => [ // -1
                                  "2020" => "2020-10-25 03:00:00",
                                  "2021" => "2020-10-31 03:00:00",
                    ]
                ],
                "timediff"  => 3600,
                "country_code"  => "dk",
            ],
            102 => [
                "id"    => 102,
                "titles" => [
                    "dk" => "Baku (GMT+4)",
                    "en" => "Baku, Azerbaijan (GMT+4)",
                ],
                "keywords" => "baku azerbaijan",
                "timediff"  => 14400,
                "country_code"  => "az",
            ],
            150 => [
                "id"    => 150,
                "titles" => [
                    "dk" => "New york",
                    "en" => "New york, USA (EST)",
                ],
                "keywords" => "baku azerbaijan",
                "daylight" => [
                    "winter" => [
                        "2020" => "2020-03-08 02:00:00",
                        "2021" => "2020-03-14 02:00:00",
                    ],
                    "summer" => [
                        "2020" => "2020-11-01 02:00:00",
                        "2021" => "2020-10-07 02:00:00",
                    ]
                ],
                "timediff" => -18000,
                "country_code"  => "us",
            ],
        ];
    }

}