<?php

namespace Controllers;

use Custom\Models\Parameters;
use Custom\Models\TimeRecords;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Lib\TimeZones;

class ListController extends \Phalcon\Mvc\Controller
{
    public function sec_to_time($seconds)
    {
        $hour = floor($seconds / 3600);
        $minute = floor($seconds % 3600 / 60);

        $str = "";
        if ($hour > 0) {
            $str .= str_replace("{hour}", $hour, Lang::get("HourShort"));
        }
        if ($minute > 0) {
            $str .= " " . str_replace("{minute}", $minute, Lang::get("MinuteShort"));
        }
        return $str;
    }

    public function get_working_days($startTime, $endTime, $ignore = [])
    {
        $result = [];

        if (86400 > $endTime - $startTime) {
            return $result;
        }

        $i = (int)$startTime;
        while ($i <= (int)$endTime) {
            $w = (int)date("w", $i);
            if (!in_array($w, $ignore)) {
                $result[] = date("Y-m-d", $i);
            }
            $i += 86400;
        }
        return $result;
    }

    public function get_full_months($startTime, $endTime)
    {
        $result = [];
        $months = [];

        $i = (int)$startTime;
        while ($i <= (int)$endTime) {
            $months[strtotime(date("Y-m", $i))][] = $i;
            $i += 86400;
        }

        foreach ($months as $time => $values) {
            if ($values[0] == "01" && end($values) == strtotime('last day of this month', $time)) {
                $result[] = date("Y-m-d", $time);
            }
        }

        return $months;
    }

    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();

        $stat = (string)trim($req['stat']);
        $employee_id = (int)trim($req['employee_id']);
        $start_date = (string)trim($req['start_date']);
        $end_date = (string)trim($req['end_date']);

