<?php
namespace Custom\Models;

use Lib\MainDB;

class LogsTracking extends MainDB
{
    public static function getSource(){
        return "logs_tracking";
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