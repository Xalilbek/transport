<?php
namespace Controllers;

use Custom\Models\UserApps;
use Lib\Auth;
use Lib\Req;
use Models\UserApps;

class SetdeviceController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $device      = trim(strtolower(Req::get("os")));
        $deviceToken = trim(Req::get("device_token"));
        if (strlen($deviceToken) > 10 && in_array($device, ["ios", "android"])) {
            $exists = UserApps::findFirst([
                [
                    "device_token" => $deviceToken,
                ],
            ]);
            if ($exists) {
                $exists->user_id    = (string)Auth::getData()->_id;
                $exists->user_token = (string) Auth::$token;
                $exists->active     = 1;
                $exists->save();
            } else {
                $App               = new UserApps();
                $App->user_id      = (string)Auth::getData()->_id;
                $App->device       = $device;
                $App->device_token = $deviceToken;
                $App->user_token   = (string) Auth::$token;
                $App->active       = 1;
                $App->created_at   = UserApps::getDate();
                $App->save();
            }
            $success = true;
        } else {
            $error = "No token detected";
        }

        if ($success) {
            $response = [
                "status"      => "success",
                "description" => "",
            ];
        } else {
            $response = [
                "status"      => "error",
                "error_code"  => 1005,
                "description" => $error,
            ];
        }
        echo json_encode($response, true);
        exit();
    }
}
