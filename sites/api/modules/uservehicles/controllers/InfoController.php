<?php
namespace Controllers;

use Custom\Models\Users;
use Custom\Models\UserVehicles;
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
        $id    = (string) Req::get("id");
        $data  = UserVehicles::findFirst([
            [
                "_id"        => UserVehicles::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['vehicles_view']['allow']) {
                $user     = Users::getByMongoId(Users::objectId($data->user_id));
                $vehicle  = Vehicles::findById($data->vehicle_id);
                $response = [
                    "status" => "success",
                    "data"   => [
                        'id'         => (string) $data->_id,
                        'user'       => ($user ? [
                            "id"       => $user->id,
                            "fullname" => $user->fullname,
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
                        'status'     => [
                            "value" => $data->status,
                            "text"  => UserVehicles::statusListByKey(Lang::getLang())[(string) $data->status],
                        ],
                        'created_at' => UserVehicles::dateFormat($data->created_at, "Y-m-d H:i"),
                    ],
                ];
            } else {
                $error = Lang::get("PermissionsDenied");
            }
        }
        if ($error) {
            $response = [
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}
