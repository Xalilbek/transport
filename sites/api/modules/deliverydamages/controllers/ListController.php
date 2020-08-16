<?php

namespace Controllers;

use Custom\Models\Deliveries;
use Custom\Models\Users;
use Custom\Models\Damage;
use Custom\Models\deliverys;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;
use Models\Files;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();

        $user_id = (string)trim($req['user_id']);


        if ($permissions['delivery_damages_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
                "parent_type" => Damage::TYPE_DELIVERY,
            ];

            if ($user_id > 0) {
                $binds['user_id'] = (string)$user_id;
            }


            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['user_id', 'number', 'type', 'status', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip = (int)$req['skip'];
            $limit = (int)$req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = Damage::find([
                $binds,
                "sort" => $sort,
            ]);

            $userIds = [];
            $usersById = [];

            foreach ($query as $row) {
                if (in_array("all", $permissions['delivery_damages_view']['selected']))
                    $userIds[] = (string)$row->creator_id;
                elseif (in_array("self", $permissions['delivery_damages_view']['selected']) && (string)Auth::getData()->_id == (string)$row->creator_id)
                    $userIds[] = (string)$row->creator_id;
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
                    "id" => $row->id,
                    "fullname" => $row->fullname,
                ];
            }


            $items = [];

            

            foreach ($query as $value) {

                $user = $usersById[$value->user_id];


                $items[] = [
                    'id' => (string)$value->_id,
                    'user' => ($user ? [
                        "id" => $user["id"],
                        "fullname" => $user["fullname"],
                    ] : [
                        "id" => 0,
                        "fullname" => Lang::get("Deleted"),
                    ]),
                    'created_at' => TimeZones::date($value->created_at, "Y-m-d H:i"),
                ];
            }

            $data = array_slice($items, $skip, $limit);

            $response = array(
                "status" => "success",
                "data" => $data,
                "count" => count($items),
                "skip" => $skip,
                "limit" => $limit,
            );

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
