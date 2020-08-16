<?php

namespace Controllers;




use Custom\Models\Damage;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\TempFiles;

class DeletefilesController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();
        $error = false;
        $id = (string)Req::get("id");
        if (!$permissions['delivery_damages_update']['allow']) {
            $error = Lang::get("PermissionDenied");
        }
        elseif (count(Req::get("photo_ids")) > 0) {
            $photo_ids = [];
            foreach (Req::get("photo_ids") as $value)
                $photo_ids[] = (string)$value;

            $data = Damage::findFirst([
                [
                    "_id" => Damage::objectId($id),
                    "parent_type" => Damage::TYPE_DELIVERY,
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);

            if (!count($data->photo_ids)>0|| !count($photo_ids)>0 ){
                $error = Lang::get("NoPhoto");
            }else{
                Damage::update(["_id" => $data->_id],
                    [
                        "photo_ids" =>
                            array_values(array_diff($data->photo_ids, $photo_ids)),
                    ]
                );

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