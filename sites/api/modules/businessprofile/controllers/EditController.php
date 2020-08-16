<?php
namespace Controllers;

use Custom\Models\Businesses;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error           = false;
        $req             = (array) Req::get();
        $source          = (string) trim($req['source']);
        $title           = trim($req['title']);
        $phone           = (string) trim($req['phone']);
        $email           = (string) trim($req['email']);
        $address         = (string) trim($req['address']);
        $currency        = (int) trim($req['currency']);
        $daily_work_hour = (float) trim($req['daily_work_hour']);

        $data = Businesses::findFirst([
            [
                "id"         => (int) Auth::$business->data->id,
                "is_deleted" => ['$ne' => 1],
            ],
        ]);

        if (!$permissions['businessprofile_update']['allow']) {
            $error = Lang::get("PageNotAllowed");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {

            $update = [
                "updated_at" => Businesses::getDate(),
            ];

            if ($source == "profile") {
                $update["title"]   = (string) $title;
                $update["phone"]   = (array) [$phone];
                $update["email"]   = (array) [$email];
                $update["address"] = (string) $address;
            } else {
                $update["currency"]        = (int) $currency;
                $update["daily_work_hour"] = (float) $daily_work_hour;
            }

            Businesses::update(["_id" => $data->_id], $update);

            $response = [
                "status"      => "success",
                "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
            ];
        }
        if ($error) {
            $response = [
                "status"      => "error",
                "description" => $error,
                "error_code"  => 1202,
            ];
        }

        echo json_encode($response, true);
        exit();
    }
}
