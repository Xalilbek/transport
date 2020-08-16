<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class MoveController extends \Phalcon\Mvc\Controller
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

        $id = (string)$req["id"];
        $type = (string)$req["type"];
        $parent_id = (string)$req["parent_id"];

        $data = Parameters::findFirst([
            [
                "_id" => Parameters::objectId($id),
                "type" => (string)$type,
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
                "parent_id" => (string)$parent_id,
                "updated_at" => Parameters::getDate(),
            ];

            Parameters::update(["_id" => $data->_id], $params);

            $response = array(
                "status" => "success",
                "description" => Lang::get("MovedSuccessfully", "Moved successfully"),
            );

            // Log start
            Activities::log([
                "user_id" => (string)Auth::getData()->_id,
                "section" => "parameters",
                "operation" => "parameters_update",
                "values" => [
                    "id" => $data->_id,
                    "type" => $data->type,
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
