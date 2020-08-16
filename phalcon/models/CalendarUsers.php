<?php
namespace Models;

use Lib\ApiDB;
use Models\Calendar;

class CalendarUsers extends ApiDB
{
    public static function getSource(){
        return "calendar_users";
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

    public static function getPermissions($calendar, $calendarUser, $user_id)
    {
        $permissions = [];
        $construct = Calendar::permissionList();

        foreach ($construct as $key => $value)
        {
            $isPermitted = $value["default"];
            if($calendarUser || $calendar)
            {
                if((int)$calendar->creator_id == (int)$user_id)
                {
                    $isPermitted = true;
                }
                else
                {
                    if(@$calendarUser && @$calendarUser->permissions && @$calendarUser->permissions->{$key} !== null){
                        $isPermitted = @$calendarUser->permissions->{$key};
                    }elseif(@$calendar && @$calendar->permissions && @$calendar->permissions->{$key} !== null)
                        $isPermitted = @$calendar->permissions->{$key};
                }
            }

            $permissions[$key] = $isPermitted;
        }
        return $permissions;
    }
}