<?php
namespace Models;

use Lib\ApiDB;

class Files extends ApiDB
{
    public static function getSource()
    {
        return "files";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int) $id,
            ],
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

    public static function getAvatar($data, $size = false)
    {
        if (in_array($data->type, ["jpg", "jpeg", "png", "gif"])) {
            $avatars = [];
            foreach ($data->avatars as $key => $value) {
                $avatars[$key] = 'https://' . $data->server . '/' . $value;
            }
            return $avatars[$size] ? $avatars[$size] : $avatars;
        }
    }

    public static function getFileUrl($data)
    {
        return 'https://' . $data->server . '/' . $data->file;
    }

    public static function copyTempFile($data, $merge = [])
    {
        $insert = array_merge((array)$data, (array)$merge);
        $id     = self::insert($insert);
        return (object) array_merge($insert, [
            "_id" => self::objectId($id),
        ]);
    }
}
