<?php

namespace Controllers;

use Custom\Models\Damage;
use Custom\Models\Files;
use Custom\Models\TempFiles;
use Custom\Models\Businesses;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class UpdateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $vehicle_id = (string)trim($req['vehicle_id']);
        $description = (string)trim($req['description']);

        
        $id    = (string) Req::get("id");
        if(!strlen($id)>0){
            $error = Lang::get("NoData");
        } elseif (!strlen($vehicle_id)>0){
            $error = Lang::get("VehicleError", "No selected vehicle");
        }
        elseif (!strlen($description)>0){
            $error = Lang::get("DescriptionError", "Description is empty");
        }else{

            $data  = Damage::findFirst([
                [
                    "_id"        => Damage::objectId($id),
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);
            if (!$data) {
                $error = Lang::get("noInformation", "No information found");
            }
            elseif (!$permissions['vehicle_damages_update']['allow']) {
                $error = Lang::get("PermissionDenied");
            }
            else {

                $update = [
                    "parent_id" =>   (string)$vehicle_id,
                    "parent_type"=> Damage::TYPE_VEHICLE,
                    "description" => (string)$description,
                    "updated_at" => Damage::getDate()
                ];
                Damage::update(["_id" => Damage::objectId($id)], $update);

                $response = [
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];
            }

        }


        if ($error) {
            $response = [
                "status"      => "error",
                "description" => $error,
                "error_code"  => 1202,
            ];
        }

        echo json_encode($response, true);
        exit();
    }
}
