<?php


namespace Controllers;


use Custom\Models\Pallet;
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
        $skip = (int)Req::get("skip");
        $limit = (int)Req::get("limit");
        if ($limit == 0)
            $limit = 50;
        if ($limit > 200)
            $limit = 200;
        if ($permissions['palettes_view']['allow']) {
            $binds = [
                "is_deleted" => 0,
            ];


//            if (!in_array("all", $permissions['palettes_view']['selected']) && in_array("self", $permissions['palettes_view']['selected']))
//                $binds ['employee_id'] = (string)Auth::getData()->_id;

            $query = Pallet::find([
                $binds,
                "skip" => $skip,
                "limit" => $limit,
                "sort" => [
                    "_id" => 1
                ]
            ]);
              $dataForCount = Pallet::count([
                $binds
            ]);
            $data = [];

            if (count($query) > 0) {

                $userIds = [];
                $usersById = [];

                foreach ($query as $row) {
                    $userIds[] = Users::objectId($row->employee_id);
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
                        "id" => (string)$row->_id,
                        "fullname" => $row->fullname,
                    ];
                }


                foreach ($query as $value) {
                    $user = $usersById[$value->employee_id];
                    $data[] = [
                        'id' => (string)$value->_id,
                        'user' => ($user ? [
                            "id" => $user["id"],
                            "fullname" => $user["fullname"],
                        ] : [
                            "id" => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'quarter_pallet' => $value->quarter_pallet,
                        'half_pallet' => $value->half_pallet,
                        'full_pallet' => $value->full_pallet,
                        'created_at' => TimeZones::date($value->created_at, "Y-m-d H:i:s")
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data" => $data,
                    "count" => $dataForCount
                );
            } else {
                $error = Lang::get("InfoNotFound", "Info not found");
            }
        } else {
            $error = Lang::get("PermissionsDenied");
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