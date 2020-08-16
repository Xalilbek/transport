<?php

namespace Controllers;

use Custom\Models\Pallet;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Files;
use Models\TempFiles;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $quarter_pallet = (int)Req::get("quarter_pallet");
        $half_pallet = (int)Req::get("half_pallet");
        $full_pallet = (int)Req::get("full_pallet");
        $employee_id = (string)Req::get("user_id");
        foreach (Req::get("photo_ids") as $value)
            $photo_ids[] = (string)$value;

        if (!$quarter_pallet > 0 && !$half_pallet > 0 && !$full_pallet > 0) {
            $error = Lang::get("FieldsAreEmpty");
        } elseif (!strlen($employee_id) > 0) {
            $error = Lang::get("User not selected");
        } elseif ($permissions['palettes_create']['allow']) {


            $temp_photos_ids = [];
            foreach ($photo_ids as $photo) {
                $temp_photos_ids[] = TempFiles::objectId($photo);
            }
            $temp_photos = TempFiles::find([
                [
                    "_id" => [
                        '$in' => $temp_photos_ids,
                    ],
                    "is_deleted" => [
                        '$ne' => 1,
                    ],
                ],
            ]);
            $photoIds = [];
            foreach ($temp_photos as $temp_photo) {


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

            Pallet::insert(
                [
                    "id" => Pallet::getNewId(),
                    "creator_id" => (string)Auth::getData()->_id,
                    "employee_id" => (string)$employee_id,
                    "quarter_pallet" => $quarter_pallet,
                    "half_pallet" => $half_pallet,
                    "full_pallet" => $full_pallet,
                    "photo_ids" => $photoIds,
                    "is_deleted" => 0,
                    "created_at" => Pallet::getDate()
                ]
            );


            $response = array(
                "status" => "success",
                "description" => Lang::get("AddedSuccessfully", "Added successfully"),
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