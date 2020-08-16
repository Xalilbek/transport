<?php

namespace Custom\Models;

use Lib\MainDB;
use Models\Files;

class Pallet extends MainDB
{


    public static function getSource()
    {
        return "pallets";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id,
            ],
        ]);
    }

    public static function getNewId($merge = [])
    {
        $last = self::findFirst(array_merge(["sort" => ["id" => -1]], $merge));
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }


}
