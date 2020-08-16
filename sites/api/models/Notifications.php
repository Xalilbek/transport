<?php
namespace Custom\Models;

use Lib\MainDB;
use Lib\TimeZones;

class Notifications extends MainDB
{
    public static function getSource(){
        return "notifications";
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



    public static function filterData( $data, $alertTypes=false, $object=false)
    {
        $alert = [
            "id" => (string)$data->_id,
            "type"              => [
                "value"      => (int)$data->alert_type,
                "text"       => $alertTypes[$data->alert_type]["title"],
            ],
            "object"            => ($object) ? [
                "id"        => (string)$data->object_id,
                "title"     => (string)$object->title
            ]: false,
            "speed"             => (int)$data->speed,
            "text"              => "Suret heddi: ".(int)$data->speed,
            "date"              => TimeZones::date($data->created_at, "d/m/Y H:i:s"),
        ];

        return $alert;
    }
}