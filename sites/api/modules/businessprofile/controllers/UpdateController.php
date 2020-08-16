<?php

namespace Controllers;

use Custom\Models\Businesses;
use Custom\Models\Files;
use Custom\Models\TempFiles;
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

        if (!$permissions['businessprofile_update']['allow']) {
            $error = Lang::get("PageNotAllowed");
        } else {

            $update = [
                "updated_at" => Businesses::getDate(),
            ];

            if ($field == "avatar") {
                if (strlen($value) > 0) {
                    $file_id  = $value;
                    $tempfile = TempFiles::findById($value);
                    if ($tempfile) {
                        $file = Files::copyTempFile($tempfile, [
                            'parent_type' => 'business_avatar',
                            'parent_id'   => (int) Auth::$business->data->id,
                        ]);
                        $update["avatar"] = [
                            "id"      => $file->_id,
                            "avatars" => (array) $file->avatars,
                            "server"  => (string) $file->server,
                        ];
                    }
                }
            }

            $upd = Businesses::update(["id" => Auth::$business->data->id], $update);

            $response = [
                "status"      => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                $upd
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
