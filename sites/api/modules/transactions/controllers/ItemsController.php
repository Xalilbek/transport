<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\TransactionItems;
use Custom\Models\Transactions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ItemsController extends \Phalcon\Mvc\Controller
{
    public function addAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $transaction_id = (string) trim($req['transaction_id']);
        $title          = (string) trim($req['title']);
        $amount         = (float) trim($req['amount']);
        $quantity       = (int) trim($req['quantity']);
        $total          = (float) $amount * $quantity;

        $key = md5($transaction_id . $title);

        if (Cache::is_brute_force("transactionItemsAdd-" . $key, ["minute" => 20, "hour" => 50, "day" => 100])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("transactionItemsAdd-" . Req::getServer("REMOTE_ADDR"), ["minute" => 40, "hour" => 300, "day" => 900])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {

            $data = Transactions::findFirst([
                [
                    "_id"        => Transactions::objectId($transaction_id),
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);

            if (!$data) {
                $error = Lang::get("noInformation", "No information found");
            } else {
                $allow = false;
                if ($permissions['transactions_create']['allow']) {
                    if (in_array("all", $permissions['transactions_create']['selected'])) {
                        $allow = true;
                    } elseif (in_array("self", $permissions['transactions_create']['selected'])) {
                        if ($data->parent_type == "user" && (string) $data->parent_id == (string)Auth::getData()->_id) {
                            $allow = true;
                        }
                    } else {
                        $allow = true;
                    }
                }

                if ($allow) {
                    $insert = [
                        "transaction_id" => (string) $transaction_id,
                        "title"          => (string) $title,
                        "amount"         => (float) $amount,
                        "quantity"       => (int) $quantity,
                        "total"          => (float) $total,
                        "is_deleted"     => 0,
                        "created_at"     => TransactionItems::getDate(),
                    ];

                    $insert_id = TransactionItems::insert($insert);

                    $total            = 0;
                    $transactionItems = TransactionItems::find([
                        [
                            "transaction_id" => (string) $transaction_id,
                            "is_deleted"     => 0,
                        ],
                    ]);

                    foreach ($transactionItems as $row) {
                        $total += $row->total;
                    }

                    Transactions::update([
                        "_id" => Transactions::objectId($transaction_id),
                    ],
                        [
                            "total" => (float) $total,
                        ]);

                    if ($insert_id) {
                        $response = array(
                            "status"      => "success",
                            "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                        );
                    } else {
                        $error = Lang::get("UnknownError", "Unknown error");
                    }
                } else {
                    $error = Lang::get("PageNotAllowed");
                }
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

    public function editAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $id             = (string) trim($req['id']);
        $transaction_id = (string) trim($req['transaction_id']);

        $title    = (string) trim($req['title']);
        $amount   = (float) trim($req['amount']);
        $quantity = (int) trim($req['quantity']);
        $total    = (float) $amount * $quantity;

        $transaction = Transactions::findFirst([
            [
                "_id"        => Transactions::objectId($transaction_id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        $data = TransactionItems::findFirst([
            [
                "_id"            => TransactionItems::objectId($id),
                "transaction_id" => (string) $transaction_id,
                "is_deleted"     => 0,
            ],
        ]);

        $allow = false;
        if ($permissions['transactions_update']['allow']) {
            if (in_array("all", $permissions['transactions_update']['selected'])) {
                $allow = true;
            } elseif (in_array("self", $permissions['transactions_update']['selected'])) {
                if ($transaction->parent_type == "user" && (string) $transaction->parent_id == (string)Auth::getData()->_id) {
                    $allow = true;
                } elseif ($transaction->parent_type == "case") {
                    $case = Cases::findById($transaction->parent_id);
                    if (in_array((string)Auth::getData()->_id, $case->users)) {
                        $allow = true;
                    }
                }
            } else {
                $allow = true;
            }
        }

        if ($allow) {

            if (Cache::is_brute_force("transactionItemsEdit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
                $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
            } elseif (!$data) {
                $error = Lang::get("noInformation", "No information found");
            } else {
                $update = [
                    "title"      => (string) $title,
                    "amount"     => (float) $amount,
                    "quantity"   => (int) $quantity,
                    "total"      => (float) $total,
                    "updated_at" => TransactionItems::getDate(),
                ];

                $updated = TransactionItems::update([
                    "_id" => $data->_id,
                ],
                    $update
                );

                $total            = 0;
                $transactionItems = TransactionItems::find([
                    [
                        "transaction_id" => (string) $transaction_id,
                        "is_deleted"     => 0,
                    ],
                ]);

                foreach ($transactionItems as $row) {
                    $total += $row->total;
                }

                Transactions::update([
                    "_id" => Transactions::objectId($transaction_id),
                ],
                    [
                        "total" => (float) $total,
                    ]);

                if ($updated) {
                    $response = array(
                        "status"      => "success",
                        "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                    );
                } else {
                    $error = Lang::get("UnknownError", "Unknown error");
                }
            }
        } else {
            $error = Lang::get("PageNotAllowed");
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

    public function deleteAction()
    {
        $permissions = Auth::getPermissions();

        $error          = false;
        $req            = (array) Req::get();
        $id             = (string) trim($req['id']);
        $transaction_id = (string) trim($req['transaction_id']);

        $transaction = Transactions::findFirst([
            [
                "_id"        => Transactions::objectId($transaction_id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        $data = TransactionItems::findFirst([
            [
                "_id"            => TransactionItems::objectId($id),
                "transaction_id" => (string) $transaction_id,
                "is_deleted"     => 0,
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $allow = false;
            if ($permissions['transactions_delete']['allow']) {
                if (in_array("all", $permissions['transactions_delete']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_delete']['selected'])) {
                    if ($transaction->parent_type == "user" && (string) $transaction->parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    } elseif ($transaction->parent_type == "case") {
                        $case = Cases::findById($transaction->parent_id);
                        if (in_array((string)Auth::getData()->_id, $case->users)) {
                            $allow = true;
                        }
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {
                $update = [
                    "is_deleted" => 1,
                    "deleter_id" => (string)Auth::getData()->_id,
                    "deleted_at" => TransactionItems::getDate(),
                ];
                TransactionItems::update(["_id" => $data->_id], $update);
                $total            = 0;
                $transactionItems = TransactionItems::find([
                    [
                        "transaction_id" => (string) $transaction_id,
                        "is_deleted"     => 0,
                    ],
                ]);

                foreach ($transactionItems as $row) {
                    $total += $row->total;
                }

                Transactions::update([
                    "_id" => Transactions::objectId($transaction_id),
                ],
                    [
                        "total" => (float) $total,
                    ]);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
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
        echo json_encode($response);
        exit;
    }

    public function listAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $transaction_id = (string) trim($req['transaction_id']);

        $transaction = Transactions::findFirst([
            [
                "_id"        => Transactions::objectId($transaction_id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        $allow = false;
        if ($permissions['transactions_view']['allow']) {
            if (in_array("all", $permissions['transactions_view']['selected'])) {
                $allow = true;
            } elseif (in_array("self", $permissions['transactions_view']['selected'])) {
                if ($transaction->parent_type == "user" && (string) $transaction->parent_id == (string)Auth::getData()->_id) {
                    $allow = true;
                } elseif ($transaction->parent_type == "case") {
                    $case = Cases::findById($transaction->parent_id);
                    if (in_array((string)Auth::getData()->_id, $case->users)) {
                        $allow = true;
                    }
                }
            } else {
                $allow = true;
            }
        }

        if ($allow) {

            $query = TransactionItems::find([
                [
                    "is_deleted"     => 0,
                    "transaction_id" => (string) $transaction_id,
                ],
                "sort" => [
                    "created_at" => -1,
                ],
            ]);

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $data[] = [
                        'id'             => (string) $value->_id,
                        'transaction_id' => (string) $value->transaction_id,
                        'title'          => (string) $value->title,
                        'amount'         => (float) $value->amount,
                        'quantity'       => (int) $value->quantity,
                        'total'          => (float) $value->total,
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data"   => $data,
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

    public function infoAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $id = (string) trim($req['id']);

        $data = TransactionItems::findFirst([
            [
                "_id"        => TransactionItems::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $transaction = Transactions::findFirst([
                [
                    "_id"        => Transactions::objectId($data->transaction_id),
                    "is_deleted" => ['$ne' => 1],
                ],
            ]);

            $allow = false;
            if ($permissions['transactions_view']['allow']) {
                if (in_array("all", $permissions['transactions_view']['selected'])) {
                    $allow = true;
                } elseif (in_array("self", $permissions['transactions_view']['selected'])) {
                    if ($transaction->parent_type == "user" && (string) $transaction->parent_id == (string)Auth::getData()->_id) {
                        $allow = true;
                    } elseif ($transaction->parent_type == "case") {
                        $case = Cases::findById($transaction->parent_id);
                        if (in_array((string)Auth::getData()->_id, $case->users)) {
                            $allow = true;
                        }
                    }
                } else {
                    $allow = true;
                }
            }

            if ($allow) {
                $response = [
                    "status" => "success",
                    "data"   => [
                        "id"       => (string) $data->_id,
                        "title"    => (string) $data->title,
                        "amount"   => (float) $data->amount,
                        "quantity" => (int) $data->quantity,
                        "total"    => (float) $data->total,
                    ],
                ];
            } else {
                $error = Lang::get("PageNotAllowed");
            }
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
