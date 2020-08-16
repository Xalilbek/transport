<?php

namespace Controllers;

use Custom\Models\TimeRecords;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Lib\TimeZones;

class ToggleController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;
        $req = (array)Req::get();
        $latitude = (float)$req["latitude"];
        $longitude = (float)$req["longitude"];
        $category_id = (string)$req["category_id"];
        $employee_id = (string)Auth::getData()->_id;

//        if ($latitude == 0 || $longitude == 0) {
//            $error = Lang::get("CoordinatesIncorrect", "Coordinates are incorrect");
//        } else {
        $binds = [
            "employee_id" => (string)$employee_id,
            "is_deleted" => [
                '$ne' => 1,
            ],
        ];
        $data = TimeRecords::findFirst([
            $binds,
            "sort" => [
                "_id" => -1,
            ],
        ]);

        $address = Lib::getAddress($latitude, $longitude);
        $work_hours_for_week = Auth::getData()->work_hours_for_week;

        $thisweek = date("w") == 0 ? 7 : date("w");
        if ($work_hours_for_week->{$thisweek}) {

            $interval_today = [
                "from" => strtotime(date("Y-m-d 00:00:00")),
                "to" => strtotime(date("Y-m-d 23:59:59"))
            ];
            $interval_this_hour = [
                "from" => strtotime(date("Y-m-d H:00:00")),
                "to" => strtotime(date("Y-m-d H:59:59")),
            ];

            $intervals = [];

            foreach ($work_hours_for_week->{$thisweek} as $value) {
                $from = strtotime(date("Y-m-d {$value->from->hour}:{$value->from->minute}:00"));
                $to = strtotime(date("Y-m-d {$value->to->hour}:{$value->to->minute}:00"));

                if ($value->from->hour == date("H")) {
                    $interval_this_hour = [
                        "from" => $from,
                        "to" => $to,
                    ];
                }

                $intervals[] = [
                    "from" => $from,
                    "to" => $to,
                ];
            }

            usort($intervals, function ($a, $b) {
                return $a['from'] - $b['from'];
            });
        }

        $time = time();
        if (!$data || ($data && $data->end_date)) {
            if ($intervals && ($time < $intervals[0]["from"] || $time > end($intervals)["to"])) {
                $error = Lang::get("WorkHourError", "You can not start to work now. You are out of work hours");
            } else {
                $insert = [
                    "id" => (int)TimeRecords::getNewId(),
                    "start_date" => TimeRecords::getDate($time),
                    "start_location" => [
                        "name" => $address ? $address["name"] : $latitude . "-" . $longitude,
                        "geometry" => [
                            "type" => "Point",
                            "coordinates" => [
                                $longitude, $latitude,
                            ],
                        ],
                    ],
                    "end_date" => null,
                    "employee_id" => (string)$employee_id,
                    "status" => 0,
                    "is_deleted" => 0,
                    "created_at" => TimeRecords::getDate(),
                ];

                $insert_id = TimeRecords::insert($insert);

                $response = [
                    "status" => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                    "data" => [
                        "id" => (string)$insert_id,
                        "start_date" => date("Y-m-d H:i:s", $time),
                        "start_unixtime" => $time,
                        "end_date" => false,
                        "end_unixtime" => false,
                        "elapse_seconds" => 0,
                    ],
                ];
            }
        } else {
            if ($intervals && $time < end($intervals)["to"]) {
                $time = end($intervals)["to"];
            }
            $elapse = $time - TimeRecords::toSeconds($data->start_date);
            $elapseMinutes = $elapse / 60;

            $update = [
                "end_date" => TimeRecords::getDate($time),
                "end_location" => [
                    "name" => $address ? $address["name"] : $latitude . "-" . $longitude,
                    "geometry" => [
                        "type" => "Point",
                        "coordinates" => [
                            $longitude, $latitude,
                        ],
                    ],
                ],
                "elapse" => $elapseMinutes,
                "category_id" => (string)$category_id,
                "update_at" => TimeRecords::getDate(),
            ];

            TimeRecords::update(
                [
                    "_id" => $data->_id,
                ],
                $update
            );

            $response = [
                "status" => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                "data" => [
                    "id" => (string)$data->_id,
                    "start_date" => TimeZones::date($data->start_date, "Y-m-d H:i:s"),
                    "start_unixtime" => TimeZones::date($data->start_date, "unix"),
                    "end_date" => date("Y-m-d H:i:s", $time),
                    "end_unixtime" => $time,
                    "elapse_seconds" => $time - TimeRecords::toSeconds($data->start_date),
                ],
            ];
        }
//        }

        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object)$response);
        exit;
    }
}
