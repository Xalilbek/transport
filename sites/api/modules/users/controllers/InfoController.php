<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id    = (int) Req::get("id");
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
            if ($permissions['allusers_view']['allow']) {
                $allow = true;
            } elseif ($data->type == "moderator" && $permissions['moderators_view']['allow']) {
                $allow = true;
            } elseif ($data->type == "employee" && $permissions['employees_view']['allow']) {
                $allow = true;
            } elseif ($data->type == "user" && $permissions['users_view']['allow']) {
                if (Auth::getData()->type == "employee") {
                    if (in_array("all", $permissions['users_view']['selected'])) {
                        $allow = true;
                    }
                } elseif (Auth::getData()->type == "moderator") {
                    $allow = true;
                }
            }

            if ($allow) {
                $userData = [
                    'id'          => $data->id,
                    'type'        => $data->type,
                    'fullname'    => $data->fullname,
                    'gender'      => $data->gender,
                    'created_at'  => Users::dateFormat($data->created_at, "Y-m-d H:i"),
                    'pin_require' => $data->pin_require,
                ];
                if (in_array($data->type, ['user', 'employee', 'moderator', 'partner'])) {
                    $userData['username']  = $data->username;
                    $userData['firstname'] = $data->firstname;
                    $userData['lastname']  = $data->lastname;
                    $userData['gender']    = $data->gender;
                    $userData['avatar']    = Auth::getAvatar($data);
                    $userData['email'] = $data->email;
                    $userData['phone'] = $data->phone;
                }
                if (in_array($data->type, ['user'])) {
                    $userData['verified'] = (int) $data->verified;
                }
                if (in_array($data->type, ['employee'])) {
                    $userData['salary'] = [
                        "monthly"             => $data->salary->monthly,
                        "hourly"              => $data->salary->hourly,
                    ];
                    $userData['work_hours_for_week'] = $data->work_hours_for_week ? $data->work_hours_for_week : [];
                }
                if (in_array($data->type, ['moderator'])) {
                    $userData['level'] = $data->level;
                }
                if (in_array($data->type, ['partner'])) {
                    $userData['partner_id'] = $data->partner_id;
                }
                $response = [
                    "status" => "success",
                    "data"   => $userData,
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
                $data,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}