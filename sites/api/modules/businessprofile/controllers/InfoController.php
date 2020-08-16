<?php
namespace Controllers;

use Custom\Models\Businesses;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $data = Auth::$business->data;

        if (!$permissions['businessprofile_view']['allow']) {
            $error = Lang::get("PageNotAllowed");
        } elseif (!$data) {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        } else {
            $info = [
                'id'              => (string) $data->_id,
                'title'           => (string) $data->title,
                'phone'           => (string) $data->phone[0],
                'email'           => (string) $data->email[0],
                'address'         => (string) $data->address,
                'currency'        => (int) $data->currency,
                'created_at'      => Businesses::dateFormat($data->created_at, "Y-m-d H:i"),
                'photo'           => Auth::getAvatar($data),
                'daily_work_hour' => (float) $data->daily_work_hour,
            ];

            $response = [
                "status" => "success",
                "data"   => $info,
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
