<?php
namespace Custom\Models;

use Lib\MainDB;

class LogsUnknownTracking extends MainDB
{
    public static function getSource(){
        return "logs_unknown_tracking";
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