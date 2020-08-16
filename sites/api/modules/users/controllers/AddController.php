<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $type      = (string) htmlspecialchars($req['type']);
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

        $salary              = (array) $req['salary'];
        $work_hours_for_week = Users::filterWorkHours((array) json_decode($req['work_hours_for_week'], true));

        // if type moderator
        $level = (string) htmlspecialchars($req['level']);

        $key = md5($type . $phone);

        if (Cache::is_brute_force("userAdd-" . $key, ["minute" => 20, "hour" => 50, "day" => 100])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("userAdd-" . Req::getServer("REMOTE_ADDR"), ["minute" => 40, "hour" => 300, "day" => 900])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!Users::typeListByKey(Lang::getLang())[$type]) {
            $error = Lang::get("UserTypeIsWrong", "UserTypeIsWrong");
        } elseif (strlen($username) > 0 && !preg_match('/^[a-z0-9\\_]{4,32}$/', strtolower($username))) {
            $error = Lang::get("UsernameIsWrong", "Username is wrong");
        } elseif (!is_numeric($phone) || strlen($phone) < 7 || strlen($phone) > 15) {
            $error = Lang::get("PhoneIsWrong", "Phone is wrong");
        } elseif (strlen($firstname) < 2 || strlen($firstname) > 50) {
            $error = Lang::get("FirstnameIsWrong", "Firstname is wrong");
        } elseif (!$gender) {
            $error = Lang::get("GenderNotSelected", "Gender not selected");
        } else {
            $allow = false;
            if ($permissions['allusers_create']['allow']) {
                $allow = true;
            } elseif ($type == "moderator" && $permissions['moderators_create']['allow']) {
                $allow = true;
            } elseif ($type == "employee" && $permissions['employees_create']['allow']) {
                $allow = true;
            } elseif ($type == "user" && $permissions['users_create']['allow']) {
                $allow = true;
            }
            if ($allow) {
                $checkUsername = Users::findFirst([
                    [
                        "username"    => (string) $username,
                        "crm_type"    => ['$ne' => 0],
                        "business_id" => ['$ne' => 0],
                    ],
                ]);
                if ($checkUsername) {
                    $error = Lang::get("UsernameExists", "Username exists");
                } else {
                    $exist = Users::findFirst([
                        [
                            "phone"       => (string) $phone,
                            "type"        => (string) $type,
                            "crm_type"    => CRM_TYPE,
                            "business_id" => BUSINESS_ID,
                            "is_deleted"  => ['$ne' => 1],
                        ],
                    ]);
                    if ($exist) {
                        $error = Lang::get("UserExists", "User exists");
                    } else {
                        $insert = [
                            "type"          => (string) $type,
                            "username"      => (string) strlen($username) > 0 ? strtolower($username) : null,
                            "password"      => (string) Lib::generatePassword($password),
                            "fullname"      => (string) $firstname . ' ' . $lastname,
                            "firstname"     => (string) $firstname,
                            "lastname"      => (string) $lastname,
                            "email"         => (string) $email,
                            "phone"         => (string) $phone,
                            "gender"        => (string) $gender,
                            "cvr"           => (string) $cvr,
                            "reg_no"        => (string) $reg_no,
                            "account_no"    => (string) $account_no,
                        ];

                        if ($type == 'user') {
                            $insert = array_merge($insert, [
                                "verified" => 0,
                            ]);
                        } elseif ($type == 'employee') {
                            $insert = array_merge($insert, [
                                "salary"              => [
                                    "monthly" => (float) $salary["monthly"],
                                    "hourly"  => (float) $salary["hourly"],
                                ],
                                "work_hours_for_week" => $work_hours_for_week,
                            ]);
                        } elseif ($type == 'moderator') {
                            $insert = array_merge($insert, [
                                "level" => (string) $level,
                            ]);
                        }

                        $insert = array_merge($insert, [
                            "status"     => 1,
                            "created_at" => Users::getDate(),
                        ]);

                        $insert_id = Users::insert($insert);

                        $response = array(
                            "status"      => "success",
                            "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                        );

                        // Log start
                        Activities::log([
                            "user_id"   => (string)Auth::getData()->_id,
                            "section"   => "users",
                            "operation" => "users_create",
                            "values"    => [
                                "id" => Users::findById($insert_id)->id,
                            ],
                            "status"    => 1,
                        ]);
                        // Log end
                    }
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
