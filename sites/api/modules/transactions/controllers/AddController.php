<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\TransactionItems;
use Custom\Models\Transactions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $parent_type = (string) trim($req['parent_type']);
        $parent_id   = (string) trim($req['parent_id']);
        $direction   = (string) trim($req['direction']);
        $description = (string) trim($req['description']);
        $status      = (int) trim($req['status']);
        $total       = (float) trim($req['total']);
        $items       = (array) json_decode($req['items'], true);

        $key = md5($parent_id);

        if (Cache::is_brute_force("transactionAdd-" . $key, [
            "minute" => 20,
            "hour"   => 50,
            "day"    => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("transactionAdd-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour"   => 300,
            "day"    => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {

            $allow = false;
            if ($permissions['transactions_create']['allow']) {
                if (in_array("all", $permissions['transactions_create']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_create']['selected'])) {
                    if ($parent_type == "user" && (string) $parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {
                $new_id = Transactions::getNewId();
                $insert = [
                    "id"          => (int) $new_id,
                    "user_id"     => (string) Auth::getData()->_id,
                    "parent_type" => (string) $parent_type,
                    "parent_id"   => (string) $parent_id,
                    "direction"   => (string) $direction,
                    "description" => (string) substr($description, 0, 1000),
                    "total"       => (float) $total,
                    "status"      => (int) $status,
                    "is_deleted"  => 0,
                    "created_at"  => Transactions::getDate(),
                ];

                $insert_id = Transactions::insert($insert);

                if ($insert_id) {
                    foreach ($items as $item) {
                        if (strlen($item["title"]) > 0 && is_numeric($item["quantity"]) && strlen($item["amount"]) > 0) {
                            TransactionItems::insert([
                                "transaction_id" => (string) $insert_id,
                                "title"          => (string) $item["title"],
                                "amount"         => (float) $item["amount"],
                                "quantity"       => (int) $item["quantity"],
                                "total"          => (float) ($item["amount"] * $item["quantity"]),
                                "is_deleted"     => 0,
                                "created_at"     => TransactionItems::getDate(),
                            ]);
                        }
                    }

                    $response = array(
                        "status"      => "success",
                        "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                    );
                } else {
                    $error = Lang::get("UnknownError", "Unknown error");
                }

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "transactions",
                    "operation" => "transactions_create",
                    "values"    => [
                        "id" => $insert_id,
                    ],
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
