<?php
namespace Controllers;

use Custom\Models\Users;
use Custom\Models\Damage;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Files;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id = (string) trim($req['user_id']);
        $vehicle_id = (string) trim($req['vehicle_id']);

        if ($permissions['vehicle_damages_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
                "parent_type" => Damage::TYPE_VEHICLE,
            ];

            if (strlen($user_id) > 0) {
                $binds['user_id'] = (string) $user_id;
            }

            if (strlen($vehicle_id) > 0) {
                $binds['parent_id'] = (string) $vehicle_id;
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

            $query = Damage::find([
                $binds,
                "sort" => $sort,
            ]);

            $userIds   = [];
            $usersById = [];

            foreach ($query as $row) {
                if (in_array("all", $permissions['vehicle_damages_view']['selected']) )
                    $userIds[] = (string) $row->creator_id;
                elseif (in_array("self", $permissions['vehicle_damages_view']['selected']) && (string)Auth::getData()->_id == (string) $row->creator_id)
                    $userIds[] = (string) $row->creator_id;
            }

            $userList = Users::find([
                [
                    "id" => [
                        '$in' => $userIds,
                    ],
                ],
            ]);

            foreach ($userList as $row) {
                $usersById[$row->id] = [
                    "id"       => $row->id,
                    "fullname" => $row->fullname,
                ];
            }

//echo "/////////////////////////////////// <br>";
            $vehiclesById = Vehicles::listById($query, 'parent_id', function ($ids) {
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

//            if(!$user_id && !$vehicle_id){
//                $users = Users::find([
//                    [
//                        "id"         => [
//                            '$nin' => $userIds,
//                        ],
//                        "type"       => "employee",
//                        "is_deleted" => [
//                            '$ne' => 1,
//                        ],
//                    ],
//                ]);
//
//                foreach ($users as $row) {
//                    $items[] = [
//                        'id'         => (string) $row->_id,
//                        'user'       => [
//                            "id"       => $row->id,
//                            "fullname" => $row->fullname,
//                        ],
//                        'vehicle'    => [
//                            "id"    => null,
//                            "title" => null,
//                        ],
//                        'created_at' => "----:--:-- --:--",
//                    ];
//                }
//            }

            foreach ($query as $value) {


                $vehicle = $vehiclesById[$value->parent_id];
                $user    = $usersById[$value->user_id];

                $items[] = [
                    'id'         => (string) $value->_id,
                    'user'       => ($user ? [
                        "id"       => $user["id"],
                        "fullname" => $user["fullname"],
                    ] : [
                        "id"       => 0,
                        "fullname" => Lang::get("Deleted"),
                    ]),
                    'vehicle'    => ($vehicle ? [
                        "id"    => (string) $vehicle->_id,
                        "number" => (string) $vehicle->number,
                        "photos" => Damage::getPhotoList($value->photo_ids),
                    ] : [
                        "id"    => null,
                        "title" => Lang::get("Deleted"),
                    ]),
                    'created_at' => Damage::dateConvertTimeZone($value->created_at, "Y-m-d H:i"),
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
