<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class Notes extends ApiDB
{
    public static $auth;

    public static function getSource(){
        return "notes";
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
    public function typeList()
    {
        $list = [
            [
                'text' => Lang::get('User'),
                'value' => 'user'
            ],
            [
                'text' => Lang::get('Calendar'),
                'value' => 'calendar'
            ],
            [
                'text' => Lang::get('Folder'),
                'value' => 'folder'
            ]
        ];

        if(CRM_TYPE == 2){
            $newList = [
                'case' => Lang::get('Case')
            ];
            foreach($newList as $key => $title){
                $list[] = [
                    'text' => $title,
                    'value' => $key
                ];
            }
        }

        return $list;
    }

    public function typeListByKey()
    {
        $list = [];
        foreach($this->typeList() as $row){
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }
}