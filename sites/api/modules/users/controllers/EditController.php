<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $id        = (int) $req['id'];
        $username  = (string) htmlspecialchars($req['username']);
        $password  = (string) htmlspecialchars($req['password']);
        $firstname = (string) htmlspecialchars($req['firstname']);
        $lastname  = (string) htmlspecialchars($req['lastname']);
        $email     = (string) htmlspecialchars(strtolower($req['email']));
        $phone     = (string) htmlspecialchars($req['phone']);
        $gender    = (string) htmlspecialchars($req['gender']);
        $cvr       = (string) htmlspecialchars($req['cvr']);
        $reg_no    = (string) htmlspecialchars($req['reg_no']);
        $account_no= (string) htmlspecialchars($req['account_no']);
        $verified  = (int) htmlspecialchars($req['verified']);

        $salary              = (array) $req['salary'];
        $work_hours_for_week = Users::filterWorkHours((array) json_decode($req['work_hours_for_week'], true));

        // if type moderator
        $level = (string) htmlspecialchars($req['level']);

        $data = Users::findFirst([
            [
                "id"         => (int) $id,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        if (Cache::is_brute_force("userAdd-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
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
                $exist = Users::findFirst([
                    [
                        "id"          => [
                            '$ne' => (int) $id,
                        ],
                        "type"        => (string) $data->type,
                        "phone"       => (string) $phone,
                        "crm_type"    => CRM_TYPE,
                        "business_id" => CRM_TYPE,
                        "is_deleted"  => 0,
                    ],
                ]);
                if ($exist) {
                    $error = Lang::get("PhoneExists", "Phone exists");
                } else {
                    $update = [
                        "username"  => (string) $username,
                        "fullname"  => (string) $firstname . ' ' . $lastname,
                        "firstname" => (string) $firstname,
                        "lastname"  => (string) $lastname,
                        "email"     => (string) $email,
                        "phone"     => (string) $phone,
                        "gender"    => (string) $gender,
                        "cvr"       => (string) $cvr,
                        "reg_no"    => (string) $reg_no,
                        "account_no"=> (string) $account_no,
                    ];

                    if (strlen($password) > 2 && (string) Lib::generatePassword($password) !== $data->password) {
                        $update["password"] = (string) Lib::generatePassword($password);
                    }

                    if ($data->type == 'user') {
                        $update = array_merge($update, [
                            "verified" => (int) $verified,
                        ]);
                    } elseif ($data->type == 'employee') {
                        $update = array_merge($update, [
                            "salary"              => (array) [
                                "monthly" => (float) $salary["monthly"],
                                "hourly"  => (float) $salary["hourly"],
                            ],
                            "work_hours_for_week" => $work_hours_for_week,
                        ]);
                    } elseif ($data->type == 'moderator') {
                        $update = array_merge($update, [
                            "level" => (string) $level,
                        ]);
                    }

                    $update = array_merge($update, [
                        "updated_at" => Users::getDate(),
                    ]);

                    Users::update(["id" => (int) $id], $update);

                    $response = array(
                        $work_hours_for_week,
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
