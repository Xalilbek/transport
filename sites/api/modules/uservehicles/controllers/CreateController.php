<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\UserVehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class CreateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {

        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id    = (string) trim($req['user_id']);
        $vehicle_id = (string) trim($req['vehicle_id']);
        $status = (int) trim($req['status']);

        $key = md5($user_id . $vehicle_id);

        if (Cache::is_brute_force("user-vehicles-add-" . $key, [
            "minute" => 20,
            "hour"   => 50,
            "day"    => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("user-vehicles-add-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour"   => 300,
            "day"    => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if ($permissions['vehicles_create']['allow']) {
                $new_id = UserVehicles::getNewId();
                $insert = [
                    "id"         => (int) $new_id,
                    "creator_id" => (string) Auth::getData()->_id,
                    "user_id"    => (string) $user_id,
                    "vehicle_id" => (string) $vehicle_id,
                    "status" => (int) $status,
                    "created_at" => UserVehicles::getDate(),
                ];

                $insert_id = UserVehicles::insert($insert);

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "user_vehicle",
                    "operation" => "user_vehicle_create",
                    "values"    => [
                        "id" => $insert_id,
                    ],
                    "status"    => 1,
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
