<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class CreateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();

        $title = (string)trim($req['title']);
        $number = (string)trim($req['number']);
        $type = (string)trim($req['type']);
        $description = (string)trim($req['description']);
        $status = (int)trim($req['status']);

        $key = md5($number);
        if (!strlen($title) > 0) {
            $error = Lang::get("TitleError", "Title is empty");
        } elseif (!strlen($number) > 0) {
            $error = Lang::get("NumberError", "Number is empty");
        } elseif (Cache::is_brute_force("vehicles-add-" . $key, [
            "minute" => 20,
            "hour" => 50,
            "day" => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("vehicles-add-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour" => 300,
            "day" => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if ($permissions['vehicles_create']['allow']) {
                $new_id = Vehicles::getNewId();
                $insert = [
                    "id" => (int)$new_id,
                    "creator_id" => (string)Auth::getData()->_id,
                    "title" => (string)$title,
                    "number" => (string)$number,
                    "type" => (string)$type,
                    "description" => (string)$description,
                    "drivers" => [],
                    "status" => (int)$status,
                    "created_at" => Vehicles::getDate(),
                ];

                $insert_id = Vehicles::insert($insert);

                $response = array(
                    "status" => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );

                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "vehicle",
                    "operation" => "vehicle_create",
                    "values" => [
                        "id" => $insert_id,
                    ],
                    "status" => 1,
                ]);
                // Log end
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
