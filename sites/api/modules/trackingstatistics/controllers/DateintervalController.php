<?php

namespace Controllers;

use Custom\Models\Objects;
use Custom\Models\TrackingStatistics;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Lib\TimeZones;

class DateintervalController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

        $permissions = Auth::getPermissions();

        $error = false;
        foreach (Req::get("ids") as $value)
            $ids[] = (string)$value;
        $dateFrom = strtotime(substr(Req::get("datefrom"), 0, 10));
        $dateTo = strtotime(substr(Req::get("dateto"), 0, 10));
        $objects = [];
        $objIds = [];
        foreach ($ids as $id) {
            $objIds[] = Objects::objectId($id);
        }


        //check Permission start
        $allow = false;


        $binds = [
            "_id" => [
                '$in' => $objIds
            ],
            "is_deleted" => 0,

        ];
        if ($permissions['tracking_statistics_view']['allow']) {
            $allow = true;
        } else if (in_array("all", $permissions['tracking_statistics_view']['selected'])) {
            $allow = true;
        } elseif (in_array("self", $permissions['tracking_statistics_view']['selected'])) {
            $binds["users"] = (string)Auth::getData()->_id;
            $allow = true;
        } else {
            $allow = false;
        }

        //check Permission end


        if (count($objIds) > 0)
            $objects = Objects::find([
                $binds
            ]);

        $ids = [];

        $objectsData = [];
        foreach ($objects as $value) {
            $ids[] = (string)$value->_id;
            $objectsData[(string)$value->_id] = $value;
        }
        if (!$allow) {
            $error = Lang::get("PageNotAllowed");
        } else if (!$dateFrom || !$dateTo) {
            $error = Lang::get("dateIntervalWrong", "Date interval is wrong");
        } elseif (count($ids) > 0) {

            $binds = [
                "object_id" => [
                    '$in' => $ids
                ],
                "datetime" => [
                    '$gte' => TimeZones::date(strtotime($dateFrom), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]),
                    '$lte' => TimeZones::date(strtotime($dateTo), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]),
                ]
            ];

            $statQuery = TrackingStatistics::find([
                $binds,
            ]);
            $durations = [];
            $distances = [];
            $total = [];

            foreach ($statQuery as $value) {
                $durations[$value->object_id][$value->date] = $value->duration;
                $distances[$value->object_id][$value->date] = $value->distance;
                $total[$value->object_id]["duration"] += $value->duration;
                $total[$value->object_id]["distance"] += $value->distance;

            }


            $data = [];
            foreach ($objects as $value) {
                $dates = [];
                for ($i = $dateFrom; $i < $dateTo + 10; $i = $i + 24 * 3600) {
                    $date = date("Y-m-d", $i);
                    $dates[$date] = [
                        "date" => $date,
                        "duration" => Lib::durationToStr((string)$durations[(string)$value->_id][$date], $this->lang),
                        "distance" => round((string)$distances[(string)$value->_id][$date] / 1000, 2) . " km",
                    ];

                }

                $data[] = [
                    "id" => $value->_id,
                    "title" => $value->title,
                    "imei" => $value->imei,
                    "icon" => $value->icon,
                    "total" => [
                        "duration" => Lib::durationToStr($total[$value->_id]["duration"], $this->lang),
                        "distance" => round($total[$value->_id]["distance"] / 1000, 2) . " km",
                    ],
                    "data" => $dates
                ];
            }


            $response = [
                "status" => "success",
                "data" => $data,
            ];
        } else {
            $error = Lang::get("uDontHaveObj", "Object not found");
        }


        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1023,
                "description" => $error,
            ];
        }
        echo json_encode($response, true);
        exit();

    }
}