<?php
namespace Controllers;

use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;
use const Multiple\TRANSACTION_TYPES;
use Custom\Models\Transactions;
use Custom\Models\Users;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id     = (string) trim($req['user_id']);
        $parent_type = (string) trim($req['parent_type']);
        $parent_id   = (string)trim($req['parent_id']);
        $status      = (string) trim($req['status']);

        $allow = false;
        if ($permissions['transactions_view']['allow']) {
            if (in_array("all", $permissions['transactions_view']['selected'])) {
                $allow = true;
            } elseif (in_array("self", $permissions['transactions_view']['selected'])) {
                if ($parent_type == "user" && (string) $parent_id == (string)Auth::getData()->_id) {
                    $allow = true;
                } else {
                    $allow     = true;
                    $parent_id = (string)Auth::getData()->_id;
                }
            } else {
                $allow = true;
            }
        }

        if ($allow) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if (strlen($user_id) > 0) {
                $binds['user_id'] = (string) $user_id;
            }

            if (in_array($parent_type, TRANSACTION_TYPES)) {
                $binds['parent_type'] = (string) $parent_type;
            }

            if (strlen($parent_id) > 0) {
                $binds['parent_id'] = (string) $parent_id;
            }

            if (in_array($status, ["0", "1", "2"])) {
                $binds['status'] = (int) $status;
            }

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['parent_type', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req['skip'];
            $limit = (int) $req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = Transactions::find([
                $binds,
                "skip"  => $skip,
                "limit" => $limit,
                "sort"  => $sort,
            ]);

            $count = Transactions::count([
                $binds,
            ]);

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $user = Users::findFirst([
                        [
                            "_id"         => Users::objectId($value->user_id),
                            "is_deleted" => ['$ne' => 1],
                        ],
                    ]);
                    $data[] = [
                        'id'          => (string) $value->_id,
                        'user'        => ($user ? [
                            "id"       => $user->id,
                            "fullname" => $user->fullname,
                        ] : [
                            "id"       => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'parent_id'   => $value->parent_id,
                        'type'        => Transactions::typeListByKey(Lang::getLang())[$value->parent_type],
                        'direction'   => $value->direction,
                        'description' => $value->description,
                        'total'       => (float) $value->total,
                        'status'      => Transactions::statusListByKey(Lang::getLang())[$value->status],
                        'created_at'  => TimeZones::date($value->created_at, "Y-m-d H:i"),
                    ];
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
