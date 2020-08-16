<?php
namespace Custom\Models;

use Lib\Lang;
use Lib\MainDB;
use Lib\TimeZones;

class Objects extends MainDB
{
    public static function getSource(){
        return "objects";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "_id" => (int)$id
            ]
        ]);
    }
    public static function getByImei($imei)
    {

        return self::findFirst([
            [
                "imei" => $imei,
            ]
        ]);
    }

    public static function getNewId()
    {
        $last = self::findFirst([["business_id"=>0],"sort" => ["id" => -1]]);
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }

    public static function getLonLatFromGeometry($geometry)
    {
        if($geometry->coordinates)
        {
            return [
                (float)$geometry->coordinates[0],
                (float)$geometry->coordinates[1],
            ];
        }
        else
        {
            return [false, false];
        }

    }


    public static function getStatusList()
    {
        return [
            0 => [
                "value" => 0,
                "title" => Lang::get("Stop"),
            ],
            1 => [
                "value" => 1,
                "title" => Lang::get("Active")
            ],
            2 => [
                "value" => 2,
                "title" => Lang::get("BalanceIsInsufficient", "Balance is insufficient")
            ],
        ];
    }

    public static function filterData( $value, $objectTypes=false)
    {

        $status = Objects::toSeconds($value->connected_at) > self::getTime() - ONLINE_TIME ? 1: 0;
        $error = false;

        if($value->status == 2){
            $status = 2;
            $error = Lang::get("BalanceIsInsufficient", "Balance is insufficient");
        }
        list($lon, $lat) = Objects::getLonLatFromGeometry($value->geometry);

        $obj = [
            "id"			=> (string)$value->_id,
            "title"			=> $value->title,
            "imei"			=> $value->imei,
            "coords"		=> [
                "lng" => $lon,
                "lat" => $lat
            ],
            "angle"	    => (float)@$value->angle,
            "dasd"	    => (time() - Objects::toSeconds($value->connected_at)) / 60,
            "status"	=> Objects::getStatusList()[$status],
            "lastdate"  => TimeZones::date($value->connected_at, "Y-m-d H:i:s"),
            "lasttime"  => self::secondsToTime( Objects::toSeconds($value->connected_at)),
            "speed"     => $status > 0 ? $value->speed." km/s": "0 km/s",
            "address"   => ($value->address) ? (string)@$value->address->name: "-",
            "icon"      => $value->icon > 0 ? (int)$value->icon: 1,
            "type"      => strlen($value->type) > 0 ? $value->type: false,
            "vehicle_id"=> (string)$value->vehicle_id
        ];

        if($error)
            $obj["error"] = $error;
        if($objectTypes && $objectTypes[$value->type]){
            /**
            $objectType = $objectTypes[$value->type];
            $obj["type"] = ($objectType) ? [
            "type"	=> (int)$value->type,
            "title"	=> $objectType["title"]
            ]: [
            "type"	=> (int)$value->type,
            "title"	=> ""
            ];*/
        }

        return $obj;
    }


    public static function secondsToTime( $inputSeconds) {
        $seconds = MainDB::getTime() - $inputSeconds;

        if($seconds/60 < 60){
            $minutes = (int)($seconds/60);
        }elseif($seconds/3600 < 24){
            $hours = (int)($seconds/3600);
        }elseif($seconds/86400 < 35){
            $days = (int)($seconds/86400);
        }elseif($seconds/(30*86400) < 13){
            $months = (int)($seconds/86400/30);
        }

        if($seconds < 120){
            $date_text = Lang::get("Online");
        }elseif($minutes > 0 && $minutes < 61){
            $date_text = $minutes." ".Lang::get("minutes", "minutes")." ".Lang::get("ago");
        }elseif($hours > 0 && $hours < 25){
            $date_text = $hours." ".Lang::get("hours", "hours")." ".Lang::get("ago");
        }elseif($days > 0 && $days < 34){
            $date_text = $days." ".Lang::get("days", "days")." ".Lang::get("ago");
        }elseif($months > 0 && $months < 12){
            $date_text = $months." ".Lang::get("months", "month(s)")." ".Lang::get("ago");
        }else{
            $date_text = date("Y-m-d H:i:s", $inputSeconds);
        }
        return trim($date_text);
    }
}