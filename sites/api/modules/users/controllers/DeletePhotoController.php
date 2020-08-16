<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeletePhotoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();
        $id    = (int) $req['id'];
        $data  = Users::findFirst([
            [
                "id"         => (int) $id,
                "is_deleted" => ['$ne' => 1],
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

                $update = [
                    'avatar' => false,
                ];
                Users::update(["id" => (int) $id], $update);

                $response = [
                    "status"      => "success",
                    "description" => Lang::get("DeletedSuccessfully"),
                ];

                // Log start
                Activities::log([
                    "user_id"   => Auth::getData()->id,
                    "section"   => "users",
                    "operation" => "users_photo_delete",
                    "values"    => [
                        "id" => $data->id,
                    ],
                    "status"    => 1,
                ]);
                // Log end
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
