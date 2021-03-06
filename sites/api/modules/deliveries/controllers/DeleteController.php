<?php
namespace Controllers;

use Custom\Models\Deliveries;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = Req::get();
        $id    = (string) $req["id"];
        $data  = Deliveries::findFirst([
            [
                "_id"         => Deliveries::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
			if($permissions['deliveries_delete']['allow']) {
                $update = [
                    "is_deleted" => 1,
                    "deleter_id" => (string)Auth::getData()->_id,
                    "deleted_at" => Deliveries::getDate(),
                ];
                Deliveries::update(["_id" => $data->_id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
                ];
                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "delivery",
                    "operation" => "delivery_delete",
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
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode($response);
        exit;
    }
}
