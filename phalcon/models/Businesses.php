<?php
namespace Models;

use Lib\ApiDB;
use Models\Files;

class Businesses extends ApiDB
{
    public static function getSource()
    {
        return "businesses";
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

    public static function getAvatar($data)
    {
        if($data->avatar_id)
        {
            $file = Files::findById($data->avatar_id);
            return [
                "small" => FILE_URL."/upload/".(string)$file->uuid."/".(string)$data->avatar_id."/small.jpg",
                "large" => FILE_URL."/upload/".(string)$file->uuid."/".(string)$data->avatar_id."/medium.jpg",
                "org" => FILE_URL."/upload/".(string)$file->uuid."/".(string)$data->avatar_id."/org.jpg",
            ];
        }
        else
        {
            return [
                "small" => FILE_URL."/resources/images/noavatar.jpg",
                "large" => FILE_URL."/resources/images/noavatar.jpg",
                "org"   => ""
            ];
        }
    }
}