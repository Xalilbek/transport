<?php
namespace Controllers;

use Custom\Models\Damage;
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
        if (!strlen($id)>0){
            $error = Lang::get("NoData");
        }else{
            $data  = Damage::findFirst([
                [
                    "_id"        => Damage::objectId($id),
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);
            if (!$data) {
                $error = Lang::get("noInformation", "No information found");
            } elseif ($permissions['vehicle_damages_delete']['allow']) {
                    $update = [
                        "is_deleted" => 1,
                        "deleter_id" => (string)Auth::getData()->_id,
                        "deleted_at" => Damage::getDate(),
                    ];
                    Damage::update(["_id" => $data->_id], $update);
                    $response = [
                        "status"      => "success",
                        "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
                    ];
                    // Log start
                    Activities::log([
                        "user_id"   => (string)Auth::getData()->_id,
                        "section"   => Damage::TYPE_VEHICLE."_damage",
                        "operation" => Damage::TYPE_VEHICLE."_damage_delete",
                        "values"    => [
                            "id" => $data->_id,
                        ],
                        "status"    => 1,
                    ]);
                    // Log end
            } else {
                $error = Lang::get("PermissionsDenied");
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
