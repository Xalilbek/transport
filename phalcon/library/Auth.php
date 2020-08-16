<?php
namespace Lib;

use Models\Cache;
use Models\Files;
use Models\Tokens;
use Models\Users;

class Auth
{
    public static $data;

    public static $permissions;

    public static $error = false;

    public static $errorCode = 0;

    public static $cacheSeconds = 10;

    public static $token;

    public static $tokenData;

    public static $crm;

    public static $business;

    public static $translations;

    public static function init()
    {

        $data = false;
        $req = json_decode(file_get_contents('php://input'), true);
        $token = (Req::get('source') !== "app" && strlen(@$_COOKIE["ut"]) > 0) ? (string)@$_COOKIE["ut"]: (string)trim(@$req['token']);
        if (Req::get('token'))
            $token = (string)trim(Req::get('token'));

        $tokenUser = (string)trim(Req::get('token_user'));

           if (strlen($token) > 0)
        {
            $cacheKey   = md5($tokenUser."-".$token);
            if($tokenUser && strlen($tokenUser) > 0)
                $data       = Cache::get($cacheKey);
            if(!$data){
                $data        = Tokens::getDataByToken($token);
                Cache::set($cacheKey, $data, 60);
            }

            foreach ($data->data as $key => $value)
            {
                if (in_array($key, ["crm", "business", "tokenData", "data"])) {
                    Auth::$$key = Users::toPHP(hex2bin($value));
                } else {
                    Auth::$$key = in_array($key, ["translations"]) ? (array) $value : $value;
                }
            }

            if (!Auth::$data || $data->status != "success" || (int) Auth::$data->is_deleted == 1) {
                if (!$data->status) {
                    Auth::$error     = Lang::get("ConnectionError", "Connection error");
                    Auth::$errorCode = 1000;
                } else {
                    Auth::$error     = Lang::get("AuthExpired", "Authentication expired");
                    Auth::$errorCode = 1001;
                }
            } else {

                define("TOKEN", (string) Auth::$token);
                define("CRM_TYPE", (int) @Auth::$data->crm_type);
                define("BUSINESS_ID", (int) @Auth::$data->business_id);
            }
        } else {
            Auth::$error     = Lang::get("AuthExpired", "Authentication expired");
            Auth::$errorCode = 1001;
        }

        return $data;
    }

    public static function createToken($request, $data)
    {
        $token = Auth::generateToken(md5($data->id . "-" . $request->get("REMOTE_ADDR") . "-" . microtime()), md5($data->id . "-" . $request->get("HTTP_USER_AGENT")));

        $tokenInsert = [
            "user_id"    => (float) $data->id,
            "token"      => $token,
            "ip"         => htmlspecialchars($request->getServer("REMOTE_ADDR")),
            "device"     => htmlspecialchars($request->getServer("HTTP_USER_AGENT")),
            "active"     => 1,
            "created_at" => MainDB::getDate(),
        ];
        Tokens::insert($tokenInsert);

        return $token;
    }

    public static function generateToken($namespace, $name)
    {
        $nhex = str_replace(array('-', '{', '}'), '', $namespace);
        $nstr = '';
        for ($i = 0; $i < strlen($nhex); $i += 2) {
            $nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
        }
        $hash = sha1($nstr . $name);

        $string = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_";
        $token  = "";
        for ($i = 0; $i < 80; $i++) {
            if (is_int($i / 2)) {
                $token .= $string[rand(0, strlen($string))];
            } else {
                $token .= $hash[rand(0, strlen($hash))];
            }
        }

        return $token . "_" . $hash;
    }

