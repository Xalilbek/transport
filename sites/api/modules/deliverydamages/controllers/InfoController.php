<?php

namespace Controllers;

use Custom\Models\Damage;
use Custom\Models\Deliveries;
use Custom\Models\Users;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;
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
                "parent_type" => Damage::TYPE_DELIVERY,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } elseif ($permissions['delivery_damages_view']['allow']) {


            $user = Users::getByMongoId(Users::objectId($data->user_id));
//            $delivery = Deliveries::findById($data->vehicle_id);
            $response = [
                "status" => "success",
                "data" => [
                    'id' => (string)$data->_id,
                    'description' => $data->description,
                    'user' => ($user ? [
                        "id" => $user->id,
                        "fullname" => $user->fullname,
                    ] : [
                        "id" => 0,
                        "fullname" => Lang::get("Deleted"),
                    ]),
//                    'delivery' => ($delivery ? [
//                        "id" => (string)$delivery->_id,
//                        "title" => (string)$delivery->title,
//                    ] : [
//                        "id" => null,
//                        "title" => Lang::get("Deleted"),
//                    ]),

                    'photos' => Damage::getPhotoList($data->photo_ids),

                    'created_at' => TimeZones::date($data->created_at, "Y-m-d H:i"),

                ],
            ];
        } else {
            $error = Lang::get("PermissionsDenied");
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
