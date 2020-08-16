<?php

namespace Controllers;

use Custom\Models\Transactions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = Req::get();
        $id = (string)$req["id"];
        $data = Transactions::findFirst([
            [
                "_id" => Transactions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $allow = false;
            if ($permissions['transactions_delete']['allow']) {
                if (in_array("all", $permissions['transactions_delete']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_delete']['selected'])) {
                    if ($data->parent_type == "user" && (string)$data->parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {
                $update = [
                    "is_deleted" => 1,
                    "deleter_id" => (string)Auth::getData()->_id,
                    "deleted_at" => Transactions::getDate(),
                ];
                Transactions::update(["_id" => $data->_id], $update);
                $response = [
                    "status" => "success",
                    "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
                ];
                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "transactions",
                    "operation" => "transactions_delete",
                    "values" => [
                        "id" => $data->_id,
                    ],
                    "status" => 1,
                ]);
                // Log end
            } else {
                $error = Lang::get("PageNotAllowed");
            }
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
