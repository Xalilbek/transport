<?php
namespace Custom\Models;

use Lib\Lang;
use Lib\MainDB;

class Deliveries extends MainDB
{
    public static function getSource(){
        return "deliveries";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
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

    public static function statusList()
    {
        return [
			0 => [
				'text' => Lang::get('Pending'),
				'value' => 0
			],
			1 => [
				'text' => Lang::get('Deliveried'),
				'value' => 1
			],
		];
    }

    public static function statusListByKey()
    {
        $list = [];
        foreach(self::statusList() as $row){
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }


}