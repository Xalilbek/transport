<?php
namespace Controllers;

use Custom\Models\Files;
use Custom\Models\TempFiles;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class UploadphotoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error     = false;
        $req       = (array) Req::get();
        $id        = (int) $req['id'];
        $avatar_id = (string) $req['avatar_id'];
        $data      = Users::findFirst([
            [
                "id"         => (int) $id,
                "is_deleted" => [
                    '$ne' => 1,
                ],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $allow = false;
            if ($permissions['allusers_update']['allow']) {
                $allow = true;
            } elseif ($data->type == "moderator" && $permissions['moderators_update']['allow']) {
                $allow = true;
            } elseif ($data->type == "employee" && $permissions['employees_update']['allow']) {
                $allow = true;
            } elseif ($data->type == "user" && $permissions['users_update']['allow']) {
                if (Auth::getData()->type == "employee") {
                    if (in_array("all", $permissions['users_update']['selected'])) {
                        $allow = true;
                    }
                } elseif (Auth::getData()->type == "moderator") {
                    $allow = true;
                }
            }

            if ($allow) {
                $tempfile = TempFiles::findById($avatar_id);
                if ($tempfile) {
                    $file = Files::copyTempFile($tempfile, [
                        "parent_type" => "profile",
                        "parent_id"   => (int) $id,
                    ]);

                    $update = [
                        'avatar'     => [
                            "id"      => $file->_id,
                            "avatars" => (array) $file->avatars,
                            "server"  => (string) $file->server,
                        ],
                        'updated_at' => Users::getDate(),
                    ];

                    Users::update(["id" => (int) $id], $update);

                    $response = [
                        "status"      => "success",
                        "description" => Lang::get("UploadedSuccessfully", "Uploaded successfully"),
                    ];

                    // Log start
                    Activities::log([
                        "user_id"   => Auth::getData()->id,
                        "section"   => "users",
                        "operation" => "users_photo_upload",
                        "values"    => [
                            "id" => $id,
                        ],
                        "status"    => 1,
                    ]);
                    // Log end
                } else {
                    $error = Lang::get("FileNotFound", "File not found");
                }
            } else {
                $error = Lang::get("PageNotAllowed");
            }
        }
        if ($error) {
            $response = [
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}
