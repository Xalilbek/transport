<?php
namespace Custom\Models;

use Lib\MainDB;

class TrackingStatistics extends MainDB
{
    public static function getSource(){
        return "tracking_statistics";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
}