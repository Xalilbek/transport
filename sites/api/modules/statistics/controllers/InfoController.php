<?php

namespace Controllers;

use Custom\Models\Deliveries;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");
        $data = Deliveries::findFirst([
            [
                "_id" => Deliveries::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['deliveries_view']['allow']) {
                $employee = Users::getById($data->employee_id);

                $response = [
                    "status" => "success",
                    "data" => [
                        'id' => (string)$data->_id,
                        'number' => (string)$data->number,
                        'weight' => (float)$data->weight,
                        'price' => (float)$data->price,
                        'address' => (string)$data->address,
                        'geometry' => (array)$data->geometry,
                        'employee' => ($employee ? [
                            "id" => $employee->id,
                            "fullname" => $employee->fullname,
                        ] : [
                            "id" => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'status' => [
                            "value" => $data->status,
                            "text" => Deliveries::statusListByKey($this->lang)[(string)$data->status],
                        ],
                        'created_at' => Deliveries::dateFormat($data->created_at, "Y-m-d H:i"),
                    ],
                ];
            } else {
                $error = Lang::get("PageNotAllowed");
            }
        }
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
