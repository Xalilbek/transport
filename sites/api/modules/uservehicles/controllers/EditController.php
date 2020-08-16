<?php
namespace Controllers;

use Custom\Models\UserVehicles;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error       = false;
        $req         = (array) Req::get();
        $id          = (string) $req['id'];
        $user_id = (int) trim($req['user_id']);
        $vehicle_id      = (string) trim($req['vehicle_id']);

        $data = UserVehicles::findFirst([
            [
                "_id"         => UserVehicles::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("user-vehicles-edit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
			if($permissions['vehicles_update']['allow']) {
                $update = [
                    "user_id" => (int) $user_id,
                    "vehicle_id"      => (string) $vehicle_id,
                    "updated_at"  => UserVehicles::getDate(),
                ];

                UserVehicles::update(["_id" => $data->_id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id" => Auth::getData()->id,
                    "section" => "user_vehicle",
                    "operation" => "user_vehicle_update",
                    "values" => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => UserVehicles::findById($data->_id),
                    "status" => 1,
                ]);
                // Log end
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