    public static function filterData($data)
    {
        $permission = new Permission();

        $filtered = [
            "id"             => $data->id,
            "crm_type"       => $data->crm_type,
            "username"       => strlen($data->username) > 0 ? (string) $data->username : false,
            "fullname"       => (string) $data->fullname,
            "firstname"      => (string) $data->firstname,
            "lastname"       => (string) $data->lastname,
            "phone"          => strlen((string) $data->phone) > 0 ? (string) $data->phone : false,
            "email"          => (string) $data->email,
            "photo"          => Auth::getAvatar($data),
            "pin_require"    => ($data->pin_require) ? true : false,
            "pin"            => strlen($data->pin) > 0 ? $data->pin : false,
            "gender"         => [
                "slug"  => (string) $data->gender == "female" ? "female" : "male",
                "title" => (string) $data->gender == "female" ? Lang::get("Female") : Lang::get("Male"),
            ],
            "email_verified" => ($data->email_verified) ? true : false,
            "phone_verified" => ($data->phone_verified) ? true : false,
            "type"           => $data->type,
            "verified"       => $data->verified ? $data->verified : 0,
            "permissions"    => $permission->getPermissionsByUser($data, Auth::getPermissionConstruct((int) @Auth::$business->data->package_id, $data->type)),
        ];

        if ($data->type == "employee") {
            if ($data->crm_type == 2) {
                $signFile = Files::findFirst([
                    [
                        "_id"        => Files::objectId($data->sign_file_id),
                        "is_deleted" => [
                            '$ne' => 1,
                        ],
                    ],
                ]);
                $filtered = array_merge($filtered, [
                    "price_for_practical_lesson" => $data->price_for_practical_lesson,
                    "invoice_due_date"           => $data->invoice_due_date,
                    "sign"                       => $data->sign_file_id ? [
                        "file_id" => $data->sign_file_id,
                        "url"     => Files::getFileUrl($signFile),
                        "avatar"  => Files::getAvatar($signFile, "medium"),
                    ] : [
                        "file_id" => "",
                        "url"     => "",
                    ],
                ]);
            } elseif ($data->crm_type == 3) {
                $filtered = array_merge($filtered, [
                    "salary"              => (object) [
                        "monthly" => $data->salary->monthly,
                        "hourly"  => $data->salary->hourly,
                    ],
                    "work_hours_for_week" => $data->work_hours_for_week ? $data->work_hours_for_week : [],
                ]);
            }
        }

        return $filtered;
    }

    public static function getAvatar($data)
    {
        if ($data->avatar && $data->avatar->id) {
            $avatars = [];
            foreach ($data->avatar->avatars as $key => $value) {
                $avatars[$key] = 'https://' . $data->avatar->server . '/' . $value;
            }
            return $avatars;
        } else {
            return [
                "tiny"    => FILE_URL . "/resources/images/noavatar.jpg",
                "small"   => FILE_URL . "/resources/images/noavatar.jpg",
                "medium"  => FILE_URL . "/resources/images/noavatar.jpg",
                "large"   => FILE_URL . "/resources/images/noavatar.jpg",
                "nophoto" => true,
            ];
        }
    }

    public static function getPermissions()
    {
        return json_decode(json_encode(Auth::$permissions), true);
    }

    public static function getPermissionConstruct($packageId, $userType)
    {
        if (Auth::$crm->data->permissions) {
            if (Auth::$crm->data->permissions->{$packageId} && $construct = Auth::$crm->data->permissions->{$packageId}->{$userType}) {
                return json_decode(json_encode($construct), true);
            }
        }
        return [];
    }

    public static function checkPermission( $key, $belongsTo, $withKey = false)
    {
        $permissions = Auth::getPermissions();

        $allow = false;
        if ($permissions[$key]['allow']) {
            if (in_array("all", $permissions[$key]['selected'])) {
                $withKey ? $allow['all'] : $allow = true;
            } elseif (in_array("self", $permissions[$key]['selected'])) {
                if ($belongsTo == Auth::$auth->getData()->id) {
                    $withKey ? $allow['self'] : $allow = true;
                }
            }
        }

        return $allow;
    }

    public static function getSwitchCacheKey()
    {
        return "switchUsers_" . Auth::$data->id;
    }

    public static function clearSwitchCache()
    {
        Cache::set(Auth::getSwitchCacheKey(), false, time() + 60);
    }

    public static function setData($data)
    {
        return Auth::$data = $data;
    }

    public static function getData()
    {
        return Auth::$data;
    }

    public static function refreshData()
    {
        $data = Users::findFirst([
            [
                "id" => (int) Auth::$data->id,
            ],
        ]);

        return Auth::$data = $data;
    }

    public static function getToken()
    {
        return Auth::$token;
    }

    public static function getFromCache()
    {
        return Cache::get(Auth::getCacheKey());
    }

    public static function getCacheKey()
    {
        return md5("auth-d");
    }

    public static function flushCache()
    {
        return Cache::set(Auth::getCacheKey(), false, time());
    }

    public static function saveCache($data)
    {
        return Cache::set(Auth::getCacheKey(), $data, time() + Auth::$cacheSeconds);
    }
}
