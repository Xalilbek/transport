<?php

namespace Controllers;

use Custom\Models\Vehicles;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();
        $id = (string)$req['id'];
        $title = (string)trim($req['title']);
        $number = (string)trim($req['number']);
        $type = (string)trim($req['type']);
        $description = (string)trim($req['description']);
        $status = (int)trim($req['status']);

        $data = Vehicles::findFirst([
            [
                "_id" => Vehicles::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!strlen($title) > 0) {
            $error = Lang::get("TitleError", "Title is empty");
        } elseif (!strlen($number) > 0) {
            $error = Lang::get("NumberError", "Number is empty");
        } elseif (Cache::is_brute_force("vehicles-edit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['vehicles_update']['allow']) {
                $update = [
                    "title" => (string)$title,
                    "number" => (string)$number,
                    "type" => (string)$type,
                    "description" => (string)$description,
                    "status" => (int)$status,
                    "updated_at" => Vehicles::getDate(),
                ];

                Vehicles::update(["_id" => $data->_id], $update);
                $response = [
                    "status" => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "vehicle",
                    "operation" => "vehicle_update",
                    "values" => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => Vehicles::findById($data->_id),
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
