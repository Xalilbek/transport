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
        $req = (array)Req::get();

        $title = (string)trim($req['title']);
        $type = (string)trim($req['type']);
        $description = (string)trim($req['description']);
        $number = (string)trim($req['number']);
        $status = (int)trim($req['status']);

        if ($permissions['vehicles_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if (in_array($status, [1, 2, 3])) {
                $binds['status'] = (int)$status;
            }

            if (strlen($title) > 0) {
                $binds['title'] = [
                    '$regex' => $title,
                    '$options' => 'i',
                ];
            }

            if (strlen($number) > 0) {
                $binds['number'] = [
                    '$regex' => $number,
                    '$options' => 'i',
                ];
            }

            if (strlen($type) > 0) {
                $binds['type'] = [
                    '$regex' => $type,
                    '$options' => 'i',
                ];
            }

            if (strlen($description) > 0) {
                $binds['description'] = [
                    '$regex' => $description,
                    '$options' => 'i',
                ];
            }


            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['number', 'type', 'status', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip = (int)$req['skip'];
            $limit = (int)$req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = Vehicles::find([
                $binds,
                "skip" => $skip,
                "limit" => $limit,
                "sort" => $sort,
            ]);

            $count = Vehicles::count([
                $binds,
            ]);

            $vehicleIds = [];
            foreach ($query as $row) {
                $vehicleIds[] = (string)$row->_id;
            }

            $userVehicles = UserVehicles::find([
                [
                    "vehicle_id" => [
                        '$in' => $vehicleIds,
                    ],
                    "is_deleted" => [
                        '$ne' => 1,
                    ],
                ],
            ]);

            $userIds = [];
            $usersById = [];
            foreach ($userVehicles as $row) {
                $userIds[] = (string)$row->user_id;
            }

            $userList = Users::find([
                [
                    "_id" => [
                        '$in' => $userIds,
                    ],
                ],
            ]);

            foreach ($userList as $row) {
                $usersById[$row->id] = $row;
            }

            $usersByVehicleId = [];
            foreach ($userVehicles as $row) {
                $usersByVehicleId[(string)$row->vehicle_id][] = [
                    "id" => (string)$row->user_id,
                    "fullname" => $usersById[$row->user_id]->fullname,
                    "status" => $row->status,
                ];
            }

            $data = [];
            if (count($query) > 0) {

                foreach ($query as $value) {
                    $employees = $usersByVehicleId[(string)$value->_id];
                    $data[] = [
                        'id' => (string)$value->_id,
                        'title' => (string)$value->title,
                        'number' => (string)$value->number,
                        'users' => $employees,
                        'type' => (string)$value->type,
                        'description' => (string)$value->description,
                        'status' => [
                            "value" => $value->status,
                            "text" => Vehicles::statusListByKey($this->lang)[(string)$value->status],
                        ],
                        'created_at' => Vehicles::dateFormat($value->created_at, "Y-m-d H:i"),
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data" => $data,
                    "count" => $count,
                    "skip" => $skip,
                    "limit" => $limit,
                );
            } else {
                $error = Lang::get("noInformation", "No information found");
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
