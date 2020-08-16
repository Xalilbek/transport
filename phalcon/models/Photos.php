<?php
namespace Models;

use Lib\ApiDB;

class Photos extends ApiDB
{
    public static function getSource(){
        return "photos";
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