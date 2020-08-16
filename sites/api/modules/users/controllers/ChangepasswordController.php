<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;

class ChangepasswordController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error      = false;
        $req        = (array) Req::get();
        $id         = (int) $req['id'];
        $password   = (string) htmlspecialchars($req['password']);
        $repassword = (string) htmlspecialchars($req['repassword']);

        $data = Users::findFirst([
            [
                "id"         => (int) $id,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("editUser-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } elseif (strlen($password) < 6 || strlen($password) > 100) {
            $error = Lang::get("PasswordError", "Password is wrong (min 6 characters)");
        } elseif (strlen($repassword) > 0 && $password !== $repassword) {
            $error = Lang::get("RePasswordError", "Passwords dont match");
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
                    "password"   => Lib::generatePassword($password),
                    "updated_at" => Users::getDate(),
                ];

                Users::update(["id" => (int) $id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];
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
