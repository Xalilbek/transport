<?php

namespace Controllers;


use Custom\Models\Cache;
use Custom\Models\GeoObjects;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $data = false;
        $error = false;
        $id = (string)Req::get("id");
        $coordinates = Req::get("coordinates");
        $radius = (int)Req::get("radius");
        $title = str_replace(["<", ">", '"', "'"], "", trim(urldecode(Req::get("title"))));
        if (strlen($id) > 0)
            $data = GeoObjects::findFirst([
                [
                    "_id" => GeoObjects::objectId($id),
                    "user_id" => (string)Auth::getData()->_id,
                    "is_deleted" => 0,
                ]
            ]);


        if (Cache::is_brute_force("editObj-" . $id, ["minute" => 30, "hour" => 100, "day" => 300])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("editObj-" . Req::getServer("REMOTE_ADDR"), ["minute" => 100, "hour" => 600, "day" => 1500])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        } elseif (!Auth::checkPermission($this->lang, 'objects_update', $data->user_id)) {
            $error = Lang::get("PageNotAllowed");
        } elseif (strlen($title) < 2 || strlen($title) > 50) {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 40 characters)");
        } elseif (!in_array($data->type, ["marker", "circle", "polygon"])) {
            $error = Lang::get("ObjectTypeIsWrong", "Object type is wrong");
        } elseif ($data->type == "circle" && $radius < 1) {
            $error = Lang::get("RadiusIncorrect", "Radius is wrong");
        } else {
            $geoJson = GeoObjects::getGeojson($data->type, $coordinates);

            if ($geoJson) {
                $update = [
                    "title" => $title,
                    "type" => $data->type,
                    "geometry" => $geoJson,
                    "radius" => $radius,
                    "updated_at" => Objects::getDate()
                ];

                //var_dump($update);exit;
                GeoObjects::update(["_id" => $data->_id], $update);


                $response = [
                    "status" => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully")
                ];
            } else {
                $error = Lang::get("CoordinatesIncorrect", "Coordinates are incorrect");
            }
        }

        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 5317,
                "description" => $error,
            ];
        }
        echo json_encode((object)$response);
        exit;
    }

}