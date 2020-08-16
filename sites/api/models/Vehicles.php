<?php

namespace Custom\Models;

use Lib\Lang;
use Lib\MainDB;

class Vehicles extends MainDB
{
    public static function getSource()
    {
        return "vehicles";
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

    public static function statusList()
    {
        return [
            0 => [
                'text' => Lang::get('Active'),
                'value' => "1"
            ],
            1 => [
                'text' => Lang::get('Inactive'),
                'value' => "0"
            ],
        ];
    }

    public static function statusListByKey()
    {
        $list = [];
        foreach (self::statusList() as $row) {
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }

    public static function typeList()
    {
        return [
            0 => [
                'text' => Lang::get('Sedan'),
                'value' => 1,
            ],
            1 => [
                'text' => Lang::get('Suv'),
                'value' => 2,
            ],
            2 => [
                'text' => Lang::get('Pickup'),
                'value' => 3,
            ],
            3 => [
                'text' => Lang::get('Van'),
                'value' => 4,
            ],
            4 => [
                'text' => Lang::get('Minivan'),
                'value' => 5,
            ],
            5 => [
                'text' => Lang::get('Truck'),
                'value' => 6,
            ],
            6 => [
                'text' => Lang::get('MiniTruck'),
                'value' => 7,
            ],
            7 => [
                'text' => Lang::get('Coupe'),
                'value' => 8,
            ],
            8 => [
                'text' => Lang::get('Micro'),
                'value' => 9,
            ],
        ];
    }

    public static function typeListByKey()
    {
        $list = [];
        foreach (self::typeList() as $row) {
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }

//    public function updateUserId()
//    {
//        try {
//
//            $list = Vehicles::find([
//                [
//
//                ],
//                "sort" => [
//                    "_id" => 1
//                ]
//            ]);
//            foreach ($list as $value) {
//
//                if ($value->creator_id && !Users::objectId($value->creator_id)) {
//                    if ($mongoid = (string)Users::getById($value->creator_id)->_id) {
//
//                        Vehicles::update(
//                            ["creator_id" => $value->creator_id],
//                            [
//                                "creator_id" => (string)Users::getById($value->creator_id)->_id,
//                            ]
//                        );
//
//                    }
//                }
//                if ($value->deleter_id && !Users::objectId($value->deleter_id)) {
//                    if ($mongoid = (string)Users::getById($value->deleter_id)->_id) {
//
//                        Vehicles::update(
//                            ["deleter_id" => $value->deleter_id],
//                            [
//                                "deleter_id" => (string)Users::getById($value->deleter_id)->_id,
//                            ]
//                        );
//                    }
//                }
//
//            }
//
//            return "Vehicles updated";
//
//        } catch (\Exception $exception) {
//            return $exception;
//        }
//
//    }
}
