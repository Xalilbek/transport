<?php
namespace Controllers;

use Custom\Models\Cases;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class SecurityController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
    }

    public function changePinAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id    = (int) Req::get("id");
        $pin   = (int) Req::get("pin");
        $data  = Users::findFirst([
            [
                "id"         => (int) $id,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!is_numeric($pin) || strlen($pin) !== 4) {
            $error = Lang::get("PinError", "Pin code is wrong. Only numbers required");
        } else if (!$data) {
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
                    "pin"        => (int) $pin,
                    "updated_at" => Users::getDate(),
                ];

                Users::update(["id" => (int) $id], $update);

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                );
                // Log start
                Activities::log([
                    "user_id"   => Auth::getData()->id,
                    "section"   => "users",
                    "operation" => "users_update",
                    "values"    => [
                        "id" => $data->id,
                    ],
                    "oldObject" => $data,
                    "newObject" => Users::getById($data->id),
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

    public function changePinStatusAction()
    {
        $permissions = Auth::getPermissions();

        $error       = false;
        $id          = (int) Req::get("id");
        $pin_require = (int) Req::get("pin_require");
        $data        = Users::findFirst([
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
                    } elseif (in_array("self", $permissions['users_update']['selected'])) {
                        $cases = Cases::find([
                            [
                                "is_deleted" => ['$ne' => 1],
                                'users'      => [
                                    '$in' => [Auth::getData()->id],
                                ],
                            ],
                        ]);
                        if ($cases) {
                            foreach ($cases as $row) {
                                foreach ($row->users as $uid) {
                                    if ($uid != Auth::getData()->id) {
                                        $allow = true;
                                    }
                                }
                            }
                        }
                    }
                } elseif (Auth::getData()->type == "moderator") {
                    $allow = true;
                }
            }

            if ($allow) {
                $update = [
                    "pin_require" => $pin_require == 1 ? true : false,
                    "updated_at"  => Users::getDate(),
                ];

                Users::update(["id" => (int) $id], $update);

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                );
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
