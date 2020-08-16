<?php

namespace Controllers;


use Custom\Models\Damage;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Files;
use Models\TempFiles;

class AddfilesController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();
        $error = false;
        $id = (string)Req::get("id");
        if (!$permissions['vehicle_damages_update']['allow']) {
        $error = Lang::get("PermissionDenied");
        }
        elseif (count(Req::get("photo_ids")) > 0) {
            $photo_ids = [];
            foreach (Req::get("photo_ids") as $value)
                $photo_ids[] = (string)$value;



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



            $data = Damage::findFirst([
                [
                    "_id" => Damage::objectId($id),
                    "parent_type" => Damage::TYPE_VEHICLE,
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);

            Damage::update(["_id" => $data->_id],
                [
                    "photo_ids" => array_merge($data->photo_ids, $photoIds),
                ]
            );

            $response = [
                "status"      => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            ];
        }
        else{
            $error = Lang::get("NoPhoto", "No Photo");
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