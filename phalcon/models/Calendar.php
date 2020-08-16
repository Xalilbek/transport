<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class Calendar extends ApiDB
{
    public static function getSource()
    {
        return "calendar";
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
            ["sort" => ["id" => -1]],
        ]);
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }

    public static function colorList()
    {
        return [
            0 => [
                'id'    => 1,
                'color' => '#5e72e4',
            ],
            1 => [
                'id'    => 2,
                'color' => '#2dce89',
            ],
            2 => [
                'id'    => 3,
                'color' => '#fb6340',
            ],
            3 => [
                'id'    => 4,
                'color' => '#039be5',
            ],
            4 => [
                'id'    => 5,
                'color' => '#f5365c',
            ],
            5 => [
                'id'    => 6,
                'color' => '#8898aa',
            ],
            6 => [
                'id'    => 7,
                'color' => '#8965e0',
            ],
            7 => [
                'id'    => 8,
                'color' => '#ffd600',
            ],
        ];
    }
    public static function colorListByKey()
    {
        $list = [];
        foreach (self::colorList() as $row) {
            $list[$row['id']] = $row['color'];
        }
        return $list;
    }

    public static function permissionList($lang = false)
    {
        return [
            "set_permission"   => [
                "value"   => "set_permission",
                "title"   => ($lang) ? Lang::get("CanSetPermission", "Can set permission") : "",
                "default" => false,
                "warning" => ($lang) ? Lang::get("WarningPermission", "This permission can change your permission too") : false,
            ],
            "set_notification" => [
                "value"   => "set_notification",
                "title"   => ($lang) ? Lang::get("CanSetNotification", "Can set notification") : "",
                "default" => false,
            ],
            "add_user"         => [
                "value"   => "add_user",
                "title"   => ($lang) ? Lang::get("CanAddUser", "Can add user") : "",
                "default" => true,
            ],
            "remove_user"      => [
                "value"   => "remove_user",
                "title"   => ($lang) ? Lang::get("CanRemoveUser", "Can remove user") : "",
                "default" => false,
            ],
            "edit"             => [
                "value"   => "edit",
                "title"   => ($lang) ? Lang::get("CanEdit", "Can edit") : "",
                "default" => false,
            ],
            "delete"           => [
                "value"   => "delete",
                "title"   => ($lang) ? Lang::get("CanDelete", "Can delete") : "",
                "default" => false,
            ],
        ];
    }

    public static function getDefaultNofitications()
    {
        return [
            [
                "type"   => "",
                "before" => [
                    "type"  => "",
                    "value" => "",
                ],
            ],
        ];
    }

    public static function repetitionOfDate($repeat, $startTime, $endTime, $timeOffset, $repeatEndTime)
    {
        $data = [];
        $days = [];
        $months = $monthsByKey = [];

        $repeatEndTime = $repeatEndTime + 11 * 86400;

        $s = $startTime;
        while ($s <= $endTime) {
            $days[] = (string) date("d", $s);
            $monthsByKey[(string) date("m", $s)][] = (string) date("d", $s);
            $s += 86400;
        }


        $countDays   = count($days);
        $repeatCount = 0;

        if ($repeat->active == 1) {
            switch ($repeat->type) {
                case "day":
                    $i = $startTime;
                    $c = 0;
                    while ($i <= $repeatEndTime) {
                        if ($repeat->every > 0 && !($c % $repeat->every)) {

                            if ($repeat->end->type == "after") {
                                if ($repeatCount > $repeat->end->value) {
                                    break;
                                }
                            }
                            $repeatCount += 1;

                            $s   = $i;
                            $max = $i + ($countDays * 86400);
                            if ($repeat->end->type == "date") {
                                if (strtotime(date($repeat->end->value)) + 86400 < $max) {
                                    $max = strtotime(date($repeat->end->value)) + 86400;
                                }
                            }

                            while ($s < $max) {
                                $data[$i] = $s;
                                $s += 86400;
                            }
                        }
                        $i += 86400;
                        $c += 1;
                    }
                    break;

                case "week":
                    if ($repeat->every > 0 && count($repeat->days) > 0) {
                        $weekTime = strtotime('monday this week', $startTime);
                        $i        = $weekTime;

                        $c = 0;
                        $s = 0;
                        while ($i <= $repeatEndTime) {

                            if (!(round(($i - $weekTime) / 604800, 0) % ($repeat->every))) {

                                if ($repeat->end->type == "after") {
                                    if ($repeatCount > $repeat->end->value) {
                                        break;
                                    }
                                }
                                $repeatCount += 1;

                                if (!$s) {
                                    $s   = $k   = $startTime;
                                    $max = strtotime('monday next week', $startTime);
                                } else {
                                    $s   = $k   = $i;
                                    $max = $i + 604800;
                                }

                                if ($repeat->end->type == "date") {
                                    if (strtotime(date($repeat->end->value)) + 86400 < $max) {
                                        $max = strtotime(date($repeat->end->value)) + 86400;
                                    }
                                }

                                while ($s < $max) {
                                    $w = date("w", $s);
                                    if ($w == 0) {
                                        $w = 7;
                                    }
                                    if (in_array($w, $repeat->days)) {
                                        $t = $s;
                                        while ($t <= $s + ($endTime - $startTime)) {
                                            $data[$s] = $t;
                                            $t += 86400;
                                        }
                                    }
                                    $s += 86400;
                                }

                            }
                            $i += 604800;
                            $c += 1;
                        }
                    } else {
                        $data = Calendar::getDatesOfInterval($startTime, $endTime);
                    }
                    break;

                case "month":
                    if ($repeat->every > 0) {
                        $i     = strtotime('first day of this month', $startTime);
                        $c = 0;
                        $s = 0;
                        while ($i <= $repeatEndTime) 
                        {
                            if (!($c % ($repeat->every))) {
                                if ($repeat->end->type == "after") {
                                    if ($repeatCount > $repeat->end->value) {
                                        break;
                                    }
                                }
                                $repeatCount += 1;

                                if (date("d", $i) == date("d", $startTime)) {
                                    
                                    $s   = $k = $i;
                                    $max = $i + $countDays * 86400;

                                    if ($repeat->end->type == "date") {
                                        if (strtotime(date($repeat->end->value)) + 86400 < $max) {
                                            $max = strtotime(date($repeat->end->value)) + 86400;
                                        }
                                    }

                                    while ($s < $max) {
                                        //$data[date("Y-m-d", $k)][] = date("Y-m-d", $s);
                                        $data[$k] = $s;
                                        $s += 86400;
                                    }
                                }
                            }
                            
                            $i += 86400;
                            if (date("d", $i) == "01") {
                                $c += 1;
                            }
                        }
                    } else {
                        $data = Calendar::getDatesOfInterval($startTime, $endTime);
                    }
                    break;

                case "year":
                    if ($repeat->every > 0) {
                        $years = [];
                        $i     = strtotime('first day of this month', $startTime);
                        $c = 0;
                        $s = 0;
                        while ($i <= $repeatEndTime) 
                        {
                            if (!($c % ($repeat->every))) {
                                if ($repeat->end->type == "after") {
                                    if ($repeatCount > $repeat->end->value) {
                                        break;
                                    }
                                }
                                $repeatCount += 1;

                                if (date("m", $i) == date("m", $startTime) && date("d", $i) == date("d", $startTime)) {
                                    
                                    $s   = $k = $i;
                                    $max = $i + $countDays * 86400;

                                    if ($repeat->end->type == "date") {
                                        if (strtotime(date($repeat->end->value)) + 86400 < $max) {
                                            $max = strtotime(date($repeat->end->value)) + 86400;
                                        }
                                    }

                                    while ($s < $max) {
                                        $data[$k] = $s;
                                        $s += 86400;
                                    }
                                }
                            }
                            
                            $i += 86400;
                            if (in_array(date("Y", $i), $years)) {
                                $years[] = date("Y", $i);
                                $c += 1;
                            }
                        }
                    } else {
                        $data = Calendar::getDatesOfInterval($startTime, $endTime);
                    }
                    break;
            }
        } else {
            $data = Calendar::getDatesOfInterval($startTime, $endTime);
        }

        return $data;
    }

    public static function getDatesOfInterval($startTime, $endTime)
    {
        $data = [];
        $i    = $startTime;
        while ($i <= $endTime) {
            $data[$startTime] = $i;
            $i += 86400;
        };
        return $data;
    }

    public static function getDaysOfMonths($startTime, $endTime) 
    {
        $data = [];
        $i    = $startTime;
        while ($i <= $endTime) {
            $data[date("m", $i)][] = date("d", $i);
            $i += 86400;
        };

        return $data;
    }
}
