<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class RecommendationsController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();
        $from        = Req::get("from");

        $q     = (string) trim(htmlspecialchars(urldecode(Req::get('query'))));
        $binds = [
            "is_deleted" => ['$ne' => 1],
        ];

        if (strlen($q) > 0) {
            $binds["fullname"] = [
                '$regex'   => $q,
                '$options' => 'i',
            ];
        }

        $query = Users::find([
            $binds,
            "sort" => [
                "firstname" => 1,
            ],
        ]);

        $usersByType = [];
        $users       = [];

        if (count($query) > 0) {
            foreach ($query as $value) {
                if (in_array($value->type, ['user', 'employee', 'moderator', 'partner'])) {
                    $usersByType[$value->type][] = [
                        "id"       => $value->id,
                        "fullname" => $value->fullname,
                        "avatar"   => Auth::getAvatar($value, "small"),
                        "type"     => $value->type,
                        "hide"     => false,
                    ];
                }
            }

            $users = [];

            $usersByKey = [
                "user"      => [
                    "title" => Lang::get("Users"),
                    "list"  => ($usersByType["user"]) ? $usersByType["user"] : [],
                ],
                "employee"  => [
                    "title" => Lang::get("Employees"),
                    "list"  => ($usersByType["employee"]) ? $usersByType["employee"] : [],
                ],
                "moderator" => [
                    "title" => Lang::get("Moderators"),
                    "list"  => ($usersByType["moderator"]) ? $usersByType["moderator"] : [],
                ],
                "partner"   => [
                    "title" => Lang::get("Partners"),
                    "list"  => ($usersByType["partner"]) ? $usersByType["partner"] : [],
                ],
            ];

            if ($from == "calendar") {
                if (in_array("user", $permissions["calendar_create"]["selected"])) {
                    $users[] = $usersByKey["user"];
                }
                if (in_array("employee", $permissions["calendar_create"]["selected"])) {
                    $users[] = $usersByKey["employee"];
                }
                if (in_array("moderator", $permissions["calendar_create"]["selected"])) {
                    $users[] = $usersByKey["moderator"];
                }
                if (in_array("partner", $permissions["calendar_create"]["selected"])) {
                    $users[] = $usersByKey["partner"];
                }
            } elseif ($from == "chat") {
                if (in_array("user", $permissions["chat_create"]["selected"])) {
                    $users[] = $usersByKey["user"];
                }
                if (in_array("employee", $permissions["chat_create"]["selected"])) {
                    $users[] = $usersByKey["employee"];
                }
                if (in_array("moderator", $permissions["chat_create"]["selected"])) {
                    $users[] = $usersByKey["moderator"];
                }
                if (in_array("partner", $permissions["chat_create"]["selected"])) {
                    $users[] = $usersByKey["partner"];
                }
            } else {
                $users = [
                    $usersByKey["user"],
                    $usersByKey["employee"],
                    $usersByKey["moderator"],
                    $usersByKey["partner"],
                ];
            }

            $response = array(
                "status" => "success",
                "data"   => $users,
                "count"  => count($query),
            );
        } else {
            $error = Lang::get("noInformation", "No information found");

            $response = array(
                "status"      => "error",
                "error_code"  => 1023,
                "description" => $error,
            );
        }

        echo json_encode($response);
        exit();
    }
}
