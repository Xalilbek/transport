<?php
namespace Controllers;

use Custom\Models\TransactionItems;
use Custom\Models\Transactions;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id    = (string) Req::get("id");
        $data  = Transactions::findFirst([
            [
                "_id"        => Transactions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $allow = false;
            if ($permissions['transactions_view']['allow']) {
                if (in_array("all", $permissions['transactions_view']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_view']['selected'])) {
                    if ($data->parent_type == "user" && (string) $data->parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {

                $user = Users::findFirst([
                    [
                        "_id"         => Users::objectId($data->user_id),
                        "is_deleted" => ['$ne' => 1],
                    ],
                ]);
                $items            = [];
                $transactionItems = TransactionItems::find([
                    [
                        "transaction_id" => (string) $data->_id,
                        "is_deleted"     => 0,
                    ],
                ]);
                foreach ($transactionItems as $row) {
                    $items[] = [
                        "id"       => (string) $row->_id,
                        "title"    => (string) $row->title,
                        "amount"   => (float) $row->amount,
                        "quantity" => (int) $row->quantity,
                        "total"    => (float) $row->total,
                    ];
                }
                $response = [
                    "status" => "success",
                    "data"   => [
                        'id'          => (string) $data->_id,
                        'user'        => ($user ? [
                            "id"       => $user->id,
                            "fullname" => $user->fullname,
                        ] : [
                            "id"       => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'parent_type' => $data->parent_type,
                        'parent_id'   => $data->parent_id,
                        'type'        => Transactions::typeListByKey(Lang::getLang())[$data->parent_type],
                        'direction'   => $data->direction,
                        'description' => $data->description,
                        'status'      => [
                            "value" => $data->status,
                            "text"  => Transactions::statusListByKey(Lang::getLang())[(string) $data->status],
                        ],
                        'items'       => $items,
                        'total'       => (float) $data->total,
                        'created_at'  => TimeZones::date($data->created_at, "Y-m-d H:i"),
                    ],
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
