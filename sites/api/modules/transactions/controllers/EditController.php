<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Transactions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error       = false;
        $req         = (array) Req::get();
        $id          = (string) $req['id'];
        $direction   = (string) trim($req['direction']);
        $description = (string) trim($req['description']);
        $status      = (int) trim($req['status']);
        $total       = (float) trim($req['total']);

        $data = Transactions::findFirst([
            [
                "_id"        => Transactions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("transactionEdit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $allow = false;
            if ($permissions['transactions_update']['allow']) {
                if (in_array("all", $permissions['transactions_update']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_update']['selected'])) {
                    if ($data->parent_type == "user" && (string) $data->parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {
                $update = [
                    "direction"   => (string) $direction,
                    "description" => (string) $description,
                    "status"      => (int) $status,
                    "total"       => (float) $total,
                    "updated_at"  => Transactions::getDate(),
                ];

                Transactions::update(["_id" => $data->_id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "transactions",
                    "operation" => "transactions_update",
                    "values"    => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => Transactions::findById($data->_id),
                    "status"    => 1,
                ]);
                // Log end
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
