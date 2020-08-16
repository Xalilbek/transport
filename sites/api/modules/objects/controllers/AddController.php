<?php

namespace Controllers;


use Custom\Models\Cache;
use Custom\Models\History;
use Custom\Models\LogsTracking;
use Custom\Models\Objects;
use Custom\Models\Vehicles;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {


        $error = false;
        $vehicle_id = (string)Req::get("vehicle_id");
        $icon = (int)Req::get("icon");
        $imei = trim(Req::get("imei"), " ");
        $title = str_replace(["<", ">", '"', "'"], "", trim(urldecode(Req::get("title"))));


        if (!Auth::checkPermission(Lang::getLang(), 'objects_create', (string)Auth::getData()->_id)) {
            $error = Lang::get("PageNotAllowed");
        } elseif (Cache::is_brute_force("objAdd-" . $imei, ["minute" => 20, "hour" => 50, "day" => 100])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("objAdd-" . Req::getServer("REMOTE_ADDR"), ["minute" => 40, "hour" => 300, "day" => 900])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (strlen($title) < 2 || strlen($title) > 50) {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 40 characters)");
        } elseif (strlen($imei) < 6 || strlen($imei) > 40 || !is_numeric($imei)) {
            $error = Lang::get("SerialIdWrong", "IMEI is wrong");
        } elseif ($icon < 1) {
            $error = Lang::get("IconWrong", "Please, choose icon");
        } else {
            $objExist = Objects::getByImei($imei);
            if ($objExist && $objExist->owner_id > 0) {
                $error = Lang::get("ObjectExists", "Object exists");
            } else {

                $obj_from_filter = Objects::findFirst([
                    [
                        "imei" => $imei,
                        "business_id" => 0
                    ]
                ]);

                $id = $obj_from_filter->_id;

                if ($obj_from_filter) {


                    Objects::update(
                        [
                            "_id" => $id,
                            "business_id" => 0
                        ],
                        [
                            "business_id" => BUSINESS_ID,
                            "owner_id" => (string)Auth::getData()->_id,
                            "users" => [(string)Auth::getData()->_id],
                            "title" => $title,
                            "status" => 2,
                            "is_deleted" => 0,
                            "icon" => $icon,
                            "vehicle_id" => $vehicle_id,
                            "owned_at" => Objects::getDate()
                        ]
                    );

                    History::update(
                        [
                            "object_id" => Objects::objectId($id),
                            "business_id" => 0
                        ],
                        [
                            "business_id" => BUSINESS_ID
                        ]
                    );
                    LogsTracking::update(
                        [
                            "object_id" => Objects::objectId($id),
                            "business_id" => 0
                        ],
                        [
                            "business_id" => BUSINESS_ID
                        ]
                    );
                } else {
                    Objects::insert(
                        [
                            "id" => Objects::getNewId(),
                            "owner_id" => (string)Auth::getData()->_id,
                            "users" => [(string)Auth::getData()->_id],
                            "title" => $title,
                            "imei" => $imei,
                            "status" => 2,
                            "is_deleted" => 0,
                            "icon" => $icon,
                            "vehicle_id" => $vehicle_id,
                            "owned_at" => Objects::getDate()
                        ]
                    );
                }


                $response = array(
                    "status" => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );
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