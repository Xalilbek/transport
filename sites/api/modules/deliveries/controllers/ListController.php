<?php
namespace Controllers;

use Custom\Models\Deliveries;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $employee_id = (string) trim($req['employee_id']);
        $start_date  = (string) trim($req['start_date']);
        $end_date    = (string) trim($req['end_date']);
        $status      = (int) trim($req['status']);

        if ($permissions['deliveries_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];
            if (strlen($start_date) > 1) {
                $binds['created_at']['$gte'] = TimeZones::date(strtotime($start_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);
            }

            if (strlen($end_date) > 1) {
                $binds['created_at']['$lte'] = TimeZones::date(strtotime($end_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]);
            }

            if (strlen($employee_id) > 0) {
                $binds['employee_id'] = (string) $employee_id;
            }

            if (in_array($status, [1, 2, 3])) {
                $binds['status'] = (int) $status;
            }

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['id', 'employee_id', 'number', 'weight', 'price', 'status', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req['skip'];
            $limit = (int) $req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = Deliveries::find([
                $binds,
                "skip"  => $skip,
                "limit" => $limit,
                "sort"  => $sort,
            ]);

            $count = Deliveries::count([
                $binds,
            ]);

            $employeesById = Deliveries::listById($query, 'employee_id', function ($ids) {
                return [
                    "col"  => "_id",
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

            $data = [];
            if (count($query) > 0) {

                foreach ($query as $value) {

                    $employee = $employeesById[$value->employee_id];
                    $data[]   = [
                        'id'         => (string) $value->_id,
                        'employee'   => ($employee ? [
                            "id"       => (string)$employee->_id,
                            "fullname" => $employee->fullname,
                        ] : [
                            "id"       => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'number'     => (string) $value->number,
                        //'weight'     => (float) $value->weight,
                        'price'      => (float) $value->price,
                        //'address'    => (string) $value->address,
                        'geometry'   => (array) $value->geometry,
                        'status'     => [
                            "value" => $value->status,
                            "text"  => Deliveries::statusListByKey($this->lang)[(string) $value->status],
                        ],
                        'created_at' => TimeZones::date($value->created_at, "Y-m-d H:i"),
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data"   => $data,
                    "count"  => $count,
                    "skip"   => $skip,
                    "limit"  => $limit,
                );
            } else {
                $error = Lang::get("noInformation", "No information found");
            }
        } else {
            $error = Lang::get("PageNotAllowed");
        }

        if ($error) {
            $response = array(
                "status"      => "error",
                "error_code"  => 1023,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
