<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;
        $req   = (array) Req::get();

        $id = (string)Auth::getData()->_id;

        $firstname = (string) htmlspecialchars($req['firstname']);
        $lastname  = (string) htmlspecialchars($req['lastname']);
        $phone     = (string) htmlspecialchars($req['phone']);
        $gender    = (string) htmlspecialchars($req['gender']);

        $invoice_due_date           = (int) trim($req['invoice_due_date']);
        $invoice_number             = (int) trim($req['invoice_number']);
        $company_name               = (string) trim($req['company_name']);
        $company_address            = (string) trim($req['company_address']);
        $payment_details            = (string) trim($req['payment_details']);
        $cvr                        = (string) trim($req['cvr']);
        $reg_no                     = (string) trim($req['reg_no']);
        $account_no                 = (string) trim($req['account_no']);

        $data = Users::findFirst([
            [
                "_id"         =>Users::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("editAccount-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            $update = [
                "fullname"   => $firstname . ' ' . $lastname,
                "firstname"  => $firstname,
                "lastname"   => $lastname,
                "phone"      => $phone,
                "gender"     => $gender,
                "updated_at" => Users::getDate(),
            ];

            if ($data->type == "employee") {
                $update = array_merge($update, [
                    "invoice_due_date"           => (int) $invoice_due_date,
                    "invoice_number"             => (int) $invoice_number,
                    "company_name"               => (string) $company_name,
                    "company_address"            => (string) $company_address,
                    "payment_details"            => (string) $payment_details,
                    "cvr"                        => (string) $cvr,
                    "reg_no"                     => (string) $reg_no,
                    "account_no"                 => (string) $account_no,
                ]);
            }

            Users::update(["_id" => Users::objectId($id)], $update);
            $response = [
                "status"      => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            ];
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
