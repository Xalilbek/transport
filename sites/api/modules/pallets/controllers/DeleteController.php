<?php

namespace Controllers;

use Custom\Models\Pallet;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");

        $data = Pallet::findFirst([
            [
                "_id" => Pallet::objectId($id),
                "is_deleted" => 0,
            ]
        ]);

        if (!$data) {
            $error = Lang::get("ObjectNotFound", "Data doesn't exist");
        } elseif ($permissions['palettes_delete']['allow']) {

            Pallet::update(
                ["_id" => Pallet::objectId($id)],
                [
                    "is_deleted" => 1,
                    "deleted_at" => Pallet::getDate()
                ]
            );


            $response = array(
                "status" => "success",
                "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
            );


        } else {
            $error = Lang::get("PermissionsDenied");
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