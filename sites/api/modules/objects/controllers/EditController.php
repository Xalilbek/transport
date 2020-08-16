<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Objects;
use Custom\Models\ObjectsGroups;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

        $error = false;
        $id = (string)Req::get("id");
        $vehicle_id = (string)Req::get("vehicle_id");
        $phone = trim(Req::get("phone"), " ");
        $icon = (int)Req::get("icon");
        $title = trim(str_replace(["<", ">"], "", trim(Req::get("title"))));
        $data = Objects::findFirst([
            [
                "_id" => Objects::objectId($id),
                "users" => (string)Auth::getData()->_id,
                "is_deleted" => 0,
            ]
        ]);

        if (Cache::is_brute_force("editObj-" . $id, ["minute" => 30, "hour" => 100, "day" => 300])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("editObj-" . Req::getServer("REMOTE_ADDR"), ["minute" => 100, "hour" => 600, "day" => 1500])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        } elseif (!Auth::checkPermission(Lang::getLang(), 'objects_update', $data->owner_id)) {
            $error = Lang::get("PageNotAllowed");
        } elseif (strlen($title) < 2 || strlen($title) > 50) {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 40 characters)");
        } elseif ($icon < 1) {
            $error = Lang::get("IconWrong", "Please, choose icon");
        } else {
            $update = [
                "title" => $title,
                "icon" => $icon,
                "vehicle_id" => $vehicle_id,
                "phone" => $phone,
                "updated_at" => Objects::getDate()
            ];
            Objects::update(["_id" => Objects::objectId($id)], $update);


            $response = [
                "status" => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully")
            ];
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