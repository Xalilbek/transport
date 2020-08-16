<?php
namespace Models;

use Lib\ApiDB;

class TempFiles extends ApiDB
{
    public static function getSource(){
        return "files_temp";
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