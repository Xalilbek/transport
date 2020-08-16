<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Damage;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Files;
use Models\TempFiles;

class CreateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();
        $photo_ids = [];
//        $delivery_id = (string)trim($req['delivery_id']);
        $description = (string)trim($req['description']);
        foreach (Req::get("photo_ids") as $value)
            $photo_ids[] = (string)$value;


//        if (!strlen($delivery_id)>0){
//            $error = Lang::get("VehicleError", "No selected vehicle");
//        }
//        else
        if (!strlen($description) > 0) {
            $error = Lang::get("DescriptionError", "Description is empty");
        } elseif ($permissions['delivery_damages_create']['allow']) {

            $temp_photos_ids= [];
            foreach ($photo_ids as $photo){
                $temp_photos_ids[] = TempFiles::objectId($photo);
            }
            $temp_photos = TempFiles::find([
                [
                    "_id"     => [
                        '$in' => $temp_photos_ids,
                    ],
                    "is_deleted" => [
                        '$ne' => 1,
                    ],
                ],
            ]);
            $photoIds = [];
            foreach ($temp_photos as $temp_photo ){


                $file = Files::insert([
                    "user_id" => $temp_photo->user_id,
                    "file" => $temp_photo->file,
                    "server" => $temp_photo->server,
                    "filename" => $temp_photo->filename,
                    "type" => $temp_photo->type,
                    "size" => $temp_photo->size,
                    "avatars" => $temp_photo->avatars,
                    "for" => $temp_photo->for,
                    "created_at" => Files::getDate(),
                    "crm_type" => $temp_photo->crm_type,
                    "business_id" => $temp_photo->business_id,
                ]);
                $photoIds [] = $file;

            }

            


            $new_id = Damage::getNewId();
            $insert = [
                "id" => (int)$new_id,
                "creator_id" => (string)Auth::getData()->_id,
                //                "parent_id" =>   (string)$delivery_id,
                "parent_type" => Damage::TYPE_DELIVERY,
                "description" => (string)$description,
                "photo_ids" => $photoIds,
                "created_at" => Damage::getDate(),
            ];

            $insert_id = Damage::insert($insert);

            $response = array(
                "status" => "success",
                "description" => Lang::get("AddedSuccessfully", "Added successfully"),
            );

            // Log start
            Activities::log([
                "user_id" => (string)Auth::getData()->_id,
                "section" => Damage::TYPE_DELIVERY . "_damage",
                "operation" => Damage::TYPE_DELIVERY . "_damage_create",
                "values" => [
                    "id" => $insert_id,
                ],
                "status" => 1,
            ]);
            // Log end
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
