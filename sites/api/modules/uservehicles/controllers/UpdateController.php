<?php

namespace Controllers;

use Custom\Models\UserVehicles;
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

        $field = trim($req['field']);
        $value = trim($req['value']);
        
        $id    = (string) Req::get("id");
        $data  = UserVehicles::findFirst([
            [
                "_id"        => UserVehicles::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } elseif (!$permissions['uservehicles_update']['allow']) {
            $error = Lang::get("PermissionDenied");
        } else {

            $update = [
                "updated_at" => Businesses::getDate(),
            ];

            if ($field == "file_id") {
                if (strlen($value) > 0) {
                    $file_id  = $value;
                    $tempfile = TempFiles::findById($file_id);
                    if ($tempfile) {
                        $update["file_id"] = (string) $file_id;

                        $files = [];
                        foreach ($tempfile as $key => $val) {
                            $files[$key] = $val;
                        }

                        $files["parent_type"] = "test_file";
                        $files["parent_id"]   = (string) $data->_id;
                        $res                  = Files::insert($files);
                    }
                }
            } elseif($field == "status"){
                $update["status"] = $value == 1 ? 1 : 0;
            }

            $upd = UserVehicles::update(["_id" => $data->_id], $update);

            $response = [
                "status"      => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            ];
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
