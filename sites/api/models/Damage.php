<?php
namespace Custom\Models;

use Lib\MainDB;
use Models\Files;

class Damage extends MainDB
{

    const TYPE_VEHICLE = 'vehicle';
    const TYPE_DELIVERY = 'delivery';

    public static function getSource()
    {
        return "damages";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int) $id,
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

    public function getPhotoList($photoIds){

        $photo_ids = [];

        foreach ($photoIds as $photo){
            $photo_ids[] = Files::objectId($photo);
        }


        $photos = Files::find([
            [
                "_id"     => [
                    '$in' => $photo_ids,
                ],
                "is_deleted" => [
                    '$ne' => 1,
                ],
            ],
        ]);

        $photosdFiltered = [];
        foreach ($photos as $photo){
            $photosdFiltered[] =[
                'id' => (string) $photo->_id,
                'avatar' => Files::getAvatar($photo, "medium"),
                'url' =>  Files::getFileUrl($photo),
            ];
        }
        return $photosdFiltered;
    }



}
