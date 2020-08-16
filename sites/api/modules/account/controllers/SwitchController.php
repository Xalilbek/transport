<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Tokens;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;

class SwitchController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error    = false;
        $response = [];

        $req      = (array) Req::get();
        $password = trim($req["password"]);
        $user_id  = (string)Auth::getData()->_id;
        if (Cache::is_brute_force("authIn-" . Auth::getData()->id, ["minute" => 20, "hour" => 200, "day" => 510])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if (in_array($user_id, Auth::$tokenData->allowed_users)) {
                Tokens::update(
                    [
                        "token" => Auth::$token,
                    ],
                    [
                        "user_id"     => $user_id,
                        "switched_at" => Tokens::getDate(),
                    ]
                );

                $success = true;
            } else if (mb_strlen($password) > 0) {
                $user = Users::findFirst([
                    [
                        "_id"          => Users::objectId($user_id),
                        "password"    => Lib::generatePassword($password),
                        "is_deleted"  => ['$ne' => 1],
                        "crm_type"    => ['$ne' => 'test'],
                        "business_id" => ['$ne' => 'test'],
                    ],
                ]);
                if ($user) {
                    $allowed_users = Auth::$tokenData->allowed_users;
                    if (!is_array($allowed_users)) {
                        $allowed_users = [];
                    }

                    $allowed_users[] = $user_id;
                    if (!in_array((string)Auth::getData()->_id, $allowed_users)) {
                        $allowed_users[] = (string)Auth::getData()->_id;
                    }

                    Tokens::update(
                        [
                            "token" => Auth::$token,
                        ],
                        [
                            "user_id"       => $user_id,
                            "allowed_users" => $allowed_users,
                            "switched_at"   => Tokens::getDate(),
                        ]
                    );

                    Auth::clearSwitchCache();

                    $success = true;
                } else {
                    $error = Lang::get("LoginError", "Username or password is wrong");
                }
            } else {
                $error = Lang::get("LoginError", "Username or password is wrong");
            }
        }

        if ($error) {
            $response = [
                "status"      => "error",
                "description" => $error,
                "error_code"  => 1021,
            ];
        } elseif ($success) {
            $response = [
                "status"      => "success",
                "description" => Lang::get("SwitchedSuccessfully", "Switched successfully"),
            ];
        }

        echo json_encode($response, true);
        exit();
    }
}
