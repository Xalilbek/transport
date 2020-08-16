<?php

namespace Controllers;

use Custom\Models\Parameters;
use Custom\Models\TimeRecords;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\MainDB;
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

    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();

        $employee_id = (string)trim($req['employee_id']);
        $start_date = (string)trim($req['start_date']);
        $end_date = (string)trim($req['end_date']);
        $status = (int)trim($req['status']);

        if ($permissions['timerecords_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if (strlen($start_date) > 1) {
                $binds['start_date']['$gte'] = TimeZones::date(strtotime($start_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);
            }

            if (strlen($end_date) > 1) {
                $binds['end_date']['$lte'] = TimeZones::date(strtotime($end_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);
            }

            if (strlen($employee_id) > 0) {
                $binds['employee_id'] = (string)$employee_id;
            }

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['id', 'employee_id', 'start_date', 'start_end', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip = (int)$req['skip'];
            $limit = (int)$req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = TimeRecords::find([
                $binds,
                "skip" => $skip,
                "limit" => $limit,
                "sort" => $sort,
            ]);

            $count = TimeRecords::count([
                $binds,
            ]);

            $employeesById = Users::listById($query, 'employee_id', function ($ids) {
                return [
                    "col" => "_id",
                    "rows" => Users::find([
                        [
                            "_id" => [
                                '$in' => array_map(function ($id) {
                                    return Users::objectId($id);
                                }, $ids),
                            ],
                        ],
                    ]),
                ];
            });

            $categoriesById = MainDB::listById($query, 'category_id', function ($ids) {
                return [
                    "col" => "_id",
                    "rows" => Parameters::find([
                        [
                            "_id" => [
                                '$in' => array_map(function ($v) {
                                    return Parameters::objectId($v);
                                }, $ids),
                            ],
                        ],
                    ]),
                ];
            });

            $total_worked_time = 0;

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $employee = $employeesById[$value->employee_id];
                    $category = $categoriesById[$value->category_id];

                    $start_time = TimeRecords::toSeconds($value->start_date);
                    $end_time = TimeRecords::toSeconds($value->end_date);

                    $worked_time = 0;
                    if ($end_time) {
                        $worked_time = $end_time - $start_time;
                    } else {
                        $worked_time = time() - $start_time;
                    }

                    $total_worked_time += $worked_time;

                    $data[] = [
                        'id' => (string)$value->_id,
                        'start_date' => $value->start_date ? TimeZones::date($value->start_date, "Y-m-d H:i") : "----:--:-- --:--",
                        'end_date' => $value->end_date ? TimeZones::date($value->end_date, "Y-m-d H:i") : "----:--:-- --:--",
                        'category_id' => (string)$value->category_id,
                        'employee' => ($employee ? [
                            "id" => (string)$employee->_id,
                            "fullname" => $employee->fullname,
                        ] : [
                            "id" => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'category' => ($category ? [
                            "id" => (string)$category->_id,
                            "title" => Parameters::getTitleByLang($category, Lang::getLang()),
                        ] : [
                            "id" => 0,
                            "title" => "null",
                        ]),
                        'start_location' => $value->start_location ? $value->start_location : '',
                        'end_location' => $value->end_location ? $value->end_location : '',
                        'worked_time' => $this->sec_to_time($worked_time),
                        'created_at' => TimeRecords::dateConvertTimeZone($value->created_at, "Y-m-d H:i"),
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data" => $data,
                    "total_worked_time" => $this->sec_to_time($total_worked_time),
                    "count" => $count,
                    "skip" => $skip,
                    "limit" => $limit,
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
