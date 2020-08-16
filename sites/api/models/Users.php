<?php
namespace Custom\Models;

use Lib\ApiDB;
use Lib\Lang;

class Users extends ApiDB
{
    public static function getSource(){
        return "users";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
    public static function getByMongoId($id){
        return self::findFirst([
            [
                "_id" => $id
            ]
        ]);
    }


    public static function getByPhone($phone)
    {
        return self::findFirst([
            [
                "phone"         => $phone,
                "is_deleted"    => 0
            ]
        ]);
    }

    public static function getByUsername($username)
    {
        return self::findFirst([
            [
                "username"         => strtolower($username),
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

    public static function statusList()
    {
        return [
            0 => [
                "label"     => Lang::get("Unverified"),
                "color"     => "#ed0000",
            ],
            1 => [
                "label"     => Lang::get("Active"),
                "color"     => "#1f9810",
            ],
            2 => [
                "label"     => Lang::get("InModeration", "In moderation"),
                "color"     => "#ff7810",
            ],
        ];
    }

    public static function levelList()
    {
        return [
			0 => [
				'text' => Lang::get('Operator'),
				'value' => 'oparator'
			],
			1 => [
				'text' => Lang::get('Supervisor'),
				'value' => 'supervisor'
			],
			2 => [
				'text' => Lang::get('Administrator'),
				'value' => 'administrator'
			],
		];
    }

    public static function levelListByKey()
    {
        $list = [];
        foreach(self::levelList() as $row){
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }

    public static function typeList()
    {
        return [
			0 => [
				'text' => Lang::get('Moderator'),
				'value' => 'moderator'
			],
			1 => [
				'text' => Lang::get('Employee'),
				'value' => 'employee'
			],
			2 => [
				'text' => Lang::get('User'),
				'value' => 'user'
			],
			3 => [
				'text' => Lang::get('Partner'),
				'value' => 'partner'
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

    public static function filterWorkHours($workHours){ 
        $data = [];
        foreach($workHours as $week => $items){
            foreach($items as $value){
                if(in_array((int)$week, [1,2,3,4,5,6,7]) && is_numeric($value["id"])){
                    $data[$week][] = [
                        "from" => [
                            "hour" => (string) $value["from"]["hour"],
                            "minute" => (string) $value["from"]["minute"],
                        ],
                        "to" => [
                            "hour" => (string) $value["to"]["hour"],
                            "minute" => (string) $value["to"]["minute"],
                        ],
                        "odd" => (float) $value["odd"],
                        "id" => (int) $value["id"]
                    ];
                }
            }
        }
        return $data;
    }
}