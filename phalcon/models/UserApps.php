<?php
namespace Models;

use Lib\ApiDB;

class UserApps extends ApiDB
{
    public static function getSource(){
        return "user_apps";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
    
    public static function getNewId($binds = [])
    {
        $last = self::findFirst([
            $binds,
            "sort" => [
                "id" => -1
            ]
        ]);

        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }

}