<?php

namespace Controllers;

use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");

        $data = Objects::findFirst([
            [
                "_id" => Objects::objectId($id),
                "users" => (string)Auth::getData()->_id,
                "is_deleted" => 0,
            ]
        ]);
        if (!$data) {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        } elseif (!Auth::checkPermission(Lang::getLang(), 'objects_view', $data->owner_id)) {
            $error = Lang::get("PageNotAllowed");
        } else {

            $obj = Objects::filterData(Lang::getLang(), $data);

            $obj["type"] = [
                "type" => (int)$data->type,
                "title" => ""
            ];

            $response = [
                "status" => "success",
                "data" => $obj
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