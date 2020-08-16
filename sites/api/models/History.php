<?php
namespace Custom\Models;

use Lib\MainDB;

class History extends MainDB
{
    public static function getSource(){
        return "history";
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