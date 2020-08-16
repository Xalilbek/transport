<?php

namespace Controllers;

use Custom\Models\Pallet;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");
        $quarter_pallet = (int)Req::get("quarter_pallet");
        $half_pallet = (int)Req::get("half_pallet");
        $full_pallet = (int)Req::get("full_pallet");
        $employee_id = (string)Req::get("user_id");


        $data = Pallet::findFirst([
            [
                "_id" => Pallet::objectId($id),
                "is_deleted" => 0,
            ]
        ]);

        if (!$quarter_pallet > 0 && !$half_pallet > 0 && !$full_pallet > 0) {
            $error = Lang::get("FieldsAreEmpty");
        }
        elseif (!strlen($employee_id)>0){
        $error = Lang::get("User not selected");
        }
        elseif (!$data) {
            $error = Lang::get("ObjectNotFound", "Data doesn't exist");
        } elseif (!$permissions['palettes_update']['allow']) {
            $error = Lang::get("PermissionDenied");
        } elseif (!$quarter_pallet > 0 && !$half_pallet > 0 && !$full_pallet > 0) {
            $error = Lang::get("FieldsAreEmpty");
        }
        else {
            Pallet::update(
                ["_id" => Pallet::objectId($id)],
                [
                    "quarter_pallet" => $quarter_pallet,
                    "half_pallet" => $half_pallet,
                    "full_pallet" => $full_pallet,
                    "employee_id" => $employee_id,
                    "updated_at" => Pallet::getDate()
                ]
            );


            $response = array(
                "status" => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            );


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