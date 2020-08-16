<?php
namespace Custom\Models;

use Custom\Models\Files;
use Lib\ApiDB;

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
                "id" => (int) $id,
            ],
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

    public function getAvatar($data)
    {
        if ($data->avatar_id) {
            $file = Files::findById($data->avatar_id);
            return [
                "small" => FILE_URL . "/upload/" . (string) $file->uuid . "/" . (string) $data->avatar_id . "/small.jpg",
                "large" => FILE_URL . "/upload/" . (string) $file->uuid . "/" . (string) $data->avatar_id . "/medium.jpg",
                "org"   => FILE_URL . "/upload/" . (string) $file->uuid . "/" . (string) $data->avatar_id . "/org.jpg",
            ];
        } else {
            return [
                "small" => FILE_URL . "/resources/images/noavatar.jpg",
                "large" => FILE_URL . "/resources/images/noavatar.jpg",
                "org"   => "",
            ];
        }
    }
}
