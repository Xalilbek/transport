<?php

namespace Controllers;

use Custom\Models\Alerts;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $error = false;
        $id = (string)Req::get("id");
        $title = str_replace(["<", ">", '"', "'"], "", trim(urldecode(Req::get("title"))));
        $type = (int)Req::get("type");

        $object_ids = [];
        foreach (Req::get("object_ids") as $value)
            $object_ids[] = $value;


        $geozone_ids = [];
        foreach (Req::get("geozone_ids") as $value)
            $geozone_ids[] = (string)$value;

        $geopoint_ids = [];
        foreach (Req::get("geopoint_ids") as $value)
            $geopoint_ids[] = (string)$value;
        $radius = (int)Req::get("radius");
        $speed = (int)Req::get("speed");
        $text = (string)trim(Req::get("text"));

        if (strlen($id) > 0)
            $data = Alerts::findFirst([
                [
                    "_id" => Alerts::objectId($id),
                    "user_id" => (string)Auth::getData()->_id,
                    "is_deleted" => 0,
                ]
            ]);

        if (Cache::is_brute_force("editObj-" . $id, ["minute" => 30, "hour" => 100, "day" => 300])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("editObj-" . Req::getServer("REMOTE_ADDR"), ["minute" => 100, "hour" => 600, "day" => 1500])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("AlertNotFound", "Alert doesn't exist");
        } elseif (!Auth::checkPermission(Lang::getLang(), 'notifications_settings_update',$data->user_id )) {
            $error = Lang::get("PageNotAllowed");
        } elseif (strlen($title) < 2 || strlen($title) > 100) {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 100 characters)");
        } elseif ($type < 1) {
            $error = Lang::get("AlertTypeError", "Alert type is wrong");
        } elseif (in_array($type, [1, 2, 3, 4]) && (count($geozone_ids) == 0 && count($geopoint_ids) == 0)) {
            $error = Lang::get("GeozonesEmpty", "Geozones / Geopoints are empty");
        } elseif (in_array($type, [3, 4]) && $radius < 1) {
            $error = Lang::get("RadiusIsWrong", "Radius is wrong");
        } elseif (in_array($type, [5]) && $speed < 1) {
            $error = Lang::get("SpeedIsWrong", "Speed is wrong");
        }
        //elseif(strlen($text) < 2 || strlen($text) > 100)
        //{
        //	$error = Lang::get("AlertTextError", "Alert text is wrong. (minimum 2 and maximum 100 characters)");
        //}
        else {
            $update = [
                "title" => $title,
                "type" => $type,
                "object_ids" => $object_ids,
                "geozone_ids" => $geozone_ids,
                "geopoint_ids" => $geopoint_ids,
                "radius" => $radius,
                "speed" => $speed,
                "text" => $text,
                "updated_at" => Alerts::getDate()
            ];
            Alerts::update(["_id" => Alerts::objectId($id)], $update);

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