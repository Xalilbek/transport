<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function getAccess($type)
    {
        $allow = false;
        $permissions = Auth::getPermissions();
        if ($permissions['parameters_delete']['allow']) {
            if ($permissions['parameters_delete']['all']) {
                $allow = true;
            } else {
                foreach (Parameters::typeListByKey() as $key => $value) {
                    if ($type == $key && in_array($key, $permissions['parameters_delete']['selected'])) {
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
        $data = Parameters::findFirst([
            [
                "_id" => Parameters::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        if (!$data) {
            $error = Lang::get("NoInformation", "Information not found");
        } elseif (!$this->getAccess($data->type)) {
            $error = Lang::get("PageNotAllowed");
        } else {
            Parameters::update(["_id" => $data->_id], [
                "is_deleted" => 1,
                "deleter_id" => (string)Auth::getData()->_id,
                "deleted_at" => Parameters::getDate(),
            ]);

            $response = array(
                "status" => "success",
                "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
            );

            // Log start
            Activities::log([
                "user_id" => (string)Auth::getData()->_id,
                "section" => "parameters",
                "operation" => "parameters_delete",
                "values" => [
                    "id" => $data->_id,
                    "type" => $data->type
                ],
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
