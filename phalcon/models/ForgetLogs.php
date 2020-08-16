<?php
namespace Models;

use Lib\ApiDB;

class ForgetLogs extends ApiDB
{
    public static function getSource(){
        return "logs_forgot";
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