        if ($permissions['statistics_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            $currency = Parameters::getById(Auth::$business->data->currency);

            if ($stat == "timerecords") {
                $data = [
                    "user" => false,
                    "currency" => $currency ? [
                        "rate" => $currency->currency_rate,
                        "slug" => $currency->currency_slug,
                        "symbol" => $currency->currency_symbol,
                    ] : [
                        "rate" => 0,
                        "slug" => "none",
                        "symbol" => "none",
                    ],
                    "statistics_per_days" => [],
                    "statistics_by_categories" => [],
                    "total_work_days" => 0,
                    "total_worked_days" => 0,
                    "total_worked_hours" => "00:00",
                    "total_employee_monthly_salary" => 0,
                    "total_employee_hourly_salary" => 0,
                ];

                $total_worked_days = 0;
                $total_worked_hours = 0;
                $statistics_by_categories = [];

                if (strlen($start_date) > 8 && strlen($end_date) > 8 && Lib::isValidDate($start_date, "Y-m-d H:i") && Lib::isValidDate($end_date, "Y-m-d H:i")) {

                    $daily_work_hour = Auth::$business->data->daily_work_hour;
                    $workingDates = $this->get_working_days(strtotime(date($start_date)), strtotime(date($end_date)), [6, 0]);
                    $total_work_days = count($workingDates);

                    $binds['start_date']['$gte'] = TimeZones::date(strtotime($start_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);
                    $binds['end_date']['$lte'] = TimeZones::date(strtotime($end_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);

                    if ($employee_id > 0) {
                        $binds['employee_id'] = (int)$employee_id;

                        $user = Users::getById($employee_id);

                        if ($user) {
                            $data["user"] = [
                                "id" => $user->id,
                                "fullname" => $user->fullname,
                                "salary" => [
                                    "monthly" => Lib::nice_number_format($user->salary->monthly),
                                    "hourly" => Lib::nice_number_format($user->salary->hourly),
                                ],
                            ];
                        }
                    }

                    $records = TimeRecords::find([
                        $binds,
                    ]);

                    $categoriesById = Parameters::listById($records, 'category_id', function ($ids) {
                        return [
                            'col' => '_id',
                            'rows' => Parameters::find([
                                [
                                    '_id' => [
                                        '$in' => array_map(function ($id) {
                                            return Parameters::objectId($id);
                                        }, $ids),
                                    ],
                                ],
                            ]),
                        ];
                    });

                    $employeesById = Users::listById($records, 'employee_id', function ($ids) {
                        return [
                            'col' => 'id',
                            'rows' => Users::find([
                                [
                                    'id' => [
                                        '$in' => $ids,
                                    ],
                                ],
                            ]),
                        ];
                    });

                    foreach ($records as $i => $row) {
                        $startTime = strtotime(TimeZones::date($row->start_date, "Y-m-d H:i"));
                        $endTime = strtotime(TimeZones::date($row->end_date, "Y-m-d H:i"));

                        $workTime = $endTime - $startTime;
                        $duration = $endTime > $startTime ? $this->sec_to_time($workTime) : 0;
                        $date = date("Y-m-d", $startTime);

                        $category = $categoriesById[$row->category_id];

                        $statistics_by_categories[$row->category_id]["title"] = Parameters::getTitleByLang($category, Lang::getLang());
                        $statistics_by_categories[$row->category_id]["id"] = $row->category_id;
                        $statistics_by_categories[$row->category_id]["duration"] += $workTime;

                        if (in_array(date("Y-m-d", $startTime), $workingDates)) {
                            $total_worked_days += 1;
                        }

                        $total_worked_hours += $workTime;

                        $data["statistics_per_days"][$i] = [
                            "date" => $date,
                            "duration" => $duration,
                            "start_time" => date("H:i", $startTime),
                            "end_time" => date("H:i", $endTime),
                            "percent" => ($duration >= $daily_work_hour ? 100 : ($duration == 0 ? 0 : round($duration / $daily_work_hour * 100, 0))),
                            "type" => [
                                "title" => Parameters::getTitleByLang($category, Lang::getLang()),
                                "key" => $category->work_type,
                            ],
                        ];

                        if (!$employee_id) {
                            $user = $employeesById[$row->employee_id];
                            $data["statistics_per_days"][$i]["user"] = [
                                "id" => 0,
                                "fullname" => Lang::get("Deleted"),
                                "salary" => [
                                    "monthly" => 0,
                                    "hourly" => 0,
                                ],
                            ];
                            if ($user) {
                                $data["statistics_per_days"][$i]["user"] = [
                                    "id" => $user->id,
                                    "fullname" => $user->fullname,
                                    "salary" => [
                                        "monthly" => Lib::nice_number_format($user->salary->monthly),
                                        "hourly" => Lib::nice_number_format($user->salary->hourly),
                                    ],
                                ];
                            }
                        }
                    }

                    if ($employee_id > 0) {
                        $data["total_employee_monthly_salary"] = Lib::nice_number_format(round(($user->salary->monthly / $total_work_days) * $total_worked_days, 2));
                        $data["total_employee_hourly_salary"] = Lib::nice_number_format(round($user->salary->hourly * ($total_worked_hours / 3600), 2));
                    }

                    $data["total_work_days"] = $total_work_days;
                    $data["total_worked_days"] = $total_worked_days;

                    $hour = floor($total_worked_hours / 3600);
                    $minute = floor($total_worked_hours % 3600 / 60);
                    $data["total_worked_hours"] = sprintf("%d:%02d", $hour, $minute);

                    foreach ($statistics_by_categories as $row) {
                        $h = floor($row["duration"] / 3600);
                        $m = floor($row["duration"] % 3600 / 60);

                        $data["statistics_by_categories"][] = [
                            "title" => $row["title"],
                            "id" => $row["id"],
                            "duration" => $this->sec_to_time($row["duration"]),
                            //"duration" => sprintf("%d:%02d", $h, $m),
                        ];
                    }

                } elseif ($stat == "deliveries") {

                }
            }

            if ($data) {
                $response = array(
                    "status" => "success",
                    "data" => $data,
                );
            } else {
                $error = Lang::get("noInformation", "No information found");
            }

        } else {
            $error = Lang::get("PageNotAllowed");
        }

        if ($error) {
            $response = array(
                "status" => "error",
                "error_code" => 1023,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
