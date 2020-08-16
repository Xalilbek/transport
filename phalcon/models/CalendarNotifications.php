<?php
namespace Models;

use Lib\ApiDB;

class CalendarNotifications extends ApiDB
{
    public static function getSource(){
        return "calendar_notifications";
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

    public static function getNotifications($calendar, $calendarNotification, $user_id)
    {
        if($calendarNotification && $calendarNotification->notifications){
            return $calendarNotification->notifications;
        } elseif ($calendar && $calendar->notifications) {
            return $calendar->notifications;
        }

        return [];
    }

    public static function filterData($notifications)
    {
        $calendarNotifications = [];
        foreach ($notifications as $notification) {
            if (count($calendarNotifications) < 5 && in_array($notification["type"], ["email", "push"]) && in_array($notification["before"]["type"], ["minute", "hour", "day", "week"]) && is_numeric($notification["before"]["value"])) {
                $calendarNotifications[] = [
                    "type"   => (string) $notification["type"],
                    "before" => [
                        "type"  => (string) $notification["before"]["type"],
                        "value" => (int) $notification["before"]["value"],
                    ],
                ];
            }
        }
        return $calendarNotifications;
    }
}