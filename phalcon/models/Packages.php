<?php
namespace Models;

use Lib\Lang;

class Packages
{
    public function list()
    {
        return [
            0 => [
                "title" => Lang::get("Econom"),
                "id"    => 1,
            ],
            1 => [
                "title" => Lang::get("Standart"),
                "id"    => 3,
            ],
            2 => [
                "title" => Lang::get("Premium"),
                "id"    => 5,
            ],
        ];
    }

    public function listByKey()
    {
        $list = [];
        foreach(self::list() as $row){
            $list[$row["id"]] = $row["title"];
        }
        return $list;
    }
}