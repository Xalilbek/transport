<?php

namespace Controllers;

use Custom\Models\Damage;
use Custom\Models\Users;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Files;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");
        $data = Damage::findFirst([
            [
                "_id" => Damage::objectId($id),
                "parent_type" => Damage::TYPE_VEHICLE,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['vehicle_damages_view']['allow']) {


                $user = Users::getByMongoId(Users::objectId($data->user_id));
                $vehicle = Vehicles::findById($data->parent_id);
                $response = [
                    "status" => "success",
                    "data" => [
                        'id' => (string)$data->_id,
                        'description'=> $data->description,
                        'vehicle' => ($vehicle ? [
                            "id" => (string)$vehicle->_id,
                            "number" => (string)$vehicle->number,
                        ] : [
                            "id" => null,
                            "number" => Lang::get("Deleted"),
                        ]),

                        'photos' => Damage::getPhotoList($data->photo_ids),

                        'created_at' => Damage::dateConvertTimeZone($data->created_at, "Y-m-d H:i"),

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
