<?php
namespace Custom\Models;

use Lib\ApiDB;

class Tokens extends ApiDB
{
    public static function getSource(){
        return "user_tokens";
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