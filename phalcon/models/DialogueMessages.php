<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class DialogueMessages extends ApiDB
{
    public static function getSource(){
        return "dialogue_messages";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }


    public static function messageCommands( $message, $vars)
    {
        $data = false;
        switch($message->command)
        {
            case "driver_info":
                $data = [
                    "type"      => "forward",
                    "screen"    => "DriverInfo",
                    "params"    => [],
                    "message"   => self::getMsgText( $message->command),
                    "label"     => Lang::get("Edit")
                ];
                break;

            case "driver_photos":
                $data = [
                    "type"      => "forward",
                    "screen"    => "DriverPhotos",
                    "params"    => [],
                    "message"   => self::getMsgText( $message->command),
                    "label"     => Lang::get("Upload")
                ];
                break;

            case "car_info":
                $data = [
                    "type"      => "forward",
                    "screen"    => "CarDetails",
                    "params"    => [
                        "car"   => [
                            "id"        => (int)@MongoUsers::$data->car_id,
                            "number"    => "XX000XX"
                        ]
                    ],
                    "message"   => self::getMsgText( $message->command),
                    "label"     => Lang::get("Edit")
                ];
                break;

            case "car_photos":
                $data = [
                    "type"      => "forward",
                    "screen"    => "CarPhotos",
                    "params"    => [
                        "car"   => [
                            "id"        => (int)@MongoUsers::$data->car_id,
                            "number"    => "XX000XX"
                        ]
                    ],
                    "message"   => self::getMsgText( $message->command),
                    "label"     => Lang::get("Upload")
                ];
                break;

            case "link":
                $data = [
                    "type"      => "redirect",
                    "link"      => $message->link,
                    "message"   => (string)$message->body,
                    "label"     => Lang::get("View")
                ];
                break;

            case "account_activated":
                $data = [
                    "type"      => "forward",
                    "screen"    => "DriverInfo",
                    "params"    => [],
                    "message"   => self::getMsgText( $message->command),
                ];
                break;
        }

        return $data;
    }

    public static function getMsgText( $command)
    {
        $data = [
            "driver_info"           => Lang::get("fillDriverInfo", "Please, fill driver information to activate your account"),
            "driver_photos"         => Lang::get("uploadVerPhotos","Please, upload required photos for driver to activate your account"),
            "car_info"              => Lang::get("fillCarInfo", "Please, fill car information to activate your account"),
            "car_photos"            => Lang::get("uploadCarPhotos","Please, upload required photos for car to activate your account"),
            "account_activated"     => Lang::get("AccountActivated", "Congratulations! Your account was activated by moderator. You will get orders from riders around you"),
        ];

        return $data[$command];
    }
}