<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\GeoObjects;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {

        $error = false;
        $objectType = strtolower(Req::get("type"));
        $coordinates = Req::get("coordinates");
        $radius = (int)Req::get("radius");
        $title = str_replace(["<", ">", '"', "'"], "", trim(urldecode(Req::get("title"))));
        if (!Auth::checkPermission($this->lang, 'geoobjects_create',(string)Auth::getData()->_id )) {
            $error = Lang::get("PageNotAllowed");
        }
        elseif (Cache::is_brute_force("objAdd-" . Req::getServer("REMOTE_ADDR"), ["minute" => 40, "hour" => 900, "day" => 9000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (strlen($title) < 2 || strlen($title) > 50) {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 40 characters)");
        } elseif (!in_array($objectType, ["marker", "circle", "polygon"])) {
            $error = Lang::get("ObjectTypeIsWrong", "Object type is wrong");
        } elseif ($objectType == "circle" && $radius < 1) {
            $error = Lang::get("RadiusIncorrect", "Radius is wrong");
        } else {
            $geoJson = GeoObjects::getGeojson($objectType, $coordinates);

            if ($geoJson) {
                $userInsert = [
                    "user_id" => (string)Auth::getData()->_id,
                    "title" => $title,
                    "type" => $objectType,
                    "geometry" => $geoJson,
                    "radius" => $radius,
                    "is_deleted" => 0,
                    "created_at" => Objects::getDate()
                ];

                GeoObjects::insert($userInsert);

                $response = array(
                    "status" => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );


            } else {
                $error = Lang::get("CoordinatesIncorrect", "Coordinates are incorrect");
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