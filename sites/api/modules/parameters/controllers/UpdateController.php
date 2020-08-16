<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class UpdateController extends \Phalcon\Mvc\Controller
{
    public function getAccess($type)
    {
        $allow = false;
        $permissions = Auth::getPermissions();
        if ($permissions['parameters_update']['allow']) {
            if ($permissions['parameters_update']['all']) {
                $allow = true;
            } else {
                foreach (Parameters::typeListByKey() as $key => $value) {
                    if ($type == $key && in_array($key, $permissions['parameters_update']['selected'])) {
                        $allow = true;
                    }
                }
            }
        }
        return $allow;
    }

    public function indexAction()
    {
        $error = false;
        $response = [];
        $req = (array)Req::get();
        $field = (string)$req["field"];
        $value = $req["value"];
        $id = (string)$req["id"];
        $data = Parameters::findFirst([
            [
                "_id" => Parameters::objectId($id),
                "is_deleted" => [
                    '$ne' => 1,
                ],
            ],
        ]);

        if (!$data) {
            $error = Lang::get("NoInformation", "Information not found");
        } elseif (!$this->getAccess($data->type)) {
            $error = Lang::get("PageNotAllowed");
        } else {
            $params = [
                "updated_at" => Parameters::getDate(),
            ];

            if ($field == "active") {
                $params["active"] = (int)$value;
            } elseif ($field == "parent") {
                $params["parent_id"] = (string)$value;
            } elseif ($field == "index") {
                $params["index"] = (int)$value;
            }

            Parameters::update(["_id" => $data->_id], $params);

            $response = [
                "status" => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            ];

            // Log start
            Activities::log([
                "user_id" => Auth::getData()->id,
                "section" => "parameters",
                "operation" => "parameters_update",
                "values" => [
                    "id" => $data->_id,
                    "type" => $data->type
                ],
                "oldObject" => $data,
                "newObject" => Parameters::findById($data->_id),
                "status" => 1,
            ]);
            // Log end
        }

        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1017,
                "description" => $error,
            ];
        }
        echo json_encode($response);
        exit;
    }
}
