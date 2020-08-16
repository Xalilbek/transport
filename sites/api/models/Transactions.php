<?php
namespace Custom\Models;

use Lib\Lang;
use Lib\MainDB;

class Transactions extends MainDB
{
    public static function getSource(){
        return "transactions";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }

    public static function getNewId()
    {
        $last = self::findFirst(["sort" => ["id" => -1]]);
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }

    public static function typeList()
    {
        return [
			0 => [
				'text' => Lang::get('Case'),
				'value' => 'case'
			],
			1 => [
				'text' => Lang::get('User'),
				'value' => 'user'
			]
		];
    }

    public static function typeListByKey()
    {
        $list = [];
        foreach(self::typeList() as $row){
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }

    public static function statusList()
    {
        return [
			0 => [
				'text' => Lang::get('Denied'),
				'value' => "0"
			],
			1 => [
				'text' => Lang::get('Verified'),
				'value' => "1"
            ],
            2 => [
				'text' => Lang::get('Pending'),
				'value' => "2"
			]
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