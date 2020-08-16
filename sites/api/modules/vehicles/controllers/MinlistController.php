<?php

namespace Controllers;

use Custom\Models\Users;
use Custom\Models\UserVehicles;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class MinlistController extends \Phalcon\Mvc\Controller
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
        $allow = false;
        if ($permissions['vehicles_view']['allow']) {
            $allow = true;
        }

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
        if (in_array($sort_field, ['number', 'type', 'description', 'status', 'created_at'])) {
            $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
        }

        if (in_array("all", $permissions['vehicles_view']['selected'])) {
            $allow = true;

        } elseif (in_array("self", $permissions['vehicles_view']['selected'])) {
            $userVehicles = UserVehicles::find([
                [

                    "user_id" => (string)Auth::getData()->_id,
                    "is_deleted" => [
                        '$ne' => 1,
                    ],
                ],
            ]);

            $vehicleIds = [];
            foreach ($userVehicles as $userVehicle) {
                $vehicleIds [] = Vehicles::objectId($userVehicle->vehicle_id);
            }


            $binds["_id"] = [
                '$in' => $vehicleIds,
            ];
            $allow = true;
        }

        if (!$allow) {
            $error = Lang::get("PermissionDenied");

        } else {
            $query = Vehicles::find([
                $binds,
                "sort" => $sort,
            ]);

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $data[] = [
                        'id' => (string)$value->_id,
                        'title' => (string)$value->title,
                        'number' => (string)$value->number,
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data" => $data,
                );


            } else {
                $error = Lang::get("noInformation", "No information found");
            }
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
