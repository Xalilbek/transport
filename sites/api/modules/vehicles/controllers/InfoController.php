<?php

namespace Controllers;

use Custom\Models\Vehicles;
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
        $data = Vehicles::findFirst([
            [
                "_id" => Vehicles::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['vehicles_view']['allow']) {
                $response = [
                    "status" => "success",
                    "data" => [
                        'id' => (string)$data->_id,
                        'title' => (string)$data->title,
                        'number' => (string)$data->number,
                        'type' => (string)$data->type,
                        'description' => (string)$data->description,
                        'status' => [
                            "value" => $data->status,
                            "text" => Vehicles::statusListByKey(Lang::getLang())[(string)$data->status],
                        ],
                        'created_at' => Vehicles::dateFormat($data->created_at, "Y-m-d H:i"),
                    ],
                ];
            } else {
                $error = Lang::get("PermissionsDenied");
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
