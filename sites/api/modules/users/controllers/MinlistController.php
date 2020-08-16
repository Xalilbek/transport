<?php
namespace Controllers;

use Custom\Models\Parameters;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class MinlistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
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
        $category_id = (string) $req['category_id'];

        $binds               = [];
        $binds["is_deleted"] = [
            '$ne' => 1
        ];

        if (Users::typeListByKey(Lang::getLang())[$type]) {
            $binds["type"] = $type;
        }

        if (strlen($category_id) > 0) {
            $category                 = Parameters::findById($category_id);
            $binds['category']['$in'] = [$category->parent_id];
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

        $binds = $this->filterBinds($binds);

        $conditions = [
            $binds,
        ];

        $query = Users::find($conditions);

        $partnersById = Users::listById($query, 'partner_id', function ($ids) {
            return [
                "col"  => "id",
                "rows" => Users::find([
                    [
                        "id" => [
                            '$in' => $ids,
                        ],
                    ],
                ]),
            ];
        });

        $data = [];
        if (count($query) > 0) {
            foreach ($query as $value) {
                $params = [
                    'id'         => (string)$value->_id,
                    //'id'         => $value->id,
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
                        'avatar'    => Auth::getAvatar($value, $medium),
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
                    $partner = $partnersById[$value->partner_id];
                    if ($partner && !$partner->is_deleted) {
                        $params = array_merge($params, [
                            'partner' => [
                                'text'  => $partner->name,
                                'value' => $partner->id,
                            ],
                        ]);
                        $data[] = $this->filterData($params);
                    }
                } else {
                    $data[] = $this->filterData($params);
                }
            }

            $response = array(
                "status" => "success",
                "data"   => $data,
            );
        } else {
            $error = Lang::get("noInformation", "No information found");
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

    public function filterData($params)
    {
        $data   = [];
        $filter = [];
        $from   = Req::get("from");

        if (in_array($from, ["calendarGrid"])) {
            $filter = ["id", "fullname"];
        }

        if ($filter) {
            foreach ($params as $key => $value) {
                if (in_array($key, $filter)) {
                    $data[$key] = $value;
                }
            }
        } else {
            $data = $params;
        }

        return $data;
    }

    public function filterBinds($params)
    {
        $error       = false;
        $data        = $params;
        $from        = Req::get("from");
        $permissions = Auth::getPermissions();

        if (in_array($from, ["calendarGrid"])) {
            if (!in_array("all", $permissions['calendar_view']['selected'])) {
                if (!$params["type"]) {
                    foreach (USER_TYPES as $type) {
                        if (in_array($type, $permissions['calendar_view']['selected'])) {
                            $data["type"]['$in'][] = $type;
                        }
                    }
                } else {
                    if (!in_array($params["type"], $permissions['calendar_view']['selected'])) {
                        $error = Lang::get("PermissionNotAllowed");
                    }
                }
            }
        }

        if ($error) {
            return $error;
        }
        return $data;
    }
}
