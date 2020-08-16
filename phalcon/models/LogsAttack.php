<?php
namespace Models;

use Lib\ApiDB;

class LogsAttack extends ApiDB
{
    public static function getSource(){
        return "logs_attack";
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