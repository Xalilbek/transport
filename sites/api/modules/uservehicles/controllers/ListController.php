<?php
namespace Controllers;

use Custom\Models\Users;
use Custom\Models\UserVehicles;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id = (string) trim($req['user_id']);
        $vehicle_id = (string) trim($req['vehicle_id']);
        $type        = (int) trim($req['type']);
        $number      = (string) trim($req['number']);
        $status      = (int) trim($req['status']);

        if ($permissions['vehicles_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if (strlen($user_id) > 0) {
                $binds['user_id'] = (string) $user_id;
            }

            if (strlen($vehicle_id) > 0) {
                $binds['vehicle_id'] = (string) $vehicle_id;
            }

            if (in_array($status, [1, 2, 3])) {
                $binds['status'] = (int) $status;
            }

            if (in_array($type, UserVehicles::typeListByKey(Lang::getLang())[$type])) {
                $binds['type'] = (int) $type;
            }

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['user_id', 'number', 'type', 'status', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req['skip'];
            $limit = (int) $req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = UserVehicles::find([
                $binds,
                "sort" => $sort,
            ]);

            $userIds   = [];
            $usersById = [];
            foreach ($query as $row) {
                $userIds[] = Users::objectId( $row->user_id);
            }

            $userList = Users::find([
                [
                    "_id" => [
                        '$in' => $userIds,
                    ],
                ],
            ]);
            foreach ($userList as $row) {
                $usersById[(string)$row->_id] = [
                    "id"       => (string)$row->_id,
                    "fullname" => $row->fullname,
                ];
            }

            $vehiclesById = Vehicles::listById($query, 'vehicle_id', function ($ids) {
                return [
                    "col"  => "_id",
                    "rows" => Vehicles::find([
                        [
                            "_id" => [
                                '$in' => array_map(function ($id) {
                                    return Vehicles::objectId($id);
                                }, $ids),
                            ],
                        ],
                    ]),
                ];
            });

            $items = [];

            if(!$user_id && !$vehicle_id){
                $users = Users::find([
                    [
                        "_id"         => [
                            '$nin' => $userIds,
                        ],
                        "type"       => "employee",
                        "is_deleted" => [
                            '$ne' => 1,
                        ],
                    ],
                ]);

                foreach ($users as $row) {
                    $items[] = [
                        'id'         => (string) $row->_id,
                        'user'       => [
                            "id"       => (string)$row->_id,
                            "fullname" => $row->fullname,
                        ],
                        'vehicle'    => [
                            "id"    => null,
                            "title" => null,
                        ],
                        'created_at' => "----:--:-- --:--",
                    ];
                }
            }

            foreach ($query as $value) {

                $vehicle = $vehiclesById[$value->vehicle_id];
                $user    = $usersById[$value->user_id];

                $items[] = [
                    'id'         => (string) $value->_id,
                    'status'     => $value->status,
                    'user'       => ($user ? [
                        "id"       => (string)$user["_id"],
                        "fullname" => $user["fullname"],
                    ] : [
                        "id"       => 0,
                        "fullname" => Lang::get("Deleted"),
                    ]),
                    'vehicle'    => ($vehicle ? [
                        "id"    => (string) $vehicle->_id,
                        "title" => (string) $vehicle->title,
                    ] : [
                        "id"    => null,
                        "title" => Lang::get("Deleted"),
                    ]),
                    'created_at' => UserVehicles::dateFormat($value->created_at, "Y-m-d H:i"),
                ];
            }

            $data = array_slice($items, $skip, $limit);

            $response = array(
                "status" => "success",
                "data"   => $data,
                "count"  => count($items),
                "skip"   => $skip,
                "limit"  => $limit,
            );

        } else {
            $error = Lang::get("PermissionsDenied");
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
