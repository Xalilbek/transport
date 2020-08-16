<?php
namespace Controllers;

use Custom\Models\Partners;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error  = false;
        $req    = (array) Req::get();
        $type   = (string) $req["type"];
        $source = (string) $req['source'];

        $id          = (int) trim($req['id']);
        $username    = (string) trim($req['username']);
        $fullname    = (string) trim($req['fullname']);
        $email       = (string) trim($req['email']);
        $phone       = (string) trim($req['phone']);
        $level       = (string) $req['level'];
        $partner_id  = (string) $req['partner_id'];
        $crm_type    = (int) $req['crm_type'];
        $business_id = (int) $req['business_id'];

        $allow = false;
        $binds = [];
        if (!$type && $permissions['allusers_view']['allow']) {
            $allow = true;
        } elseif ($type == "moderator" && $permissions['moderators_view']['allow']) {
            $allow = true;
        } elseif ($type == "employee" && $permissions['employees_view']['allow']) {
            $allow = true;
        } elseif ($type == "user" && $permissions['users_view']['allow']) {
            $allow = true;
        }

        if ($allow) {
            $binds["is_deleted"] = [
                '$ne' => 1
            ];

            if (Users::typeListByKey(Lang::getLang())[$type]) {
                $binds["type"] = $type;
            }

            if (is_numeric($id) && $id > 0) {
                $binds['id'] = (int) $id;
            }

            if (strlen($username) > 0) {
                $binds['username'] = [
                    '$regex'   => $username,
                    '$options' => 'i',
                ];
            }

            if (strlen($fullname) > 0) {
                $binds['fullname'] = [
                    '$regex'   => $fullname,
                    '$options' => 'i',
                ];
            }

            if (strlen($email) > 0) {
                $binds['email'] = [
                    '$regex'   => $email,
                    '$options' => 'i',
                ];
            }

            if (strlen($phone) > 0) {
                $binds['phone'] = [
                    '$regex'   => $phone,
                    '$options' => 'i',
                ];
            }

            if ($type == 'moderator' && Users::levelListByKey(Lang::getLang())[$level]) {
                $binds['level'] = (string) $level;
            }

            if ($type == 'partner' && is_numeric($partner_id) && $partner_id > 0) {
                $binds['partner_id'] = (int) $partner_id;
            }

            $conditions = [
                $binds,
            ];

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            if (in_array($sort_field, ['id', 'username', 'fullname', 'firstname', 'lastname', 'type', 'email', 'phone', 'created_at'])) {
                $conditions["sort"][$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req["skip"];
            $limit = (int) $req["limit"];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            if (Req::get("limit") !== "-1") {
                $conditions = array_merge($conditions, [
                    "limit" => $limit,
                    "skip"  => $skip,
                ]);
            }

            $query = Users::find($conditions);
            $count = Users::count([
                $binds,
            ]);

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $params = [
                        'id'         => $value->id,
                        'type'       => $value->type,
                        'username'   => $value->username,
                        'fullname'   => $value->fullname,
                        'email'      => $value->email,
                        'created_at' => Users::dateFormat($value->created_at, "Y-m-d H:i:s"),
                    ];

                    if (in_array($type, ['user', 'employee', 'moderator', 'partner'])) {
                        $params = array_merge($params, [
                            'username'  => $value->username,
                            'firstname' => $value->firstname,
                            'lastname'  => $value->lastname,
                            'email'     => $value->email,
                            'phone'     => $value->phone,
                            'gender'    => $value->gender,
                            'avatar'    => Auth::getAvatar($value, "medium"),
                        ]);
                    }

                    if (in_array($type, ['moderator'])) {
                        $params = array_merge($params, [
                            'level' => [
                                'text'  => Lang::get(ucfirst($value->level)),
                                'value' => $value->level,
                            ],
                        ]);
                    }

                    if (in_array($type, ['partner'])) {
                        $partner = Partners::getById($value->partner_id);
                        if ($partner && !$partner->is_deleted) {
                            $params = array_merge($params, [
                                'partner' => [
                                    'text'  => $partner->name,
                                    'value' => $partner->id,
                                ],
                            ]);
                            $data[] = $params;
                        }
                    } else {
                        $data[] = $params;
                    }
                }

                $response = array(
                    "status" => "success",
                    "data"   => $data,
                    "count"  => $count,
                    "skip"   => $skip,
                    "limit"  => $limit,
                );
            } else {
                $error = Lang::get("noInformation", "No information found");
            }
        } else {
            $error = Lang::get("PageNotAllowed");
        }

        if ($error) {
            $response = array(
                "status"      => "error",
                "error_code"  => 1023,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
