<?php
namespace Models;

use Lib\ApiDB;

class LogsAccess extends ApiDB
{
    public static function getSource(){
        return "logs_access";
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