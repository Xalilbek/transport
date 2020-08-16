<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class NoteFolders extends ApiDB
{
    public static function getSource(){
        return "note_folders";
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
			],
			2 => [
				'text' => Lang::get('Calendar'),
				'value' => 'calendar'
            ],
            3 => [
				'text' => Lang::get('Folder'),
				'value' => 'folder'
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