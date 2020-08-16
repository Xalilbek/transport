<?php
namespace Custom\Models;

use Lib\MainDB;

class LogsRawTracking extends MainDB
{
    public static function getSource(){
        return "logs_tracking_raw";
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