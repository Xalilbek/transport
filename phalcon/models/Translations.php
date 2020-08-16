<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class Translations extends ApiDB
{
    public static function getSource(){
        return "translations";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
    public static function typeList()
    {
        return [
			0 => [
				'text' => Lang::get('Web'),
				'value' => 1
			],
			1 => [
				'text' => Lang::get('PartnerApi'),
				'value' => 2
			],
			2 => [
				'text' => Lang::get('AdminApi'),
				'value' => 3
            ],
            3 => [
				'text' => Lang::get('AdminFrontend'),
				'value' => 4
            ],
            4 => [
				'text' => Lang::get('PartnerFrontend'),
				'value' => 5
            ],
            5 => [
				'text' => Lang::get('App'),
				'value' => 6
			],
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
}