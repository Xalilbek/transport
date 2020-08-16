<?php

namespace Controllers;

use Custom\Models\GeoObjects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $data = false;
        $error = false;
        $id = (string)Req::get("id");
        if (strlen($id) > 0)
            $data = GeoObjects::findFirst([
                [
                    "_id" => GeoObjects::objectId($id),
                    "user_id" => (string)Auth::getData()->_id,
                    "is_deleted" => 0,
                ]
            ]);

        if (!$data) {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        } elseif (!Auth::checkPermission($this->lang, 'objects_view', $data->user_id)) {
            $error = Lang::get("PageNotAllowed");
        } else {
            $response = [
                "status" => "success",
                "data" => [
                    "id" => $id,
                    "title" => (string)$data->title,
                    "type" => (string)$data->type,
                    "coordinates" => $data->geometry->coordinates[0],
                    "created_at" => TimeZones::date($data->created_at, "Y-m-d H:i:s")
                ]
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